<?php

declare(strict_types=1);

namespace Haikara\FlatRoute\Emitter;

use Psr\Http\Message\ResponseInterface;

class ResponseEmitter implements EmitterInterface
{
    /**
     * @inheritDoc
     */
    public static function emit(ResponseInterface $response): bool
    {
        $statusCode = $response->getStatusCode();

        foreach ($response->getHeaders() as $header => $values) {
            $name = ucwords($header, '-');
            $replace = $name !== 'Set-Cookie';

            foreach ($values as $value) {
                header("{$name}: {$value}", $replace, $statusCode);
            }
        }

        $protocolVer = $response->getProtocolVersion();
        $reasonPhrase = $response->getReasonPhrase();
        $responsePhrase = ($reasonPhrase !== '') ? ' ' . $reasonPhrase : '';

        header("HTTP/{$protocolVer} {$statusCode}{$responsePhrase}", true, $statusCode);

        echo $response->getBody();

        return true;
    }
}
