<?php

namespace App\Websites\Contracts;

interface WebserverContract
{
    const DOMAIN = 'DOMAIN';

    public function createVirtualHost($template, $location, $data);
    public function createSnippet($template, $location);
    public function getWebsiteConfigPath($domain);
    public function getVirtualHostName($domain);
    public function createRootDirectory(ConfigParserContract $vHost);
    public function reload();
}
