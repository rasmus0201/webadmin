<?php

namespace App\Websites\Nginx;

use App\TemplateEngine\Parser as TemplateParser;
use App\Websites\Contracts\ConfigParserContract;
use App\Websites\Contracts\WebserverContract;
use App\Websites\Nginx\ConfigParser\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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

    public function createRootDirectory(ConfigParserContract $vHost)
    {
        if (!isset($vHost['server']) && !isset($vHost['server']['root'])) {
            throw new \RuntimeException("The index \$vHost['server']['root'] must be set.");
        }

        $rootPath = $vHost['server']['root']->parametersAsString();

        Log::debug($rootPath);

        if (!file_exists($rootPath)) {
            $r = mkdir($rootPath, 0755, true);

            Log::debug("mkdir() = $r");
        }
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

    public function reload()
    {
        exec('sudo /etc/init.d/nginx reload');
    }
}
