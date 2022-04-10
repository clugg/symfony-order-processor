<?php

namespace App\Services;

use SplFileObject;

class FileStreamReader extends AbstractStreamReader
{
    protected ?SplFileObject $stream = null;

    public function __construct(
        protected string $path,
    ) {
        $this->stream = new SplFileObject($this->path);
    }

    /**
     * @inheritDoc
     */
    public static function supports(string $path): bool
    {
        return file_exists($path);
    }

    /**
     * @inheritDoc
     */
    public function readBytes(int $length): ?string
    {
        if ($this->stream === null) {
            return null;
        }

        return $this->stream->fread($length);
    }

    /**
     * @inheritDoc
     */
    public function close(): void
    {
        $this->stream = null;
    }
}
