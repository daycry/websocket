<?php namespace Daycry\Websocket\Config;

use CodeIgniter\Config\BaseService;
use CodeIgniter\Config\BaseConfig;

/**
 * @package   CodeIgniter WebSocket Library: Default config service
 * @category  Libraries
 * @author    Taki Elias <taki.elias@gmail.com>
 * @license   http://opensource.org/licenses/MIT > MIT License
 * @link      https://github.com/takielias
 *
 * CodeIgniter WebSocket library. It allows you to make powerful realtime applications by using Ratchet Websocket
 */

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

        return new \Daycry\Websocket\Libraries\Websocket( $config );
    }
}