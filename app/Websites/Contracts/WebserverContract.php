<?php

namespace App\Websites\Contracts;

interface WebserverContract
{
    const DOMAIN = 'DOMAIN';

    public function createVirtualHost($template, $location, $data);
    public function createSnippet($template, $location);
    public function getWebsiteConfigPath($domain);
    public function createSSLCertificate($domain, $registrantEmail);
    public function reload();
}
