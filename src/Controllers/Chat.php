<?php namespace Daycry\Websocket\Controllers;

use CodeIgniter\Controller;

use Ratchet\ConnectionInterface;

class Chat extends Controller
{
    public function __construct()
    {
        helper( 'websocket' );
    }

    public function start()
    {
        $ws = service( 'websocket' );
        $ws->set_callback('auth', array($this, '_auth'));
        $ws->set_callback('event', array($this, '_event'));
        $ws->set_callback('timer', array($this, '_timer'));
        $ws->set_callback('close', array($this, '_close'));
        $ws->set_callback('roomjoin', array($this, '_roomjoin'));
        $ws->set_callback('roomleave', array($this, '_roomleave'));
        $ws->set_callback('chat', array($this, '_chat'));
        $ws->run();
    }

    public function send()
    {
        $client = new \WebSocket\Client("ws://localhost:8282");
        //$client->text("Hello WebSocket.org!");
        $client->send( json_encode( array('user_id' => 3, 'token' => null, 'type' => 'chat', 'message' => 'Super cool message to myself!' ) ) );
        $client->close();
    }

    public function user(int $user_id = null, string $room = null )
    {
        return view('Websocket/websocket_message', array( 'user_id' => $user_id, 'room' => $room ));
    }

    public function _auth( $content = null )
    {
        // Here you can verify everything you want to perform user login.
        return( !empty( $content->user_id ) ) ? $content->user_id : false;
    }

    public function _event( $content = null )
    {
        // Here you can do everyting you want, each time message is received
        output( 'success', 'Hey ! I\'m an EVENT callback: ' . json_encode( $content ) );
        return null;
    }

    public function _roomjoin( $content, ConnectionInterface $client )
    {
        output( 'success', 'Hey ! I\'m an JOIN callback: ' . json_encode( $content ) );
        return null;
    }

    public function _roomleave( $content, ConnectionInterface $client )
    {
        output( 'success', 'Hey ! I\'m an LEAVE callback: ' . json_encode( $content ) );
        return null;
    }

    public function _timer( $content = null )
    {
        output( 'success', 'Hey ! I\'m an TIMER callback: ' . json_encode( $content ) );
        return null;
    }

    public function _close( $content = null )
    {
        output( 'success', 'Hey ! I\'m an CLOSE callback: ' . json_encode( $content ) );
        return null;
    }

    public function _chat( $content = null ) : ?array
    {
        output( 'success', 'Hey ! I\'m an CHAT callback: ' . json_encode( $content ) );
        $content->message = "Message changed";
        output( 'success', 'Hey ! I\'m an CHAT callback: ' . json_encode( $content ) );

        return $content;
    }
}