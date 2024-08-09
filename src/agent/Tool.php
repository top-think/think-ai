<?php

namespace think\ai\agent;

use think\helper\Str;

abstract class Tool
{
    protected $name        = null;
    protected $title       = null;
    protected $description = null;
    protected $parameters  = null;

    public function getName()
    {
        if ($this->name) {
            return $this->name;
        }
        return Str::snake(class_basename(static::class));
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'name'        => $this->getName(),
            'title'       => $this->getTitle(),
            'description' => $this->getDescription(),
            'parameters'  => $this->getParameters(),
        ];
    }
}
