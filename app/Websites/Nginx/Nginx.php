<?php

namespace App\Websites\Nginx;

use App\TemplateEngine\Parser;
use App\Websites\Contracts\WebserverContract;

class Nginx implements WebserverContract
{
    private $template;
    private $parser;
    private $type;

    public function template($template)
    {
        $this->template = $template;

        return $this;
    }

    public function createWebsite($data)
    {
        $parser = new Parser($this->template);

        $this->parser = $parser->render($data);
        $this->type = 'website';
    }

    public function createSnippet()
    {
        $parser = new Parser($this->template);

        $this->parser = $parser->render();
        $this->type = 'snippet';
    }

    public function save($location)
    {
        if (!$this->parser->asFile($location)) {
            return;
        }

        exec("chmod 755 $location");
        if ($this->type === 'website') {
            $enableLocation = str_replace('available', 'enabled', $location);
            exec("ln -s {$location} {$enableLocation}");
        }
    }

    public function reload()
    {
        exec('service nginx reload');
    }

    public function getWebsiteConfigPath($domain)
    {
        $domain = escapeshellcmd($domain);
        return "/etc/nginx/sites-available/{$domain}.conf";
    }
}
