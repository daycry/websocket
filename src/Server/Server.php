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
        $this->clients->attach($connection);

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
                    if( empty( $auth ) || !is_integer( $auth ) )
                    {
                        output('error', 'Client (' . $client->resourceId . ') authentication failure');
                        $client->send(json_encode(array("type" => "error", "msg" => 'Invalid ID or Password.')));
                        // Closing client connexion with error code "CLOSE_ABNORMAL"
                        $client->close(1006);
                    }

                    // Add UID to associative array of subscribers
                    $client->subscriber_id = $auth;

                    if( $this->config->auth )
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
                        if( $validJwt = valid_jwt( $content->token ) == false )
                        {
                            $client->send( json_encode( array("type" => "error", "message" => 'Invalid Token.' ) ) );
                        }
                    }

                    if( $validJwt )
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
                                        $this->_updateRoom( $room, url_title( $room->getRoomName() ) );
                                    }else{


                                        $room = New Room();
                                        $room->setRoomName( $content->room_name );
                                        $this->rooms[ url_title( $content->room_name ) ] = $room;
                                        //array_push( $this->rooms, $room );
                                        $room = $room->join($content, $client);
                                        $this->_updateRoom( $room, url_title( $room->getRoomName() ) );
                                    }
                                }else{
                                    //leave room
                                    if( $room != false )
                                    {
                                        $room->leave( $content, $client );
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
                            $user = false;
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
                                }else{
                                    $this->_AllSendMessage( $recipients, $message, $client );
                                }
                            }

                            unset( $recipients );
                        }
                    }
                }
            }




























            /*if(!empty($datas->type) && $datas->type == 'socket')
            {
                if( !empty( $datas->user_id ) && $datas->user_id !== $client->subscriber_id )
                {
                    if( !empty( $this->callback['auth'] ) && empty($client->subscriber_id ) )
                    {
                        // Call user personal callback
                        $auth = call_user_func_array( $this->callback[ 'auth' ], array( $datas ) );

                        // Verify authentication
                        if( empty( $auth ) || !is_integer( $auth ) )
                        {
                            output('error', 'Client (' . $client->resourceId . ') authentication failure');
                            $client->send(json_encode(array("type" => "error", "msg" => 'Invalid ID or Password.')));
                            // Closing client connexion with error code "CLOSE_ABNORMAL"
                            $client->close(1006);
                        }

                        // Add UID to associative array of subscribers
                        $client->subscriber_id = $auth;

                        if( $this->config->auth )
                        {
                            $data = json_encode(array("type" => "token", "token" => Authorization::generateToken($client->resourceId)));
                            $this->send_message($client, $data, $client);
                        }

                        // Output
                        if ($this->config->debug) {
                            output('success', 'Client (' . $client->resourceId . ') authentication success');
                            output('success', 'Token : ' . Authorization::generateToken($client->resourceId));
                        }
                    }
                }
            }elseif(!empty($datas->type) && $datas->type == 'roomjoin')
            {
                if( $this->config->auth )
                {
                    if( valid_jwt( $datas->token ) != false )
                    {
                        $room = $this->_checkRoom( $datas->room_name );

                        if( $room != false )
                        {
                            $room->join($datas, $client);
                            $this->_updateRoom( $room );
                        }else{
                            $room = New Room();
                            $room->setRoomName($datas->room_name);
                            array_push( $this->rooms, $room );
                            $room->join($datas, $client);
                            $this->_updateRoom( $room );
                        }

                        if( !empty($this->callback[ 'roomjoin' ] ) )
                        {
                            // Call user personal callback
                            call_user_func_array($this->callback['roomjoin'], array($datas, $client));
                        }

                    } else {
                        $client->send(json_encode(array("type" => "error", "msg" => 'Invalid Token.')));
                    }
                }
            }elseif(!empty($datas->type) && $datas->type == 'roomleave')
            {
                if( $this->config->auth )
                {
                    if( valid_jwt( $datas->token ) != false )
                    {
                        $room = $this->_checkRoom( $datas->room_name );

                        if( $room != false )
                        {
                            $room->leave($datas, $client);
                            $this->_updateRoom( $room );
                        }

                        if( !empty($this->callback[ 'roomleave' ] ) )
                        {
                            // Call user personal callback
                            call_user_func_array($this->callback['roomleave'], array($datas, $client));
                        }

                    } else {
                        $client->send(json_encode(array("type" => "error", "msg" => 'Invalid Token.')));
                    }
                }
            }else{
                // Now this is the management of messages destinations, at this moment, 4 possibilities :
                // 1 - Message is not an array OR message has no destination (broadcast to everybody except us)
                // 2 - Message is an array and have destination (broadcast to single user)
                // 3 - Message is an array and don't have specified destination (broadcast to everybody except us)
                // 4 - Message is an array and we wan't to broadcast to ourselves too (broadcast to everybody)
                if( !empty( $datas->type ) )
                {
                    $pass = true;
                    if( $this->config->auth )
                    {
                        if( valid_jwt( $datas->token ) != false )
                        {
                            if( !empty( $this->callback[ $datas->type ] ) )
                            {
                                // Call user personal callback
                                call_user_func_array( $this->callback[ $datas->type ], array( $datas, $client ) );
                            }
                        } else {
                            output( 'error', 'Client (' . $client->resourceId . ') authentication failure. Invalid Token' );
                            $client->send( json_encode( array( "type" => "error", "msg" => 'Invalid Token.' ) ) );
                            // Closing client connexion with error code "CLOSE_ABNORMAL"
                            $client->close(1006);
                            $pass = false;
                        }
                    }

                    if( $pass )
                    {
                        if( $notify )
                        {
                            if( isset( $datas->room_name ) && !empty( $datas->room_name ) )
                            {
                                $room = $this->_checkRoom( $datas->room_name );
                                if( $room )
                                {
                                    foreach( $room->roomUserObjList as $user )
                                    {
                                        if( !empty( $datas->recipient_id ) )
                                        {
                                            if( $user->subscriber_id == $datas->recipient_id )
                                            {
                                                $this->send_message($user, $message, $client);
                                                break;
                                            }
                                        } else {
                                            // Broadcast to everybody
                                            if( $broadcast )
                                            {
                                                $this->send_message($user, $message, $client);
                                            } else {
                                                // Broadcast to everybody except us
                                                if( $client !== $user )
                                                {
                                                    $this->send_message($user, $message, $client);
                                                }
                                            }
                                        }
                                    }
                                }
                            }else{
                                // We look arround all clients
                                foreach( $this->clients as $user )
                                {
                                    // Broadcast to single user
                                    if( !empty( $datas->recipient_id ) )
                                    {
                                        if( $user->subscriber_id == $datas->recipient_id )
                                        {
                                            $this->send_message($user, $message, $client);
                                            break;
                                        }
                                    } else {
                                        // Broadcast to everybody
                                        if( $broadcast )
                                        {
                                            $this->send_message($user, $message, $client);
                                        } else {
                                            // Broadcast to everybody except us
                                            if( $client !== $user )
                                            {
                                                $this->send_message($user, $message, $client);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }*/
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

        if( !empty( $this->callback['close'] ) )
        {
            call_user_func_array($this->callback['close'], array($connection));
        }
        // Detach client from SplObjectStorage
        $this->clients->detach($connection);
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