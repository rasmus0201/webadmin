<?php

namespace App\Contracts;

interface WebserverContract
{
    const DOMAIN = 'DOMAIN';

    /**
     * Create a new vHost
     *
     * @param string $template
     * @param string $location
     * @param array $data
     * @return \App\Parsers\Nginx\Parser
     */
    public function createVirtualHost($template, $location, $data);

    /**
     * Delete vHost
     *
     * @param string $name
     * @param ConfigParserContract $vHost
     * @return bool
     */
    public function deleteVirtualHost($name, ConfigParserContract $vHost);

    /**
     * Create new snippet for webserver engine
     *
     * @param string $template
     * @param string $location
     * @return \App\Parsers\Nginx\Parser
     */
    public function createSnippet($template, $location);

    /**
     * Get the full website config path by domain name
     *
     * @param string $domain
     * @return string
     */
    public function getWebsiteConfigPath($domain);

    /**
     * Get the filename of virtual host by domain name
     *
     * @param string $domain
     * @return string
     */
    public function getVirtualHostName($domain);

    /**
     * Reload webserver
     *
     * @return void
     */
    public function reload();

    /**
     * Test webserver configuration
     *
     * @return bool
     */
    public function test();
}
