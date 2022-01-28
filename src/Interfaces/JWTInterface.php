<?php
namespace Daycry\Websocket\Interfaces;

use CodeIgniter\Config\BaseConfig;

interface JWTInterface
{
    public function __construct( BaseConfig $config );
    public function encode( $payload );
    public function decode( $jwt );
}