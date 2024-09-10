<?php

namespace think\ai\api;

use SplFileInfo;
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
        $multipart = [];

        foreach ($params as $key => $value) {
            if ($value instanceof SplFileInfo) {
                $filename    = method_exists($value, 'getOriginalName') ? $value->getOriginalName() : $value->getBasename();
                $multipart[] = [
                    'name'     => $key,
                    'contents' => $value->openFile('r'),
                    'filename' => $filename,
                ];
            } else {
                $multipart[] = [
                    'name'     => $key,
                    'contents' => $value,
                ];
            }
        }

        return $this->request('POST', 'audio/transcriptions', [
            'multipart' => $multipart,
        ]);
    }

}
