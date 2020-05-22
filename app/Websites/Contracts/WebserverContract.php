<?php

namespace App\Websites\Contracts;

interface WebserverContract
{
    const DOMAIN = 'DOMAIN';

    public function template($template);
    public function createWebsite($data);
    public function createSnippet();
    public function save($location);
    public function getWebsiteConfigPath($domain);
}
