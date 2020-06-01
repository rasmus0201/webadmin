<?php

namespace App\Websites\Nginx;

use App\TemplateEngine\Parser as TemplateParser;
use App\Websites\Contracts\ConfigParserContract;
use App\Websites\Contracts\WebserverContract;
use App\Websites\Nginx\ConfigParser\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;

class Nginx implements WebserverContract
{
    public function createVirtualHost($template, $name, $data)
    {
        $parser = new TemplateParser($template);
        $parser->render($data);

        $tmpLocation = tempnam(sys_get_temp_dir(), 'webadmin-nginx-vhost');
        $safeTmpLocation = escapeshellarg($tmpLocation);
        $safeName = escapeshellarg($name);
        $bin = escapeshellarg(base_path('bin/webserver_manager'));

        // Save to tmp first
        if (!$parser->asFile($tmpLocation)) {
            return;
        }

        // Create in /etc/nginx/sites-available/$name.conf
        $lastLine = exec(sprintf('%s create %s %s 2>&1', $bin, $safeName, $safeTmpLocation), $retArr, $retVal);

        if ($retVal !== 0) {
            Log::error($retArr);
            throw new \RuntimeException("webserver_manager failed creating: '$lastLine'");
        }

        // Link /etc/nginx/sites-enabled/$name.conf -> /etc/nginx/sites-available/$name.conf
        $lastLine = exec(sprintf('%s link %s 2>&1', $bin, $safeName), $retArr, $retVal);

        if ($retVal !== 0) {
            Log::error($retArr);
            throw new \RuntimeException("webserver_manager failed linking: '$lastLine'");
        }

        return Config::createFromString($parser->asString());
    }

    public function deleteVirtualHost($name)
    {
        $safeName = escapeshellarg($name);
        $bin = escapeshellarg(base_path('bin/webserver_manager'));

        // Delete from /etc/nginx/sites-available/$name.conf
        // and /etc/nginx/sites-enabled/$name.conf
        $lastLine = exec(sprintf('%s delete %s 2>&1', $bin, $safeName), $retArr, $retVal);

        if ($retVal !== 0) {
            Log::error($retArr);
            throw new \RuntimeException("webserver_manager failed creating: '$lastLine'");
        }

        return true;
    }

    public function createSnippet($template, $name)
    {
        $parser = new TemplateParser($template);
        $parser->render();

        $tmpLocation = tempnam(sys_get_temp_dir(), 'webadmin-nginx-snippet');
        $safeTmpLocation = escapeshellarg($tmpLocation);
        $safeName = escapeshellarg($name);
        $bin = escapeshellarg(base_path('bin/webserver_manager'));

        // Save to tmp first
        if (!$parser->asFile($tmpLocation)) {
            return;
        }

        // Create in /etc/nginx/snippets/$name.conf
        $lastLine = exec(sprintf('%s create-snippet %s %s 2>&1', $bin, $safeName, $safeTmpLocation), $retArr, $retVal);

        if ($retVal !== 0) {
            Log::error($retArr);
            throw new \RuntimeException("webserver_manager failed creating: '$lastLine'");
        }

        return Config::createFromString($parser->asString());
    }

    public function getWebsiteConfigPath($domain)
    {
        $domain = escapeshellcmd($domain);
        return "/etc/nginx/sites-available/{$domain}.conf";
    }

    public function getVirtualHostName($domain)
    {
        $domain = preg_replace('/[^\p{L}\d\.-]+/u', '', $domain);

        while (Str::startsWith($domain, ['.', '-'])) {
            $domain = Str::substr($domain, 1);
        }

        while (Str::contains($domain, '..')) {
            $domain = Str::replaceFirst('..', '.', $domain);
        }

        $domain = Str::ascii($domain);
        $domain = "{$domain}.conf";

        return $domain;
    }

    public function createWithDummyIndex(ConfigParserContract $vHost)
    {
        $this->rootDirectoryGuard($vHost);

        $publicPath = $vHost['server']['root']->parametersAsString();
        $indexPath = $publicPath . '/index.php';

        if (!file_exists($publicPath)) {
            $r = mkdir($publicPath, 0775, true);
        }

        if (!file_exists($indexPath)) {
            $h = fopen($indexPath, 'w');
            fwrite($h, '<h1>This website is waiting for configuration</h1>');
            fclose($h);
        }
    }

    public function createWithGitRepository(ConfigParserContract $vHost, $gitRepository)
    {
        $this->rootDirectoryGuard($vHost);

        $rootPath = dirname($vHost['server']['root']->parametersAsString());

        if (!file_exists($rootPath)) {
            $r = mkdir($rootPath, 0775, true);
        }

        $idFilePath = escapeshellarg('/home/www-data/.ssh/id_rsa');

        exec(sprintf("cd %s && /usr/bin/git -c core.sshCommand=\"ssh -i %s\" clone %s . && composer install", escapeshellcmd($rootPath), escapeshellcmd($idFilePath), escapeshellcmd($gitRepository)));
    }

    public function reload()
    {
        exec('sudo /etc/init.d/nginx reload');
    }

    public function test()
    {
        exec('sudo /etc/init.d/nginx configtest', $retArr, $retVal);

        return $retVal === 0 ? true : false;
    }

    private function rootDirectoryGuard(ConfigParserContract $vHost)
    {
        if (!isset($vHost['server']) && !isset($vHost['server']['root'])) {
            throw new \RuntimeException("The index \$vHost['server']['root'] must be set.");
        }
    }
}
