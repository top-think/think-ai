<?php

namespace think\ai\api;

use Psr\Http\Message\StreamInterface;
use think\ai\Api;
use think\ai\StreamIterator;
use think\ai\Response\ChatResponse;
use think\ai\Response\StreamResponse;
use think\ai\Response\EnhancedStreamResponse;

class Chat extends Api
{
    public function completions($params, bool $returnRaw = false, bool $enhanced = true)
    {
        $stream = $params['stream'] ?? true;

        if (!isset($params['moderation'])) {
            $params['moderation'] = true;
        }

        $res = $this->request('POST', 'chat/completions', [
            'json'   => $params,
            'stream' => $stream,
        ]);

        if ($returnRaw) {
            if ($res instanceof StreamInterface) {
                return new StreamIterator($res);
            } else {
                return $res;
            }
        }

        // 返回包装的响应对象
        if ($res instanceof StreamInterface) {
            $iterator = new StreamIterator($res);
            return $enhanced ? new EnhancedStreamResponse($iterator) : new StreamResponse($iterator);
        } else {
            return new ChatResponse($res);
        }
    }
}
