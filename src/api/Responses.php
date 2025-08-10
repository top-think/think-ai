<?php

namespace think\ai\api;

use Psr\Http\Message\StreamInterface;
use think\ai\Api;
use think\ai\StreamIterator;

class Responses extends Api
{
    public function create($params)
    {
        $res = $this->request('POST', 'responses', [
            'json'   => $params,
            'stream' => true,
        ]);

        if ($res instanceof StreamInterface) {
            return new StreamIterator($res);
        } else {
            return $res;
        }
    }
}
