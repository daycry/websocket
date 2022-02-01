<?php namespace Daycry\Websocket\Libraries;

use Ratchet\ConnectionInterface;

class Room
{
    public $roomName;

    public $roomUserList;

    public array $roomUserObjList = [];

    private $roomlimit = 40;

    private array $kickedUser = array();

    public function __construct()
    {
        $this->roomUserList = array();
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
        return $this->roomlimit;
    }

    public function setRoomLimit($roomlimit)
    {
        $this->roomlimit = $roomlimit;

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

        }elseif( count( $this->roomUserList ) > $this->roomlimit )
        {
            $msg = array(
                "type" => "error",
                "message" => 'Room is Full.'
            );

            $client->send(json_encode($msg));

        }elseif( in_array( $data->user_id, $this->roomUserList ) )
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
            array_push($this->roomUserList, $data->user_id);
            array_push($this->roomUserObjList, $client);

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

            return $this;
        }

    }

    public function leave( $data, ConnectionInterface $client )
    {
        $this->RemoveFromList($client);

        $this->SendMsgRoom($this->roomName, $this->roomName, $data->user_id . " has left.");
    }

    public function SendMsgToRoomAllUser($sender, $receiver, $msg)
    {
        foreach ($this->roomUserObjList as $client) {

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
            foreach ($this->roomUserObjList as $client)
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

        foreach ($this->roomUserList as $client) {
            if (count($data) == 0) {
                $data[] = $client;
            } else {
                array_push($data, $client);
            }
        }
        return $data;
    }

    private function RemoveFromList($user)
    {
        $key = array_search($user->username, $this->roomUserList);

        if ($key !== false) {
            unset($this->roomUserList[$key]);
        }

        $key = array_search($user, $this->roomUserObjList);
        if ($key !== false) {
            unset($this->roomUserObjList[$key]);
        }
    }
}