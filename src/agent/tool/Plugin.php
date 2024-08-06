<?php

namespace think\ai\agent\tool;

use think\ai\agent\Tool;

class Plugin extends Tool
{
    protected $type = 'plugin';

    public function __construct(protected $plugin, protected $tool)
    {
    }

    public function toArray($name, $args = [])
    {
        return [
            'type'   => $this->getType(),
            'plugin' => [
                'name' => $this->plugin,
                'tool' => $this->tool,
                'args' => $args,
            ],
        ];
    }
}
