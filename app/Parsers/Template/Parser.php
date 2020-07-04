<?php

namespace App\Parsers\Template;

class Parser
{
    /**
     * @var string
     */
    private $template;

    /**
     * @var string
     */
    private $parsed;

    /**
     * Constructor.
     *
     * @param string $template
     */
    public function __construct($template = '')
    {
        $this->template = $template;
        $this->parsed = '';
    }

    /**
     * Render a template
     *
     * @param  array  $placeholders
     *
     * @throws If placeholders is malformed
     *
     * @return this
     */
    public function render(array $placeholders = [])
    {
        if (!is_object($placeholders) && !is_array($placeholders)) {
            throw new \Exception('Placeholder must be type of object or array');
        }

        // Make sure $placeholders is cast to array
        $placeholders = (array) $placeholders;

        $bufferedTemplate = $this->template;
        if (file_exists($this->template) && is_readable($this->template)) {
            $bufferedTemplate = file_get_contents($this->template);
        }

        if (!empty($placeholders)) {
            foreach ($placeholders as $find => $replace) {
                $bufferedTemplate = preg_replace('/{{\s*'.$find.'\s*}}/', $replace, $bufferedTemplate);
            }
        }

        $this->parsed = $bufferedTemplate;

        return $this;
    }

    /**
     * Return template as string
     *
     * @return string
     */
    public function asString()
    {
        return $this->parsed;
    }

    /**
     * When object is stringified
     *
     * @return string
     */
    public function __toString()
    {
        return $this->asString();
    }

    /**
     * When template should be stored as a file
     *
     * @param  string $location
     *
     * @throws If the file could not be stored.
     *
     * @return bool
     */
    public function asFile($location)
    {
        try {
            $handle = fopen($location, 'w');
        } catch (\Exception $e) {
            throw new \Exception('Location must be valid location', 1);
        }

        fwrite($handle, $this->parsed);
        fclose($handle);

        return true;
    }
}
