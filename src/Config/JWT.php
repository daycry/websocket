<?php namespace Daycry\Websocket\Config;

use CodeIgniter\Config\BaseConfig;

class JWT extends BaseConfig
{
    public $key = 'GGFSRTSYTSOPLGCCXS';
    public $algo = 'HS256';
    public $verify = true;
    public $expiresAt = 1;
    public $validateTimestamp = false;
}