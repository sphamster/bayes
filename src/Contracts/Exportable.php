<?php

declare(strict_types=1);

namespace Sphamster\Contracts;

interface Exportable
{
    public function export(): string;
}
