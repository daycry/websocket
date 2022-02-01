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

        }elseif( count( $this->roomUserObjList ) > $this->roomLimit )
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

            $this->SendMsgToRoomAllUser($this->roomName, $this->roomName, $data->user_id . " has joined.");

            output( 'success', 'count in room : ' . count( $this->roomUserObjList ) );

            return $this;
        }
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
        $this->SendMsgToRoomAllUser($this->roomName, $this->roomName, $client->user_id . " has left.");

        return $this;
        
    }

    public function SendMsgToRoomAllUser($sender, $receiver, $msg)
    {
        foreach( $this->roomUserObjList as $key => $client )
        {
            $client->send(json_encode(array(
                "type" => "room",
                "room_name" => $this->roomName,
                "sender" => $sender,
                "receiver" => $receiver,
                "message" => $msg
            )));

        }
    }

    private function SendMsgRoom($sender, $receiver, $msg)
    {
        if ($this->roomName == $sender)
        {
            foreach ($this->roomUserObjList as $key => $client)
            {
                $response_to = $msg;

                $client->send(json_encode(array(
                    "type" => "room",
                    "room_name" => $this->roomName,
                    "sender" => $sender,
                    "receiver" => $receiver,
                    "message" => $response_to
                )));
            }
        }
    }

    public function GetRoomUserList()
    {
        $data = array();

        /*foreach ($this->roomUserList as $client) {
            if (count($data) == 0) {
                $data[] = $client;
            } else {
                array_push($data, $client);
            }
        }*/
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

        /*$key = array_search($user->user_id, $this->roomUserList);

        if ($key !== false) {
            unset($this->roomUserList[$key]);
        }

        $key = array_search($user, $this->roomUserObjList);
        if ($key !== false) {
            unset($this->roomUserObjList[$key]);
        }*/
    }
}