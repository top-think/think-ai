<?php

namespace think\ai\agent\tool\result;

use think\ai\agent\tool\Result;
use think\helper\Arr;

class Raw extends Result
{
    protected $response;
    protected $content;

    public function __construct($result)
    {
        $this->content  = Arr::get($result, 'content', '');
        $this->response = Arr::get($result, 'response');
        $this->error    = Arr::get($result, 'error', false);
        $this->usage    = Arr::get($result, 'usage', 0);
    }

    public function getContent()
    {
        return $this->content;
    }

    public function getResponse()
    {
        return $this->response;
    }
}
