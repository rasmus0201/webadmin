<?php

namespace App\Contracts;

interface ConfigParserContract
{
    public function __construct($path = null);
    public function parseString($source);
    public function asString();
    public function save($path = null);

    public function getRootPath();
    public function getPublicPath();

    public static function createFromFile($path);
    public static function createFromString($content);
}
