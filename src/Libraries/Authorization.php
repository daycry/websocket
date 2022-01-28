<?php namespace Daycry\Websocket\Libraries;

use Daycry\Websocket\Libraries\JWT;

class Authorization
{
    public static function validateToken($token)
    {
        $config = config( 'Websocket' );

        $jwtClass = new $config->jwtClass( new $config->jwtConfigClass() );
        return $jwtClass->decode( $token );
    }

    public static function generateToken($data)
    {
        $config = config( 'Websocket' );

        $jwtClass = new $config->jwtClass( new $config->jwtConfigClass() );

        return $jwtClass->encode( $data );
    }
}