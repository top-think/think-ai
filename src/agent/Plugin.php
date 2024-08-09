<?php

namespace think\ai\agent;

use think\helper\Str;

abstract class Plugin
{
    protected $name        = null;
    protected $title       = '';
    protected $description = '';
    protected $icon        = null;

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

    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @return Tool[]
     */
    abstract public function getTools();

    public function jsonSerialize(): mixed
    {
        return [
            'name'        => $this->getName(),
            'title'       => $this->getTitle(),
            'description' => $this->getDescription(),
            'icon'        => $this->getIcon(),
            'tools'       => $this->getTools(),
        ];
    }
}
