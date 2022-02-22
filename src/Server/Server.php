<?php namespace Daycry\Websocket\Server;

use CodeIgniter\Config\BaseConfig;

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

use Daycry\Websocket\Libraries\Authorization;
use Daycry\Websocket\Libraries\Room;
use Daycry\Websocket\Interfaces\ServerInterface;
use Daycry\Websocket\Server\AbstractServer;

class Server extends AbstractServer implements MessageComponentInterface, ServerInterface
{
    public function __construct( BaseConfig $config, array $callback = [] )
    {
        parent::__construct( $config, $callback );
    }

    /**
     * Event trigerred on new client event connection
     * @method onOpen
     * @param ConnectionInterface $connection
     * @return string
     */
    public function onOpen(ConnectionInterface $connection)
    {
        // Add client to global clients object
        //$this->clients->attach($connection);
        $this->clients[ $connection->resourceId ] = $connection;

        // Output
        if( $this->config->debug )
        {
            output( 'info', 'New client connected as (' . $connection->resourceId . ')' );
        }
    }

    /**
     * Event trigerred on new message sent from client
     * @method onMessage
     * @param ConnectionInterface $client
     * @param string $message
     * @return string
     */
    public function onMessage( ConnectionInterface $client, $message )
    {
        // Broadcast var
        $broadcast = false;
        $notify = true;
        $validJwt = true;
        $result = null;
        $recipients = [];

        // Check if received var is json format
        if( valid_json( $message ) )
        {
            // If true, we have to decode it
            $content = json_decode($message);

            // Once we decoded it, we check look for notify users
            $notify = ( isset( $content->notify ) && !empty( $content->notify ) && $content->notify == false ) ? false : true;

            // Once we decoded it, we check look for global broadcast
            $broadcast = (isset( $content->broadcast ) && !empty($content->broadcast) && $content->broadcast == true) ? true : false;

            // Count real clients numbers (-1 for server)
            $clients = count($this->clients) - 1;

            // Here we have to reassign the client ressource ID, this will allow us to send message to specified client.
            if( !empty( $content->user_id ) && empty( $client->subscriber_id ) )
            {
                if( !empty( $this->callback['auth'] ) )
                {
                    // Call user personal callback
                    $auth = call_user_func_array( $this->callback[ 'auth' ], array( $content ) );

                    // Verify authentication
                    if( empty( $auth ) )
                    {
                        output('error', 'Client (' . $client->resourceId . ') authentication failure');
                        $client->send(json_encode(array("type" => "error", "msg" => 'Invalid ID or Password.')));
                        // Closing client connexion with error code "CLOSE_ABNORMAL"
                        $client->close(1006);
                    }

                    // Add UID to associative array of subscribers
                    if( is_array( $auth ) || is_object( $auth ) )
                    {
                        foreach( $auth as $key => $value )
                        {
                            $client->{ $key } = $value;
                        }
                    }else{
                        $client->subscriber_id = $auth;
                    }

                    $this->clients[ $client->resourceId ] = $client;

                    if( $this->config->auth && $auth )
                    {
                        $data = json_encode(array("type" => "token", "token" => Authorization::generateToken($client->resourceId)));
                        $this->sendMessage($client, $data, $client);
                    }

                    // Output
                    if ($this->config->debug) {
                        output('success', 'Client (' . $client->resourceId . ') authentication success');
                        output('success', 'Token : ' . Authorization::generateToken($client->resourceId));
                    }
                }
            }

            if( !empty( $content ) )
            {
                if( !empty( $content->type ) )
                {
                    if( $this->config->auth )
                    {
                        if( isset( $content->token ) && $validJwt = valid_jwt( $content->token ) == false )
                        {
                            $client->send( json_encode( array("type" => "error", "message" => 'Invalid Token.' ) ) );
                        }else{
                            $validJwt = false;
                            // Closing client connexion with error code "CLOSE_ABNORMAL"
                            $client->close(1006);
                        }
                    }

                    if( $validJwt === true )
                    {
                        if( $content->type == 'roomjoin' || $content->type == 'roomleave' )
                        {
                            if( !isset( $content->room_name ) && empty( $content->room_name ) )
                            {
                                $client->send( json_encode( array("type" => "error", "message" => 'Invalid Room.' ) ) );
                            }else{
                                $room = $this->_checkRoom( $content->room_name );

                                if( $content->type == 'roomjoin' )
                                {
                                    //join room
                                    if( $room != false )
                                    {
                                        $room = $room->join($content, $client);
                                        $this->_updateRoom( $room, url_title( $content->room_name ) );
                                    }else{
                                        $room = New Room();
                                        if( isset( $content->room_limit ) )
                                        {
                                            $room->setRoomLimit( $content->room_limit );
                                        }
                                        
                                        $room->setRoomName( $content->room_name );
                                        $this->rooms[ url_title( $content->room_name ) ] = $room;
                                        $room = $room->join($content, $client);
                                        $this->_updateRoom( $room, url_title( $room->getRoomName() ) );
                                    }
                                }else{
                                    //leave room
                                    if( $room != false )
                                    {
                                        $room = $room->leave( $content, $client );
                                        $this->_updateRoom( $room, url_title( $room->getRoomName() ) );
                                    }
                                }

                                if( !empty($this->callback[ $content->type ] ) )
                                {
                                    // Call user personal callback
                                    $result = call_user_func_array( $this->callback[ $content->type ], array( $content, $client ) );
                                }
                            }

                        }else{
                            if( !empty($this->callback[ $content->type ] ) )
                            {
                                // Call user personal callback
                                $result = call_user_func_array( $this->callback[ $content->type ], array( $content, $client ) );
                            }
                        }

                        if( $notify )
                        {
                            $message = ( $result ) ? json_encode( $result ) : $message;
                            $recipientId = ( !empty( $content->recipient_id ) ) ? $content->recipient_id : false;

                            if( isset( $content->room_name ) && !empty( $content->room_name ) )
                            {
                                $room = $this->_checkRoom( $content->room_name );
                                if( $room )
                                {
                                    $recipients = $room->roomUserObjList;
                                }
                            }else{
                                $recipients = $this->clients;
                            }

                            if( $recipientId )
                            {
                                $this->_findSendMessage( $recipients, $recipientId, $message, $client );
                            }else{
                                if( $broadcast )
                                {
                                    $this->_AllSendMessage( $recipients, $message, $client );
                                }else{
                                    $this->_AllSendMessageWithoutMe( $recipients, $message, $client );
                                }
                            }

                            unset( $recipients );
                        }
                    }
                }
            }
        } else {
            output('error', 'Client (' . $client->resourceId . ') Invalid json.');
            // Closing client connexion with error code "CLOSE_ABNORMAL"
            $client->close(1006);
        }
    }

    /**
     * Event triggered when connection is closed (or user disconnected)
     * @method onClose
     * @param ConnectionInterface $connection
     * @return string
     */
    public function onClose(ConnectionInterface $connection)
    {
        // Output
        if( $this->config->debug )
        {
            output('info', 'Client (' . $connection->resourceId . ') disconnected');
        }

        //check if exist in rooms
        foreach( $this->rooms as &$room )
        {
            $room = $room->leave( null, $connection );
            $this->_updateRoom( $room, url_title( $room->getRoomName() ) );
        }

        if( !empty( $this->callback['close'] ) )
        {
            call_user_func_array($this->callback['close'], array($connection));
        }
        // Detach client from SplObjectStorage
        //$this->clients->detach($connection);
        if( isset( $this->clients[ $connection->resourceId ] ) )
        {
            unset( $this->clients[ $connection->resourceId ] );
        }
    }

    /**
     * Event trigerred when error occured
     * @method onError
     * @param ConnectionInterface $connection
     * @param Exception $e
     * @return string
     */
    public function onError(ConnectionInterface $connection, \Exception $e)
    {
        // Output
        if( $this->config->debug )
        {
            output('fatal', 'An error has occurred: ' . $e->getMessage());
        }

        // We close this connection
        $connection->close();
    }
}