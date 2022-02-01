<?php namespace Daycry\Websocket\Server;

use CodeIgniter\Config\BaseConfig;

use Ratchet\ConnectionInterface;

abstract class AbstractServer
{
    protected $clients = [];

    protected BaseConfig $config;

    protected array $rooms = [];

    protected array $callback = array();

    public function __construct( BaseConfig $config, array $callback = [] )
    {
        // Initialize object as SplObjectStorage (see PHP doc)
        //$this->clients = new \SplObjectStorage();

        helper( 'text' );
        helper( 'websocket' );

        // save configuration
        $this->config = $config;

        $this->callback = $callback;

        // // Check if auth is required
        if( $this->config->auth && empty( $this->callback['auth'] ) )
        {
            output('fatal', 'Authentication callback is required, you must set it before run server, aborting..');
        }

        // Output
        if( $this->config->debug )
        {
            output('success', 'Running server on host ' . $this->config->host . ':' . $this->config->port);
        }

        // Output
        if( !empty( $this->callback['auth'] ) && $this->config->debug )
        {
            output('success', 'Authentication activated');
        }

        // Output
        if( !empty( $this->callback['close'] ) && $this->config->debug )
        {
            output('success', 'Close activated');
        }
    }

    /**
     * Function to send the message
     * @method send_message
     * @param array $user User to send
     * @param array $message Message
     * @param array $client Sender
     * @return string
     */
    protected function sendMessage( ConnectionInterface $user, $message, ConnectionInterface $client )
    {
        // Send the message
        $user->send($message);

        // We have to check if event callback must be called
        if( !empty($this->callback['event'] ) )
        {
            // At this moment we have to check if we have authent callback defined
            call_user_func_array( $this->callback['event'], array( ( valid_json( $message ) ? json_decode( $message ) : $message ) ) );

            // Output
            if( $this->config->debug )
            {
                output('info', 'Callback event "' . $this->callback['event'][1] . '" called');
            }
        }

        // Output
        if( $this->config->debug )
        {
            output('info', 'Client (' . $client->resourceId . ') send \'' . $message . '\' to (' . $user->resourceId . ')');
        }
    }

    protected function _findSendMessage( $users, $recipientId, $message, ConnectionInterface $client )
    {
        foreach( $users as $key => $user )
        {
            if( $user->subscriber_id == $recipientId )
            {
                $this->sendMessage( $user, $message, $client );
                break;
            }
        }
    }

    protected function _AllSendMessage( $users, $message, ConnectionInterface $client )
    {
        foreach( $users as $key => $user )
        {
            $this->sendMessage( $user, $message, $client );
        }
    }

    protected function _checkRoom( $name )
    {
        foreach( $this->rooms as $room )
        {
            if( $room->roomName == $name )
            {
                return $room;
            }
        }

        return false;
    }

    protected function _updateRoom( $room, $name )
    {
        $this->room[ $name ] = $room;
        /*foreach( $this->rooms as &$r )
        {
            if( $r->getRoomName() == $room->getRoomName() )
            {
                $r = $room;
            }
        }*/
    }
}