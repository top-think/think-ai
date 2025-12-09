<?php

namespace think\ai\api;

use think\ai\Api;

class Avatar extends Api
{
    public function video($params)
    {
        return $this->request('POST', 'avatar/video', [
            'json' => $params,
        ]);
    }

    public function query($params)
    {
        return $this->request('POST', 'avatar/query', [
            'json' => $params,
        ]);
    }
}
