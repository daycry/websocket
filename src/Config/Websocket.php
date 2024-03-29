<?php 
namespace Daycry\Websocket\Config;

use CodeIgniter\Config\BaseConfig;
use Daycry\Websocket\Server\AbstractServer;
use Daycry\Websocket\Server\Server;

class Websocket extends BaseConfig
{
    public string $host = "0.0.0.0";
    public int $port = 8282;
    public bool $timer = false;
    public int $interval = 1;
    public bool $auth = false;
    public bool $debug = false;

    public array $callbacks = [ 'auth', 'event', 'close', 'timer', 'roomjoin', 'roomleave', 'room' ];

    public string $serverClass = Server::class;
}