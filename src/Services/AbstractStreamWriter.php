<?php

namespace App\Services;

use SplFileObject;

abstract class AbstractStreamWriter
{
    protected ?SplFileObject $stream = null;

    public function __construct(
        protected string $path,
    ) {
        $this->stream = new SplFileObject($this->path, 'w');
    }

    /**
     * Whether or not this stream writer supports writing to the path provided.
     *
     * @param string $path
     * @return boolean
     */
    abstract public static function supports(string $path): bool;

    /**
     * Processes the data into the expected format and writes it to the stream.
     *
     * @param array $data
     * @return boolean
     */
    abstract public function writeLine(array $data): bool;

    /**
     * Closes the stream.
     *
     * @return void
     */
    public function close(): void
    {
        $this->stream = null;
    }
}
