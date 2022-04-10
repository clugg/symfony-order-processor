<?php

namespace App\Services;

class JSONLStreamWriter extends AbstractStreamWriter
{
    /**
     * @inheritDoc
     */
    public static function supports(string $path): bool
    {
        return strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'jsonl';
    }

    /**
     * @inheritDoc
     */
    public function writeLine(array $data): bool
    {
        return $this->stream->fwrite(json_encode($data) . PHP_EOL);
    }
}
