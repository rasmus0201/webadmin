<?php

namespace App\Websites\Contracts;

interface ConfigParserContract
{
    public function __construct($path = null);
    public function parseString($source);
    public function asString();
    public function save($path = null);

    public static function createFromFile($path);
    public static function createFromString($content);
}
