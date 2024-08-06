<?php

namespace think\ai\agent\tool\result;

use think\ai\agent\tool\Result;

class Plain extends Result
{
    public function __construct(protected $data)
    {
    }

    public function getResponse()
    {
        if (is_array($this->data)) {
            return json_encode($this->data, JSON_UNESCAPED_UNICODE);
        }
        return (string) $this->data;
    }
}
