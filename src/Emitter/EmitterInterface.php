<?php

declare(strict_types=1);

namespace Haikara\FlatRoute\Emitter;

use Psr\Http\Message\ResponseInterface;

interface EmitterInterface
{
    /**
     * Responseを適切に出力する
     * Cookieを含むheaderを設定したのち、bodyを出力する
     * @param ResponseInterface $response
     * @return bool
     */
    public static function emit(ResponseInterface $response): bool;
}
