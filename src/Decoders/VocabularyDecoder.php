<?php

declare(strict_types=1);

namespace Sphamster\Decoders;

use Sphamster\Concerns\FromBayes;
use Sphamster\Contracts\Decoder;
use Sphamster\Exception\JsonCorruptedException;

class VocabularyDecoder implements Decoder
{
    use FromBayes;

    /**
     * @param array<string, mixed> $data
     * @throws JsonCorruptedException
     */
    public function decode(array $data): void
    {
        if ( ! isset($data['vocabulary']) || ! is_array($data['vocabulary'])) {
            throw new JsonCorruptedException();
        }

        foreach ($data['vocabulary'] as $token) {
            if (is_string($token)) {
                $this->classifier->vocabulary()->add($token);
            }
        }
    }
}
