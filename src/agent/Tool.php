<?php

namespace think\ai\agent;

use JsonSerializable;
use think\ai\agent\tool\Args;
use think\ai\agent\tool\Result;
use think\ai\agent\tool\result\Raw;
use think\helper\Arr;
use think\helper\Str;

/**
 * @method run($args)
 */
abstract class Tool implements JsonSerializable
{
    protected $type        = 'function';
    protected $name        = null;
    protected $title       = null;
    protected $description = null;
    protected $extra       = null;
    protected $parameters  = null;

    /**
     * @param $args
     * @return Result
     */
    public function __invoke($args)
    {
        $res = $this->run(new Args($args));

        if (!$res instanceof Result) {
            $res = new Raw($res);
        }
        return $res;
    }

    public function getType()
    {
        return $this->type;
    }

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

    public function getExtra()
    {
        return $this->extra;
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    public function getLlmDescription()
    {
        $extra       = $this->getExtra();
        $description = $this->getTitle() . PHP_EOL . $this->getDescription();

        if (!empty($extra)) {
            $description .= PHP_EOL . $extra;
        }

        return $description;
    }

    public function getLlmParameters()
    {
        $properties = [];
        $required   = [];

        $parameters = $this->getParameters();
        if (!empty($parameters)) {
            foreach ($parameters as $name => $parameter) {
                if (($parameter['provider'] ?? 'llm') != 'llm') {
                    continue;
                }

                if ($parameter['required'] ?? false) {
                    $required[] = $name;
                }

                $properties[$name] = Arr::only($parameter, ['type', 'description', 'enum', 'default', 'items']);
            }
        }

        if (empty($properties)) {
            return null;
        }

        return [
            'type'       => 'object',
            'properties' => $properties,
            'required'   => $required,
        ];
    }

    public function toArray($name, $args = [])
    {
        return [
            'type'     => $this->getType(),
            'function' => [
                'name'        => $name,
                'description' => $this->getLlmDescription(),
                'parameters'  => $this->getLlmParameters(),
            ],
        ];
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
