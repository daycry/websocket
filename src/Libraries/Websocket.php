<?php namespace Daycry\Websocket\Libraries;

use CodeIgniter\Config\BaseConfig;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;

class Websocket
{
    public BaseConfig $config;
    
    public ?string $host = null;

    public ?string $port = null;

    public bool $auth = false;

    public bool $debug = false;

    public array $callback = [];

    // initiate library, check for existing Configuration
    public function __construct( BaseConfig $config )
    {
        helper('websocket');
        $this->config = $config;
    }

    public function run()
    {
        // Initialize all the necessary class
        $server = IoServer::factory(
            new HttpServer(
                new WsServer(
                    new Server($this->config, $this->callback)
                )
            ),
            $this->config->port,
            $this->config->host
        );

        if ($this->config->debug)
        {
            output('success', 'Running server on host ' . $this->config->host . ':' . $this->config->port);
        }

        if ($this->config->timer)
        {
            $server->loop->addPeriodicTimer($this->config->interval, function ()
            {
                if (!empty($this->callback['citimer']))
                {
                    call_user_func_array($this->callback['citimer'], array(date('d-m-Y h:i:s a', time())));
                }
            });
        }

        $server->run();
    }

    public function set_callback($type = null, array $callback = array())
    {
        // Check if we have an authorized callback given
        if (!empty($type) && in_array($type, $this->config->callbacks))
        {
            if (is_callable($callback))
            {
                $this->callback[$type] = $callback;
            } else {
                output('fatal', 'Method ' . $callback[1] . ' is not defined');
            }
        }
    }
}