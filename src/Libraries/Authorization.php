<?php namespace Daycry\Websocket\Libraries;

use Daycry\Websocket\Libraries\JWT;

/**
 * @package   CodeIgniter WebSocket Library: Validate JWT Token
 * @category  Libraries
 * @author    Taki Elias <taki.elias@gmail.com>
 * @license   http://opensource.org/licenses/MIT > MIT License
 * @link      https://github.com/takielias
 *
 * CodeIgniter WebSocket library. It allows you to make powerful realtime applications by using Ratchet Websocket
 */

class Authorization
{
    public static function validateTimestamp($token)
    {
        $token = self::validateToken($token);
        if ($token != false && (now() - $token->timestamp < (\Daycry\Websocket\Config\Services::websocket()->config->token_timeout * 60))) {
            return $token;
        }
        return false;
    }

    public static function validateToken($token)
    {
        return JWT::decode($token, \Daycry\Websocket\Config\Services::websocket()->config->jwt_key);
    }

    public static function generateToken($data)
    {
        return JWT::encode($data, \Daycry\Websocket\Config\Services::websocket()->config->jwt_key);
    }
}