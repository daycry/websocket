<?php namespace Daycry\Websocket\Libraries;

use Daycry\Websocket\Config\Websocket as ConfigWebsocket;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;

class Websocket
{
    public ConfigWebsocket $config;

    public bool $auth = false;

    public bool $debug = false;

    public array $callback = [];

    // initiate library, check for existing Configuration
    public function __construct( ConfigWebsocket $config )
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
                    new $this->config->serverClass($this->config, $this->callback)
                )
            ),
            $this->config->port,
            $this->config->host
        );

        if ($this->config->debug)
        {
            output('success', 'Web Socket server on host ' . $this->config->host . ':' . $this->config->port);
        }

        if ($this->config->timer)
        {
            $server->loop->addPeriodicTimer($this->config->interval, function ()
            {
                if( !empty( $this->callback['timer'] ) )
                {
                    call_user_func_array( $this->callback['timer'], array( date( 'Y-m-d H:i:s', time() ) ) );
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