<?php
namespace Daycry\Websocket\Interfaces;

use CodeIgniter\Config\BaseConfig;
use Ratchet\ConnectionInterface;

interface ServerInterface
{
    public function __construct( BaseConfig $config, array $callback = [] );
    public function onOpen( ConnectionInterface $connection );
    public function onMessage( ConnectionInterface $connection, $message );
    public function onClose( ConnectionInterface $connection );
    public function onError( ConnectionInterface $connection, \Exception $e );
}