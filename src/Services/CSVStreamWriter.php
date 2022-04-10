<?php

namespace App\Services;

class CSVStreamWriter extends AbstractStreamWriter
{
    protected bool $headersWritten = false;

    /**
     * @inheritDoc
     */
    public static function supports(string $path): bool
    {
        return strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'csv';
    }

    /**
     * @inheritDoc
     */
    public function writeLine(array $data): bool
    {
        $success = true;
        if (! $this->headersWritten) {
            $success = $this->stream->fputcsv(array_keys($data)) !== false;
            $this->headersWritten = true;
        }

        return $success && $this->stream->fputcsv(array_values($data)) !== false;
    }
}
