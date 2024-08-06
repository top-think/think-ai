<?php

namespace think\ai\api;

use think\ai\Api;

class Plugin extends Api
{
    public function list($params = [])
    {
        return $this->request('GET', 'plugin', [
            'query' => $params,
        ]);
    }
}
