<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\StreamInterface;

class HTTPStreamReader extends AbstractStreamReader
{
    protected ?StreamInterface $stream = null;

    public function __construct(
        protected string $url,
    ) {
        $response = (new Client())->get($this->url, [
            RequestOptions::STREAM => true,
        ]);

        $this->stream = $response->getBody();
    }

    /**
     * @inheritDoc
     */
    public static function supports(string $path): bool
    {
        return filter_var($path, FILTER_VALIDATE_URL);
    }

    /**
     * @inheritDoc
     */
    public function readBytes(int $length): ?string
    {
        return $this->stream->read($length);
    }

    /**
     * @inheritDoc
     */
    public function close(): void
    {
        if ($this->stream === null) {
            return;
        }

        $this->stream->close();
        $this->stream = null;
    }
}
