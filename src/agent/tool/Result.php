<?php

namespace think\ai\agent\tool;

abstract class Result
{
    protected $usage = 0;
    protected $error = false;

    public function isError()
    {
        return $this->error;
    }

    public function setUsage($usage)
    {
        $this->usage = $usage;
        return $this;
    }

    public function getUsage()
    {
        return $this->usage;
    }

    public function getContent()
    {
        return '';
    }

    abstract public function getResponse();
}
