<?php

declare(strict_types=1);

namespace Sphamster\Contracts;

interface Importable
{
    public function import(string $content): static;
}
