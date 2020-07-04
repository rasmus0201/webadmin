<?php

namespace App\Builders;

use App\Parsers\Template\Parser as TemplateParser;
use App\Contracts\ConfigParserContract;
use App\Contracts\WebserverContract;
use App\Parsers\Nginx as NginxParser;
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
        $bin = base_path('bin/webserver_manager');

        // Save to tmp first
        if (!$parser->asFile($tmpLocation)) {
            return;
        }

        // Create in /etc/nginx/sites-available/$name.conf
        $lastLine = exec(
            escapeshellcmd(sprintf(
                '%s create %s %s',
                $bin,
                escapeshellarg($name),
                escapeshellarg($tmpLocation)
            )) . ' 2>&1',
            $retArr,
            $retVal
        );

        if ($retVal !== 0) {
            Log::error($retArr);
            throw new \RuntimeException("webserver_manager failed creating: '$lastLine'");
        }

        // Link /etc/nginx/sites-enabled/$name.conf -> /etc/nginx/sites-available/$name.conf
        $lastLine = exec(
            escapeshellcmd(sprintf(
                '%s link %s',
                $bin,
                escapeshellarg($name)
            )) . ' 2>&1',
            $retArr,
            $retVal
        );

        if ($retVal !== 0) {
            Log::error($retArr);
            throw new \RuntimeException("webserver_manager failed linking: '$lastLine'");
        }

        return NginxParser::createFromString($parser->asString());
    }

    public function deleteVirtualHost($name, ConfigParserContract $vHost)
    {
        $bin = base_path('bin/webserver_manager');

        // Delete from /etc/nginx/sites-available/$name.conf
        // and /etc/nginx/sites-enabled/$name.conf
        $lastLine = exec(
            escapeshellcmd(sprintf(
                '%s delete %s',
                $bin,
                escapeshellarg($name)
            )) . ' 2>&1',
            $retArr,
            $retVal
        );

        if ($retVal !== 0) {
            Log::error($retArr);
            throw new \RuntimeException("webserver_manager failed creating: '$lastLine'");
        }

        // Delete root path of virtual host
        $rootPath = $vHost->getRootPath();

        if (!Str::startsWith($rootPath, '/var/www/') || Str::contains($rootPath, '..')) {
            return false;
        }

        exec(
            escapeshellcmd(sprintf(
                'rm -Rf %s',
                escapeshellarg($rootPath)
            )),
            $retArr,
            $retVal
        );

        if ($retVal !== 0) {
            Log::error($retArr);
            throw new \RuntimeException("Failed removing root directory for virtual host: '$lastLine'");
        }

        return true;
    }

    public function createSnippet($template, $name)
    {
        $parser = new TemplateParser($template);
        $parser->render();

        $tmpLocation = tempnam(sys_get_temp_dir(), 'webadmin-nginx-snippet');
        $bin = base_path('bin/webserver_manager');

        // Save to tmp first
        if (!$parser->asFile($tmpLocation)) {
            return;
        }

        // Create in /etc/nginx/snippets/$name.conf
        $lastLine = exec(
            escapeshellcmd(sprintf(
                '%s create-snippet %s %s',
                $bin,
                escapeshellarg($name),
                escapeshellarg($tmpLocation)
            )) . ' 2>&1',
            $retArr,
            $retVal
        );

        if ($retVal !== 0) {
            Log::error($retArr);
            throw new \RuntimeException("webserver_manager failed creating: '$lastLine'");
        }

        return NginxParser::createFromString($parser->asString());
    }

    public function getWebsiteConfigPath($domain)
    {
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

    public function test()
    {
        exec('sudo /etc/init.d/nginx configtest', $retArr, $retVal);

        return $retVal === 0 ? true : false;
    }
}
