<?php

namespace think\ai\api;

use think\ai\Api;

class Images extends Api
{
    public function generations($params)
    {
        return $this->request('POST', 'images/generations', [
            'json' => $params,
        ]);
    }

    public function inpainting($params)
    {
        return $this->request('POST', 'images/inpainting', [
            'json' => $params,
        ]);
    }

    public function outpainting($params)
    {
        return $this->request('POST', 'images/outpainting', [
            'json' => $params,
        ]);
    }
}
