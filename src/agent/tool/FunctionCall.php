<?php

namespace think\ai\agent\tool;

use think\ai\agent\Tool;
use think\ai\agent\tool\result\Plain;
use think\helper\Arr;

abstract class FunctionCall extends Tool
{
    protected $extra = null;

    /**
     * @param $args
     * @return Result
     */
    public function __invoke($args)
    {
        $res = $this->run(new Args($args));

        if (!$res instanceof Result) {
            $res = new Plain($res);
        }
        return $res;
    }

    public function prepare()
    {

    }

    abstract protected function run(Args $args);

    public function getExtra()
    {
        return $this->extra;
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
            'type'     => 'function',
            'function' => [
                'name'        => $name,
                'description' => $this->getLlmDescription(),
                'parameters'  => $this->getLlmParameters(),
            ],
        ];
    }
}
