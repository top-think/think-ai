<?php

namespace think\ai\api;

use think\ai\Api;

class Embeddings extends Api
{
    public function create($params)
    {
        return $this->request('POST', 'embeddings', [
            'json' => $params,
        ]);
    }
}
