<?php

namespace think\ai\api;

use think\ai\Api;

class Rerank extends Api
{
    public function create($params)
    {
        return $this->request('POST', 'rerank', [
            'json' => $params,
        ]);
    }
}
