<?php

namespace think\ai\agent;

use JsonSerializable;
use think\helper\Arr;
use think\helper\Str;

abstract class Plugin implements JsonSerializable
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
     * @param $name
     * @return Tool|null
     */
    public function getTool($name)
    {
        $tools = $this->getTools();

        $tool = Arr::first($tools, function ($tool) use ($name) {
            return $tool->getName() == $name;
        });

        if ($tool) {
            return $tool;
        }
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
