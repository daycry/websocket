<?php namespace Daycry\Websocket\Exceptions;

use CodeIgniter\Exceptions\ExceptionInterface;
use CodeIgniter\Exceptions\FrameworkException;

class WebsocketException extends FrameworkException implements ExceptionInterface
{
    public static function forMissingName()
    {
        return new static(lang('Websocket.missingName'));
    }

    public static function forUnmatchedName(string $name)
    {
        return new static(lang('Websocket.unmatchedName', [$name]));
    }

    public static function forProtectionViolation(string $name)
    {
        return new static(lang('Websocket.protectionViolation', [$name]));
    }
}