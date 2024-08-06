<?php

namespace think\ai\agent\tool;

abstract class Result
{
    protected $usage = 0;

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
