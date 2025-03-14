<?php

declare(strict_types=1);

namespace Sphamster\Decoders;

use Sphamster\Concerns\FromBayes;
use Sphamster\Contracts\Decoder;
use Sphamster\Exception\JsonCorruptedException;

class JsonDecoder implements Decoder
{
    use FromBayes;

    /**
     * @param array<string, mixed> $data
     * @throws JsonCorruptedException
     */
    public function decode(array $data): void
    {
        $decoders = [
            (new TotalDocumentDecoder())->fromBayes($this->classifier),
            (new VocabularyDecoder())->fromBayes($this->classifier),
            (new CategoryDecoder())->fromBayes($this->classifier),
        ];

        foreach ($decoders as $decoder) {
            $decoder->decode($data);
        }
    }
}
