<?php

namespace think\ai\api;

use think\ai\Api;

class Videos extends Api
{
    public function generations($params)
    {
        return $this->request('POST', 'videos/generations', [
            'json' => $params,
        ]);
    }

    public function query($params)
    {
        return $this->request('POST', 'videos/query', [
            'json' => $params,
        ]);
    }
}
