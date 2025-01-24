<?php

namespace think\ai\api;

use think\ai\Api;

class Audio extends Api
{
    public function speech($params)
    {
        return $this->request('POST', 'audio/speech', [
            'json' => $params,
        ]);
    }

    public function transcriptions($params)
    {
        return $this->request('POST', 'audio/transcriptions', [
            'json' => $params,
        ]);
    }

}
