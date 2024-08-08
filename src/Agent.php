<?php

namespace think\ai;

use think\ai\agent\tool\FunctionCall;
use think\ai\agent\tool\result\Error;
use think\ai\agent\tool\result\Raw;
use think\helper\Arr;
use Throwable;

abstract class Agent
{
    protected $config = [];

    protected $usage     = 0;
    protected $round     = 0;
    protected $chunks    = [];
    protected $functions = [];
    protected $plugins   = [];

    protected $canUseTool = false;

    protected function addFunction($key, FunctionCall $func, $args = [])
    {
        $this->functions[$key] = [$func, $args];
        return $this;
    }

    /**
     * @param $key
     * @return array{FunctionCall, array}
     */
    protected function getFunction($key)
    {
        if (!isset($this->functions[$key])) {
            return [null, []];
        }
        return $this->functions[$key];
    }

    protected function addPlugin($name, $tool, $args)
    {
        $this->plugins[] = [
            'name' => $name,
            'tool' => $tool,
            'args' => $args,
        ];
        return $this;
    }

    protected function buildTools()
    {
        if (!$this->canUseTool) {
            return null;
        }

        $tools = [];

        foreach ($this->plugins as $plugin) {
            $tools[] = [
                'type'   => 'plugin',
                'plugin' => $plugin,
            ];
        }

        foreach ($this->functions as $name => $function) {
            /** @var FunctionCall $object */
            [$object, $args] = $function;
            $tools[] = $object->toArray($name, $args);
        }

        return $tools;
    }

    protected function getSystemVars()
    {
        return [];
    }

    protected function replaceVars($prompt, $vars = [])
    {
        $vars = [
            ...$this->getSystemVars(),
            ...$vars,
        ];

        foreach ($vars as $key => $value) {
            $prompt = str_replace("{{{$key}}}", $value, $prompt);
        }

        return $prompt;
    }

    abstract protected function buildPromptMessages();

    protected function buildHistoryMessages($messages, $maxTokens = 0)
    {
        $historyMessages = [];

        foreach ($messages as $message) {
            $chunkMessages = [
                [
                    'role'    => 'user',
                    'content' => $message->content,
                ],
            ];

            foreach ($message->chunks as $chunk) {
                if (!empty($chunk['error'])) {
                    break 2;
                }
                if (!empty($chunk['tools'])) {
                    if (!$this->canUseTool) {
                        break 2;
                    }
                    $calls     = [];
                    $responses = [];
                    foreach ($chunk['tools'] as $tool) {
                        $calls[] = [
                            'id'       => $tool['id'],
                            'type'     => 'function',
                            'function' => [
                                'name'      => $tool['name'],
                                'arguments' => $tool['arguments'],
                            ],
                        ];

                        $responses[] = [
                            'tool_call_id' => $tool['id'],
                            'role'         => 'tool',
                            'name'         => $tool['name'],
                            'content'      => $tool['response'],
                        ];
                    }

                    $chunkMessages[] = [
                        'role'       => 'assistant',
                        'content'    => $chunk['content'] ?? null,
                        'tool_calls' => $calls,
                    ];

                    $chunkMessages = array_merge($chunkMessages, $responses);
                } else {
                    $chunkMessages[] = [
                        'role'    => 'assistant',
                        'content' => $chunk['content'],
                    ];
                }
            }

            $tempHistoryMessages = array_merge($chunkMessages, $historyMessages);
            if ($maxTokens > 0) {
                $tokens = Util::tikToken($tempHistoryMessages);
                if ($tokens > $maxTokens * .6) {
                    break;
                }
            }
            $historyMessages = $tempHistoryMessages;
        }
        return $historyMessages;
    }

    abstract protected function init($params);

    public function chat($params = [])
    {
        $this->init($params);
        return $this->run();
    }

    protected function start()
    {
        $messages = $this->buildPromptMessages();
        $tools    = $this->buildTools();
        yield from $this->iteration($messages, $tools);
    }

    protected function run()
    {
        try {
            $start = microtime(true);
            yield from $this->start();
        } finally {
            $latency = round((microtime(true) - $start) * 1000);

            $usage = $this->consumeTokens($this->usage);

            //更新统计
            yield [
                'stats' => [
                    'usage'   => $usage,
                    'latency' => $latency,
                ],
            ];

            if (!empty($this->chunks)) {
                $this->saveMessage($usage, $latency);
            }

            $this->round     = 0;
            $this->usage     = 0;
            $this->chunks    = [];
            $this->functions = [];
            $this->plugins   = [];
        }
    }

    abstract protected function saveMessage($usage, $latency);

    abstract protected function consumeTokens($usage);

    protected function iteration($messages, $tools)
    {
        $chunkIndex = $this->round;
        $this->round++;

        $model       = Arr::get($this->config['model'], 'name');
        $temperature = Arr::get($this->config['model'], 'params.temperature', 0.8);

        $params = [
            'model'       => $model,
            'messages'    => $messages,
            'temperature' => $temperature,
        ];

        if (!empty($tools)) {
            $params['tools'] = $tools;
        }

        $calls = [];

        try {
            $result = $this->getClient()->chat()->completions($params);

            foreach ($result as $event) {
                if (!empty($event['delta']['tool_calls'])) {
                    $call      = $event['delta']['tool_calls'][0];
                    $callIndex = $call['index'] ?? 0;
                    unset($call['index']);

                    if (!isset($calls[$callIndex])) {
                        $calls[$callIndex] = $call;

                        switch ($call['type']) {
                            case 'plugin':
                                $payload = [
                                    'id'        => $call['id'],
                                    'name'      => $call['plugin']['function'],
                                    'title'     => $call['plugin']['title'],
                                    'arguments' => $call['plugin']['arguments'],
                                ];
                                break;
                            case 'function':
                                $name = $call['function']['name'];
                                [$function] = $this->getFunction($name);
                                if ($function) {
                                    $payload = [
                                        'id'        => $call['id'],
                                        'name'      => $name,
                                        'title'     => $function->getTitle(),
                                        'arguments' => $call['plugin']['arguments'],
                                    ];
                                }
                                break;
                        }

                        if (!empty($payload)) {
                            //下发调用工具的状态
                            yield from $this->sendToolData($chunkIndex, $callIndex, $payload);
                        }
                    } else {
                        $calls[$callIndex] = Util::mergeDeep($calls[$callIndex], $call);
                    }
                } else {
                    $content = $event['delta']['content'] ?? '';
                    if ($content !== '') {//这里必须和''强比较，防止0等字符不能输出
                        yield from $this->sendChunkData($chunkIndex, 'content', $content, true);
                    }
                }

                if (!empty($event['usage'])) {
                    $this->usage += $event['usage']['total_tokens'];
                    yield from $this->sendChunkData($chunkIndex, 'content', '', true);
                }
            }
        } catch (Throwable $e) {
            yield from $this->sendChunkData($chunkIndex, 'error', $e->getMessage());
        }

        if (!empty($calls)) {
            $messages[] = [
                'role'       => 'assistant',
                'tool_calls' => $calls,
            ];

            foreach ($calls as $index => $call) {
                $id   = $call['id'];
                $type = $call['type'];

                switch ($type) {
                    case 'plugin':
                        $content = $call['plugin']['content'];
                        if (!empty($content) && is_array($content)) {
                            switch ($content['type']) {
                                case 'image':
                                    //图片本地化
                                    $content['image'] = $this->saveImage($content['image']);
                                    break;
                            }
                        }

                        $result = new Raw([
                            'response' => $call['plugin']['response'],
                            'content'  => $content,
                            'error'    => $call['plugin']['error'],
                            'usage'    => $call['plugin']['usage'],
                        ]);
                        break;
                    case 'function':
                        try {
                            $name = $call['function']['name'];
                            [$function, $args] = $this->getFunction($name);

                            if (empty($function)) {
                                throw new Exception("tool [{$name}] not exist");
                            }

                            $arguments = json_decode($call['function']['arguments'], true);

                            if (!is_array($arguments)) {
                                $arguments = [];
                            }

                            $result = $function(array_merge($arguments, $args));
                        } catch (Throwable $e) {
                            $result = new Error($e);
                        }

                        $messages[] = [
                            'tool_call_id' => $id,
                            'role'         => 'tool',
                            'name'         => $name,
                            'content'      => $result->getResponse(),
                        ];
                        break;
                }

                if (!empty($result)) {
                    //调用工具产生的计费
                    $this->usage += $result->getUsage();

                    //下发调用工具完成的状态
                    yield from $this->sendToolData($chunkIndex, $index, [
                        'response' => $result->getResponse(),
                        'error'    => $result->isError(),
                        'content'  => $result->getContent(),
                    ]);
                }
            }

            yield from $this->iteration($messages, $tools);
        }
    }

    abstract protected function getClient(): Client;

    protected function saveImage($image)
    {
        return $image;
    }

    protected function sendToolData($chunkIndex, $toolIndex, $data)
    {
        $this->updateChunk($chunkIndex, "tools.{$toolIndex}", $data);

        yield [
            'chunks' => [
                'index' => $chunkIndex,
                'tools' => [
                    'index' => $toolIndex,
                    ...$data,
                ],
            ],
        ];
    }

    protected function sendChunkData($chunkIndex, $key, $value, $append = false)
    {
        $this->updateChunk($chunkIndex, $key, $value, $append);

        yield [
            'chunks' => [
                'index' => $chunkIndex,
                $key    => $value,
            ],
        ];
    }

    protected function updateChunk($chunkIndex, $key, $value, $append = false)
    {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                Arr::set($this->chunks, "{$chunkIndex}.{$key}.{$k}", $v);
            }
        } else {
            if ($append) {
                $value = Arr::get($this->chunks, "{$chunkIndex}.{$key}", '') . $value;
            }
            Arr::set($this->chunks, "{$chunkIndex}.{$key}", $value);
        }
    }
}
