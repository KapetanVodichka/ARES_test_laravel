<?php

namespace App\Services\Parsers;

interface ParserInterface
{
    public function parse(string $data): array;
}
