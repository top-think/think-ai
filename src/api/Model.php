<?php

namespace think\ai\api;

use think\ai\Api;

class Model extends Api
{
    public function list($params = [])
    {
        return $this->request('GET', 'model', [
            'query' => $params,
        ]);
    }
}
