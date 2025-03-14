<?php

declare(strict_types=1);

namespace Sphamster\Concerns;

use Sphamster\Bayes;

trait FromBayes
{
    protected Bayes $classifier;

    public function fromBayes(Bayes $classifier): static
    {
        $this->classifier = $classifier;
        return $this;
    }
}
