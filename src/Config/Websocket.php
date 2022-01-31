<?php namespace Daycry\Websocket\Config;

use CodeIgniter\Config\BaseConfig;

class Websocket extends BaseConfig
{
    public $host = "0.0.0.0";
    public $port = 8282;
    public $timer = false;
    public $interval = 1;
    public $auth = false;
    public $debug = false;

    public $callbacks = [ 'auth', 'event', 'close', 'timer', 'roomjoin', 'roomleave', 'room' ];

    /**
     * Server Configuration
     */
    public $serverClass = \Daycry\Websocket\Server\Server::class;

    /**
     * JWT Token configuration
     */
    public $jwtClass = \Daycry\Websocket\Libraries\JWT::class;
    public $jwtConfigClass = \Daycry\Websocket\Config\JWT::class;
}