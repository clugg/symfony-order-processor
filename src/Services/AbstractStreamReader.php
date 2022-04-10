<?php

namespace App\Services;

use Generator;

abstract class AbstractStreamReader
{
    public static int $CHUNK_SIZE = 1024;

    protected string $buffer = '';

    /**
     * Whether or not this stream reader supports reading from the path provided.
     *
     * @param string $path
     * @return boolean
     */
    abstract public static function supports(string $path): bool;

    /**
     * Reads the specified number of bytes from the stream.
     * Retursn null if the stream is exhausted.
     *
     * @param integer $length
     * @return string|null
     */
    abstract protected function readBytes(int $length): ?string;

    /**
     * Reads the next line in the stream.
     * Returns null if the stream is exhausted.
     *
     * @return string|null
     */
    public function readLine(): ?string
    {
        // if our buffer doesn't already contain a new line, read until it does
        if (strpos($this->buffer, "\n") === false) {
            $reachedNewLine = false;
            while (!$reachedNewLine) {
                $chunk = $this->readBytes(self::$CHUNK_SIZE);
                if (empty($chunk)) {
                    return $this->buffer ?: null;
                }

                $reachedNewLine = strpos($chunk, "\n") !== false;
                $this->buffer .= $chunk;
            }
        }

        // extract the first line contained in the buffer
        list($line, $this->buffer) = explode("\n", $this->buffer, 2);

        return $line;
    }

    /**
     * Returns a generator of lines from the stream.
     *
     * @return Generator|string[]
     */
    public function lines(): Generator
    {
        while (($line = $this->readLine()) !== null) {
            $line = trim($line);

            if (! empty($line)) {
                yield $line;
            }
        }
    }

    /**
     * Closes the stream.
     *
     * @return void
     */
    abstract public function close(): void;
}
