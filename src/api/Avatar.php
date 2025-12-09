<?php

namespace think\ai\api;

use think\ai\Api;
use think\ai\Exception;
use think\helper\Arr;

class Avatar extends Api
{
    public function video($params, $wait = false)
    {
        $result = $this->request('POST', 'avatar/video', [
            'json' => $params,
        ]);

        if (!$wait) {
            return $result;
        }

        $id = $result['id'];

        if (!is_int($wait)) {
            $wait = 300;
        }

        $start = time();
        while (true) {
            try {
                $result = $this->query(['id' => $id]);
                if (Arr::get($result, 'status') != 'processing') {
                    return $result;
                }
            } catch (\Throwable) {

            }
            if (time() - $start > $wait) {
                throw new Exception('Timeout');
            }
            sleep(5);
        }

    }

    public function query($params)
    {
        return $this->request('POST', 'avatar/query', [
            'json' => $params,
        ]);
    }
}
