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
    public $jwt_key = "GGFSRTSYTSOPLGCCXS";
    public $token_timeout = 1;

    public $callbacks = [ 'auth', 'event', 'close', 'timer' ];
}