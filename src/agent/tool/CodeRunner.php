<?php

namespace think\ai\agent\tool;

use think\ai\agent\tool\result\Raw;
use think\ai\Client;

class CodeRunner extends Func
{
    protected $title       = '代码执行器';
    protected $name        = 'runner';
    protected $description = '这个插件将会创建一个沙箱来运行python代码并获取结果，尤其处理数学、计算机、图片和文件等。首先，LLM将分析问题，并用python输出解决这个问题的步骤。其次，LLM立即生成代码，按照步骤解决问题。LLM会参考错误消息调整代码，直到成功。生成代码时如果需要根据上文的返回值的话，请分段生成，无需重复之前的代码';
    protected $parameters  = [
        'code'  => [
            'type'        => 'string',
            'description' => '执行的代码，用户通过files参数提供的文件保存在/home/user目录下。',
            'required'    => true,
        ],
        'files' => [
            'type'        => 'array',
            'items'       => [
                'type'       => 'object',
                'properties' => [
                    'name' => [
                        'type'        => 'string',
                        'description' => '保存到/home/user目录下时使用的文件名',
                    ],
                    'url'  => [
                        'type'        => 'string',
                        'description' => '文件地址，请使用远程文件地址，以http开头',
                    ],
                ],
            ],
            'description' => '如果用户有提供文件，请使用该参数传递',
        ],
    ];

    protected $id;

    public function __construct(protected Client $client)
    {
    }

    public function run(Args $args)
    {
        $usage = 0;
        if (!$this->id) {
            $sandbox = $this->client->sandbox()->create();

            $this->id = $sandbox['id'];
            $usage    = $sandbox['usage'] ?? 0;
        }

        $code  = $args->get('code');
        $files = $args->get('files', []);

        $result = $this->client->sandbox()->execute($this->id, $code, $files);

        return (new Raw($result))->setUsage($usage);
    }

    public function __destruct()
    {
        if ($this->id) {
            $this->client->sandbox()->delete($this->id);
        }
    }
}
