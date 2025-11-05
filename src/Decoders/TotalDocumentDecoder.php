<?php

declare(strict_types=1);

namespace Sphamster\Decoders;

use Sphamster\Concerns\FromBayes;
use Sphamster\Contracts\Decoder;
use Sphamster\Exception\JsonCorruptedException;

class TotalDocumentDecoder implements Decoder
{
    use FromBayes;

    /**
     * @param array<string, mixed> $data
     * @throws JsonCorruptedException
     */
    public function decode(array $data): void
    {
        if ( ! isset($data['totalDocuments']) || ! is_numeric($data['totalDocuments'])) {
            throw new JsonCorruptedException();
        }

        $this->classifier->state()->totalDocuments((int) $data['totalDocuments']);
    }
}
