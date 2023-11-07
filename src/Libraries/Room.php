<?php namespace Daycry\Websocket\Libraries;

use Ratchet\ConnectionInterface;

class Room
{
    public string $roomName;

    public array $roomUserObjList = [];

    private $roomLimit = 5;

    private array $kickedUser = array();

    public function __construct()
    {
        $this->roomUserObjList = array();
    }

    public function getRoomName()
    {
        return $this->roomName;
    }

    public function setRoomName($roomName)
    {
        $this->roomName = $roomName;

        return $this;
    }

    public function getRoomLimit()
    {
        return $this->roomLimit;
    }

    public function setRoomLimit($roomLimit)
    {
        $this->roomLimit = $roomLimit;

        return $this;
    }

    public function join($data, ConnectionInterface $client)
    {
        if( in_array( $data->user_id, $this->kickedUser ) )
        {
            $msg = array(
                "type" => "error",
                "message" => 'Recently you have been kicked from this room. Please Try again later.'
            );

            $client->send(json_encode($msg));

        }elseif( count( $this->roomUserObjList ) >= $this->roomLimit )
        {
            $msg = array(
                "type" => "error",
                "message" => 'Room is Full.'
            );

            $client->send(json_encode($msg));

        }elseif( isset( $this->roomUserObjList[ $client->resourceId ] ) )
        {
            $msg = array(
                "status" => true,
                "type" => "room",
                "room_name" => $data->room_name,
                "sender" => $data->room_name,
                "receiver" => $data->room_name,
                "message" => $this->GetRoomUserList()
            );

            $client->send(json_encode($msg));

        }else{
            //array_push($this->roomUserList, $client->resourceId );
            $this->roomUserObjList[ $client->resourceId ] = $client;

            $msg = array(
                "status" => true,
                "type" => "room",
                "room_name" => $data->room_name,
                "sender" => $data->room_name,
                "receiver" => $data->room_name,
                "message" => $this->GetRoomUserList()
            );

            $client->send(json_encode($msg));

            $u = ( isset( $data->user_id ) && $data->user_id ) ? $data->user_id : $client->resourceId;
            $this->SendMsgToRoomAllUser($this->roomName, $this->roomName, "(" . $u . ") has joined.", $client);

            output( 'success', 'count in room : ' . count( $this->roomUserObjList ) );
        }

        return $this;
    }

    public function leave( $data = null, ConnectionInterface $client )
    {
        if( $data == null )
        {
            $data = $this->roomUserObjList[ $client->resourceId ];
        }
        output( 'success', 'data resource : ' . json_encode( $client->resourceId ) );
        output( 'success', 'data leave : ' . json_encode( $client->user_id ) );

        $this->RemoveFromList( $client->resourceId );

        //$this->SendMsgRoom($this->roomName, $this->roomName, $data->user_id . " has left.");
        $u = ( isset( $data->user_id ) && $data->user_id ) ? $data->user_id : $client->resourceId;
        $this->SendMsgToRoomAllUser($this->roomName, $this->roomName, "(" . $u . ") has left.", $client);

        return $this;
        
    }

    public function SendMsgToRoomAllUser($sender, $receiver, $msg, $client = null)
    {
        foreach( $this->roomUserObjList as $key => $user )
        {
            if( !$client || ( isset($client) && $client->resourceId != $key ) )
            {
                $user->send(json_encode(array(
                    "type" => "room",
                    "room_name" => $this->roomName,
                    "sender" => $sender,
                    "receiver" => $receiver,
                    "message" => $msg
                )));
            }

        }
    }

    public function GetRoomUserList()
    {
        $data = array();

        foreach( $this->roomUserObjList as $key => $client )
        {
            if( isset( $client->user_id ) )
            {
                array_push( $data, $client->user_id );
            }
        }

        return $data;
    }

    private function RemoveFromList($user)
    {
        if( isset( $this->roomUserObjList[ $user ] ) )
        {
            unset( $this->roomUserObjList[ $user ] );
        }
    }
}