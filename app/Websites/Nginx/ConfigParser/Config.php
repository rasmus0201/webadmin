<?php

namespace App\Websites\Nginx\ConfigParser;

use App\Websites\Contracts\ConfigParserContract;

class Config extends DirectiveCollection implements ConfigParserContract
{
    /**
     * Path to parse from
     *
     * @var string
     */
    private $path;

    /**
     * The conf file path.
     *
     * @var string
     */
    public function __construct($path = null)
    {
        parent::__construct();

        $this->path = $path;

        if ($path != null && $path != '' && file_exists($path)) {
            $content = file_get_contents($this->path);
            $this->parseString($content);
        }
    }

    /**
     * Parses a config data from string.
     *
     * @param string $source The data to parse.
     *
     * @return void
     */
    public function parseString($source)
    {
        if (!isset($source) || $source == '') {
            return;
        }

        // TODO: There may be potential error. Check it.
        // Masked \#
        $source = preg_replace('/(\\\#)/', chr(1), $source);

        // Remove comments
        $source = preg_replace('/(\#([^\r\n]*))/', '', $source);

        // Restore \#
        $source = preg_replace('/\x01/', '\#', $source);

        // Parse
        $this->parse($this, $source, 0);
    }

    /**
     * Returns current instance as string.
     *
     * @return string
     */
    public function asString()
    {
        if (!isset($this->items) || count($this->items) == 0) {
            return '';
        }

        $stream = fopen('php://memory', 'w+');

        if ($stream === false) {
            throw new \ErrorException('Culd not convert the instance to string.');
        }

        $this->writeToFile($stream, $this, 0);

        // Remove last line break
        ftruncate($stream, ftell($stream) - 1);

        // Read
        rewind($stream);
        $result = stream_get_contents($stream);

        // Close
        fclose($stream);

        return $result;
    }

    /**
     * Saves the configuration file.
     *
     * @param string $path path to save. If not specified, will use the file path specified during initialization.
     */
    public function save($path = null)
    {
        if ($path == null || $path == '') {
            $path = $this->path;
        }

        if ($path == null || $path == '') {
            throw new \InvalidArgumentException('Could not save to file. Path is required, value can not be empty.');
        }

        if (!isset($this->items) || count($this->items) == 0) {
            throw new \ErrorException('No data to save.');
        }

        $file = fopen($path, 'w+');

        if ($file === false) {
            throw new \ErrorException('Could not save to file.');
        }

        $this->writeToFile($file, $this, 0);

        // Remove last line break
        ftruncate($file, ftell($file) - 1);

        fclose($file);
    }

    /**
     * Parses a config data.
     *
     * @param DirectiveCollection $parent An instance of collection in which to place the result of data parsing.
     * @param string $source The data to parse.
     * @param int $level The nesting level. Default: 0.
     *
     * @return void
     */
    private function parse($parent, $source, $level = 0)
    {
        // Masking quotes
        $masked = $this->maskingQuotes($source);

        // Search and each derictives
        preg_match_all('/^(\s*)(?<directive>[\w\d\x5F]+)(\s+)/im', $masked, $matches, PREG_OFFSET_CAPTURE);

        $index = 0;
        $maxEndIdx = -1;

        for ($i = 0; $i < count($matches['directive']); $i++) {
            $directive = $matches['directive'][$i];
            $index = $directive[1];

            // Data may have already been processed
            if ($index <= $maxEndIdx) {
                continue;
            }

            // Get name
            $name = trim($directive[0]);
            // Get name size
            $len = strlen($name);
            // Determining the type of directive
            $block = strpos($masked, '{', $index + $len);
            $simple = strpos($masked, ';', $index + $len);
            if ($block === false) {
                $type = DirectiveType::NGINX_DIRECTIVE_SIMPLE;
            } elseif ($simple === false) {
                $type = DirectiveType::NGINX_DIRECTIVE_BLOCK;
            } else {
                $type = min([$block, $simple]) == $block ? DirectiveType::NGINX_DIRECTIVE_BLOCK : DirectiveType::NGINX_DIRECTIVE_SIMPLE;
            }

            if ($type == DirectiveType::NGINX_DIRECTIVE_SIMPLE) {
                $parameters = trim(substr($source, $index + $len, $simple - ($index + $len)));
                if ($parent->containsDirective($name)) {
                    if ($parameters == '') {
                        // no parameters, ignore it
                        continue;
                    }
                    // is not unique derictive name and not group, transform to group
                    if (!$parent->items[$name]->isGroup()) {
                        $group = new DirectiveGroup($name);
                        $group->addDirective($parent->items[$name]->parameters);
                        $group->addDirective($this->parseParameters($parameters));
                        $parent->items[$name] = $group;
                    } else {
                        // is group
                        $parent->items[$name]->addDirective($this->parseParameters($parameters));
                    }
                } else {
                    // new derictive
                    $parent->add(new Directive($name, $this->parseParameters($parameters)));
                }
            } else {
                // Search end border
                if (($endIdx = $this->searchEndsBlock($masked, $block + 1)) === false) {
                    continue;
                }

                // Get parameters
                $parameters = trim(substr($source, $index + $len, $block - ($index + $len)));
                if ($parameters != '') {
                    $parameters = $this->parseParameters($parameters);
                } else {
                    $parameters = null;
                }

                // Is unique directive name
                if ($parent->containsDirective($name)) {
                    // Is not unique derictive name and not group, transform
                    if (!$parent[$name]->isGroup()) {
                        $group = new DirectiveGroup($name);
                        $group->addDirective($parent->items[$name]);
                        $group->addDirective($parameters);
                        $parent->items[$name] = $group;
                    } else {
                        // Is group, add new element
                        $parent->items[$name]->addDirective($parameters);
                    }
                    // Get last element
                    $new = $parent->items[$name]->lastChild()->directives;
                } else {
                    // New derictive
                    $new = $parent->add(new Directive($name, $parameters))->directives;
                }

                // Get block content
                $body = substr($source, $block + 1, $endIdx - $block - 1);

                // Parse block
                $this->parse($new, $body, $level + 1);

                // Remember index
                if ($endIdx > $maxEndIdx) {
                    $maxEndIdx = $endIdx;
                }
            }
        }
    }

    /**
     * Parses a string parameter and returns an array.
     *
     * @param string $source The string to parse.
     *
     * @return array
     */
    private function parseParameters($source)
    {
        $masked = $this->maskingQuotes($source);
        $result = [];
        $last = 0;

        while (($start = strpos($masked, ' ', $last)) !== false) {
            if ($last == $start) {
                $last++;
                continue;
            }
            $result[] = $this->dequote(substr($source, $last, $start - $last));
            $last = $start + 1;
        }

        if ($last == 0 && $source != '') {
            $result[] = $this->dequote($source);
        } elseif ($last != strlen($source) && substr($source, $last) != '') {
            $result[] = $this->dequote(substr($source, $last));
        }

        return $result;
    }

    /**
     * Removes quotes left and right.
     *
     * @param string $value The text for processing.
     *
     * @return string
     */
    private function dequote($value)
    {
        if ($value == null || strlen($value) == 0) {
            return '';
        }

        return preg_replace('/^([\"\']{1})(.*)\1$/', '\2', $value);
    }

    /**
     * Masking a text in quotes.
     *
     * @param string $source The text to parse.
     */
    private function maskingQuotes($source)
    {
        $maxEndIdx = -1;
        preg_match_all('/[\"\']{1}/m', $source, $matches, PREG_OFFSET_CAPTURE);

        foreach ($matches[0] as $m) {
            $start = $m[1];

            // Data may have already been processed
            if ($start <= $maxEndIdx) {
                continue;
            }

            // Search closed quotes
            if (($end = $this->searchEndsBlock($source, $start + 1, $m[0], $m[0], '\\')) === false) {
                continue;
            }

            // Masking (chr(2) - any unused char)
            $source = substr_replace($source, str_repeat(chr(2), $end - $start + 1), $start, $end - $start + 1);

            // Remember index
            if ($end > $maxEndIdx) {
                $maxEndIdx = $end;
            }
        }

        return $source;
    }

    /**
     * Searchs end block.
     *
     * @param string $source The source.
     * @param int $start The start position.
     * @param string $open The string that indicates the beginning of the block. Default: {.
     * @param string $close The string that indicates the ending of the block. Default: }.
     * @param string $escape Set the escape character. Defaults as a backslash (\).
     * @return string
     */
    private function searchEndsBlock($value, $start, $open = '{', $close = '}', $escape = '\\')
    {
        // Search from the beginning of the end of the block parent
        $endIdx = $this->searchChar($value, $close, $start, $escape);

        // Search for the next open-block from the start of the parent block
        $nextOpenIndex = $this->searchChar($value, $open, $start, $escape);

        $openingCount = 0;

        while (($nextOpenIndex !== false && $nextOpenIndex < $endIdx) || $openingCount > 0) {
            // Select all from the start of parent to the end found
            $part = substr($value, $start, $endIdx - $start);

            // Counting the number of blocks
            $openingCount += substr_count($part, $open);

            // Closed block is the latest open
            $openingCount--;

            // Search the next closed block from the current closed
            $start = $endIdx + strlen($close);
            if ($this->searchChar($value, $close, $start, $escape) === false) {
                break;
            }

            // Search the next open block from the closed
            $endIdx = $this->searchChar($value, $close, $start, $escape);
        }

        return $endIdx;
    }

    /**
     * Searches are not escapes in the specified string.
     *
     * @param string $value
     * @param string $search
     * @param int $start
     * @param string $escap
     *
     * @return int|bool
     */
    private function searchChar($value, $search, $start, $escape = '\\')
    {
        $result = false;

        while (($result = strpos($value, $search, $start)) !== false) {
            // Check prev char
            if (($prevChar = substr($value, $start - 1 - strlen($escape), 1)) === false || $prevChar != $escape) {
                break;
            }
        }

        return $result;
    }

    /**
     * Writes a directives to file.
     *
     * @param resource $file A file system pointer resource that is typically created using fopen().
     * @param DirectiveCollection $directives Directives to save.
     * @param int $level Directive nesting level (it affects the number of indents).
     *
     * @return void
     */
    private function writeToFile($file, $directives, $level, $groupName = null)
    {
        if (!isset($directives) || count($directives) == 0) {
            return;
        }

        $type = null;
        $i = 0;
        $count = $directives->count();
        foreach ($directives->items as $directive) {
            // Is group
            if ($directive->isGroup() && $directive->hasChild()) {
                // Additional line break
                if ($type == DirectiveType::NGINX_DIRECTIVE_SIMPLE && !$directive->isSimple()) {
                    fwrite($file, "\n");
                }

                // Write array
                $this->writeToFile($file, $directive->directives, $level, $directive->name);
                continue;
            }

            # Write indents
            fwrite($file, str_repeat("\t", $level));

            # Check for parameters or children
            if (!$directive->hasParameters() && !$directive->hasChild()) {
                // Directive is empty, write warning message and skip
                fwrite($file, sprintf("# Warning: The directive \"%s\" is empty.\n", ($groupName == null ? $directive->name : $groupName."[".$directive->name."]")));
                continue;
            }

            // Write directive name
            if ($groupName == null) {
                fwrite($file, $directive->name);
            } else {
                fwrite($file, $groupName);
            }

            fwrite($file, " ");

            // Write parameters
            if ($directive->hasParameters()) {
                fwrite($file, $directive->parametersAsString());
            }

            // Write directive body
            if ($directive->hasChild()) {
                // Additional space
                if ($directive->hasParameters()) {
                    fwrite($file, " ");
                }

                // Open block
                fwrite($file, "{\n");

                // Write childs
                $this->writeToFile($file, $directive->directives, $level + 1);

                // Write indents
                fwrite($file, str_repeat("\t", $level));

                // Close block
                fwrite($file, "}\n");
                $type = DirectiveType::NGINX_DIRECTIVE_BLOCK;

                // Additional line break
                if ($i < $count - 1) {
                    fwrite($file, "\n");
                }
            } else {
                // Close simply directive
                fwrite($file, ";\n");
                $type = DirectiveType::NGINX_DIRECTIVE_SIMPLE;
            }

            $i++;
        }
    }

    /**
     * Creates a new Directive.
     *
     * @param string $name The directive name.
     * @param string|string[] $parameters The list of parameters. Default: NULL.
     *
     * @return Directive
     */
    public static function createDirective($name, $parameters = null)
    {
        return new Directive($name, $parameters);
    }

    /**
     * Creates a new DirectiveGroup.
     *
     * @param string $name The group name.
     *
     * @return DirectiveGroup
     */
    public static function createGroup($name)
    {
        return new DirectiveGroup($name);
    }

    /**
     * Creates a new instance of the Config class from file path.
     *
     * @param string $path The path to config file.
     *
     * @return Config
     */
    public static function createFromFile($path)
    {
        return new Config($path);
    }

    /**
     * Creates a new instance of the Config class from string value.
     *
     * @param string $content The config file data.
     *
     * @return Config
     */
    public static function createFromString($content)
    {
        $result = new Config();
        $result->parseString($content);
        return $result;
    }

    /**
     * Creates a new instance of the Config class.
     *
     * @return Config
     */
    public static function create()
    {
        return new Config();
    }
}
