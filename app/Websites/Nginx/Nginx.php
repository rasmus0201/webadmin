<?php

namespace App\Websites\Nginx;

use App\TemplateEngine\Parser;
use App\Websites\Contracts\ConfigParserContract;
use App\Websites\Contracts\WebserverContract;
use App\Websites\Nginx\ConfigParser\Config;
use Illuminate\Support\Facades\Log;

class Nginx implements WebserverContract
{
    public function createVirtualHost($template, $location, $data)
    {
        $parser = new Parser($template);
        $parser->render($data);

        if (!$parser->asFile($location)) {
            return;
        }

        $enableLocation = str_replace('available', 'enabled', $location);
        exec("chmod 755 $location");
        exec("ln -sf $location $enableLocation");

        return Config::createFromString($parser->asString());
    }

    public function createSnippet($template, $location)
    {
        $parser = new Parser($template);
        $parser->render();

        if (!$parser->asFile($location)) {
            return;
        }

        exec("chmod 755 $location");

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

    public function reload()
    {
        exec('sudo /etc/init.d/nginx reload');
    }
}
