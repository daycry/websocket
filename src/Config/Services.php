<?php namespace Daycry\Websocket\Config;

use CodeIgniter\Config\BaseService;
use CodeIgniter\Config\BaseConfig;
use Daycry\Websocket\Libraries\Websocket;

class Services extends BaseService
{
    public static function websocket(BaseConfig $config = null, bool $getShared = true)
    {
        if( $getShared )
        {
            return static::getSharedInstance( 'websocket', $config );
        }

        if( empty( $config ) )
        {
            $config = config( 'Websocket' );
        }

        return new Websocket( $config );
    }
}