<?php

namespace think\ai\api;

use Psr\Http\Message\StreamInterface;
use think\ai\Api;
use think\ai\StreamIterator;

class Chat extends Api
{
    public function completions($params)
    {
        $stream = $params['stream'] ?? true;

        if (!isset($params['moderation'])) {
            $params['moderation'] = true;
        }

        $res = $this->request('POST', 'chat/completions', [
            'json'   => $params,
            'stream' => $stream,
        ]);

        if ($res instanceof StreamInterface) {
            return new StreamIterator($res);
        } else {
            return $res;
        }
    }
}
