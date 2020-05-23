<?php

namespace App\Websites\Nginx;

use App\TemplateEngine\Parser;
use App\Websites\Contracts\ConfigParserContract;
use App\Websites\Contracts\WebserverContract;
use App\Websites\Nginx\ConfigParser\Config;

class Nginx implements WebserverContract
{
    public function createVirtualHost($template, $location, $data)
    {
        $parser = new Parser($template);
        $parser->render($data);

        // if (!$parser->asFile($location)) {
        //     return;
        // }
        //
        // $enableLocation = str_replace('available', 'enabled', $location);
        // exec("chmod 755 $location");
        // exec("ln -sf $location $enableLocation");

        return Config::createFromString($parser->asString());
    }

    public function createSnippet($template, $location)
    {
        $parser = new Parser($template);
        $parser->render($data);

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

        if (!file_exists($rootPath)) {
            mkdir($rootPath, 0755, true);
        }
    }

    public function getWebsiteConfigPath($domain)
    {
        $domain = escapeshellcmd($domain);
        return "/etc/nginx/sites-available/{$domain}.conf";
    }

    public function createSSLCertificate($domain, $registrantEmail)
    {
        $safeDomain = escapeshellarg($domain);
        $safeMail = escapeshellarg($registrantEmail);
        $iniFile = escapeshellarg(base_path('digitalocean.ini'));

        // First delete any current certifcate
        exec("certbot delete --cert-name $safeDomain 2>&1", $retArr);

        // Then create new certifcate
        $lastLine = exec(
            "certbot certonly --dns-digitalocean --dns-digitalocean-credentials $iniFile -m $safeMail -d $safeDomain -d www.$safeDomain 2>&1",
            $retArr,
            $retVal
        );

        if ($retVal !== 0) {
            throw new \RuntimeException("certbot failed: '$lastLine'");
        }
    }

    public function reload()
    {
        exec('systemctl reload nginx');
    }
}
