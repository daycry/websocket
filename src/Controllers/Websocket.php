<?php namespace Daycry\Websocket\Controllers;

use CodeIgniter\Controller;

class Websocket extends Controller
{
    /**
     * @var Config
     */
    protected $config;

    public function __construct()
    {
        $this->config = config('Websocket');
    }

    public function start()
    {
        $ws = service( 'websocket' );
        $ws->set_callback('auth', array($this, '_auth'));
        $ws->set_callback('event', array($this, '_event'));
        $ws->run();
    }

    public function send()
    {
        $client = new \WebSocket\Client("ws://localhost:8282");
        //$client->text("Hello WebSocket.org!");
        $client->send( json_encode( array('user_id' => 3, 'token' => null, 'type' => 'chat', 'message' => 'Super cool message to myself!' ) ) );
        $client->close();
    }

    public function user($user_id = null)
    {
        return view('Websocket/websocket_message', array('user_id' => $user_id));
    }

    public function _auth($datas = null)
    {
        // Here you can verify everything you want to perform user login.
        return (!empty($datas->user_id)) ? $datas->user_id : false;
    }

    public function _event($datas = null)
    {
        // Here you can do everyting you want, each time message is received
        log_message('error', 'Hey ! I\'m an EVENT callback: ' . json_encode( $datas ) );
    }

    public function _timer($datas = null)
    {
        log_message('error', 'Timer event: ' . $datas );
    }
}