<?php

namespace App\Contracts;

interface WebserverContract
{
    const DOMAIN = 'DOMAIN';

    public function createVirtualHost($template, $location, $data);
    public function deleteVirtualHost($name, ConfigParserContract $vHost);
    public function createSnippet($template, $location);
    public function getWebsiteConfigPath($domain);
    public function getVirtualHostName($domain);
    public function reload();
    public function test();
}
