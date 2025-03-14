<?php

declare(strict_types=1);

namespace Sphamster\Contracts;

use Sphamster\Bayes;

interface Decoder
{
    /**
     * @param array<string, mixed> $data
     */
    public function decode(array $data): void;

    public function fromBayes(Bayes $classifier): static;
}
