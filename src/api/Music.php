<?php

namespace think\ai\api;

use think\ai\Api;

class Music extends Api
{
    public function song($params)
    {
        return $this->request('POST', 'music/song', [
            'json' => $params,
        ]);
    }

    public function bgm($params)
    {
        return $this->request('POST', 'music/bgm', [
            'json' => $params,
        ]);
    }

    public function query($params)
    {
        return $this->request('POST', 'music/query', [
            'json' => $params,
        ]);
    }
}
