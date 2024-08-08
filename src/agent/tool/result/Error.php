<?php

namespace think\ai\agent\tool\result;

use think\ai\agent\tool\Result;
use Throwable;

class Error extends Result
{
    public function __construct(protected Throwable $exception)
    {
        $this->error = true;
    }

    public function getResponse()
    {
        return 'error: ' . $this->exception->getMessage();
    }
}
