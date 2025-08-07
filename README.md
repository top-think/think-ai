## 💎ThinkAI —— 聚合AI接口服务

[ThinkAI](https://doc.topthink.com/think-ai/default.html)致力于为企业和开发者提供便捷高效的大模型对话、图像、语音、视频、向量等AI接口聚合服务。通过ThinkAI，用户可以一站式轻松地调用各类LLM模型和AI能力，将其整合到自己的产品和服务中，实现更加智能化的功能，助力企业数智化转型。

## 支持模型
目前顶想ThinkAI已经支持了包括GPT、DeepSeek、智谱、火山智能、星火、通义千问、腾讯混元、字节豆包、MiniMax、月之暗面在内的多个模型，未来还将不断添加更多类型的模型支持。开发者可以加入官方的[推广奖励计划](https://doc.topthink.com/public/cooperation.html)（最高返利15%）。

ThinkAI更注重提供底层的AI接口服务，这意味着用户可以更加灵活地自主研发AI应用服务。如果没有研发能力或者希望快速搭建应用，可以使用官方的ThinkBot启智AI Agent服务，为企业提供了更加便捷的开箱即用的解决方案（[体验启智](https://bot.topthink.com) ）。个人用户可以直接使用我们的AI助理服务ThinkChat（支持聊天、画画、视频、搜索、阅读和智能体市场，[立刻体验](https://chat.topthink.com)）。

## 安装
```
composer require topthink/think-ai
```

生成[Token令牌](https://console.topthink.com/user/token)，并注意需要勾选启智AI。

## 用法

### 快速开始

```php
use think\ai\Client;

// 从环境变量自动读取 token
$client = Client::create();

// 或手动提供 token
$client = new Client('YOUR_TOKEN');

// 最简单的聊天
$response = $client->ask('你好，请介绍一下自己');
echo $response;

// 最简单的图片生成
$imageUrl = $client->image('一只可爱的猫咪');
echo $imageUrl;
```

### 聊天功能

#### 1. 使用构建器模式（推荐）

```php
use think\ai\Client;

$client = Client::create();

// 方法一：直接使用构建器
$response = $client->chatBuilder()
    ->model('gpt-4')
    ->system('你是一个专业的程序员')
    ->user('如何实现快速排序？')
    ->temperature(0.7)
    ->maxTokens(1000)
    ->send();

// 方法二：使用回调函数（更优雅）
$response = $client->chat(function($chat) {
    $chat->model('gpt-4')
        ->system('你是一个专业的程序员')
        ->user('如何实现快速排序？')
        ->temperature(0.7)
        ->maxTokens(1000);
})->send();

// 获取回复内容
echo $response->getContent();

// 获取使用的 token 数
echo "使用的 token: " . $response->getTotalTokens();
```

#### 2. 流式聊天

```php
// 基础流式聊天
$stream = $client->chatBuilder()
    ->model('gpt-3.5-turbo')
    ->user('写一个关于春天的诗')
    ->stream(true)
    ->send();

// 方法1：简单处理
$stream->onChunk(function($chunk) {
    echo $chunk->getContent();
});

// 方法2：使用增强的事件处理
$stream->onStart(function() {
    echo "开始生成...\n";
})
->onContent(function($content) {
    echo $content;
})
->onEnd(function($fullContent) {
    echo "\n生成完成！总字数：" . mb_strlen($fullContent);
})
->onError(function($error) {
    echo "发生错误：" . $error->getMessage();
})
->process();

// 方法3：流式输出到浏览器（SSE）
$stream->streamToBrowser();

// 方法4：流式写入文件
$stream->streamToFile('output.txt');

// 方法6：带进度跟踪
$stream->withProgress(function($chunkCount, $chunk) {
    echo "已接收 {$chunkCount} 个数据块\r";
})
->onContent(function($content) {
    // 处理内容
})
->process();
```

#### 3. 高级流式处理

```php
// 使用回调函数的优雅语法
$client->chat(function($chat) {
    $chat->model('gpt-4')
        ->user('写一篇关于人工智能的文章')
        ->stream(true);
})->send()
->onContent(function($content) {
    // 实时处理内容，比如：
    // - 实时翻译
    // - 情感分析
    // - 关键词提取
    echo $content;
})
->onFunctionCall(function($functionCall) {
    // 处理函数调用（如果启用了 Function Calling）
    $functionName = $functionCall['name'];
    $arguments = $functionCall['arguments'];
    // 执行函数...
})
->process();

// 组合多个处理器
$stream = $client->chatBuilder()
    ->model('gpt-4')
    ->user('分析这段代码并给出改进建议')
    ->stream(true)
    ->send();

// 同时进行多个操作
$stream
    ->streamToFile('analysis.md')  // 保存到文件
    ->onContent(function($content) {
        // 实时显示
        echo $content;
    })
    ->onEnd(function($fullContent) {
        // 完成后发送邮件通知
        sendEmail('分析完成', $fullContent);
    })
    ->process();
```

#### 4. 多轮对话

```php
$chatBuilder = $client->chatBuilder()
    ->model('gpt-4')
    ->system('你是一个有用的助手');

// 第一轮对话
$chatBuilder->user('北京的天气怎么样？');
$response1 = $chatBuilder->send();

// 继续对话
$chatBuilder->assistant($response1->getContent())
    ->user('那上海呢？');
$response2 = $chatBuilder->send();
```

#### 5、工具调用（函数调用）

ThinkAI 支持工具调用（Function Calling），让 AI 能够调用你定义的函数来获取实时数据或执行操作。

```php

// 方法1：使用构建器模式（推荐）
$result = $client->chatBuilder()
    ->model('gpt-4o-mini')
    ->user('今天有什么新闻？')
    ->tool('plugin', [
        'name' => 'pmbk5ezJ',
        'tool' => 'news_internal'
    ])
    ->send();

// 方法2：使用回调函数语法
$result = $client->chat(function($chat) {
    $chat->model('gpt-4o-mini')
        ->user('帮我查一下北京的天气')
        ->tool('plugin', [
            'name' => 'weather_plugin_id',
            'tool' => 'get_weather'
        ]);
})->send();

// 方法3：添加多个工具
$result = $client->chatBuilder()
    ->model('gpt-4o')
    ->user('帮我分析这张图片中的商品并查询价格')
    ->tool('plugin', [
        'name' => 'vision_analyzer',
        'tool' => 'analyze_image'
    ])
    ->tool('plugin', [
        'name' => 'price_checker',
        'tool' => 'get_product_price'
    ])
    ->image('https://example.com/product.jpg')  // 如果需要分析图片
    ->send();

// 获取工具调用结果
if ($result->hasToolCalls()) {
    foreach ($result->getToolCalls() as $toolCall) {
        echo "调用了工具: {$toolCall->name}\n";
        echo "参数: " . json_encode($toolCall->arguments, JSON_UNESCAPED_UNICODE) . "\n";
        echo "结果: {$toolCall->result}\n\n";
    }
}

// 流式响应中的工具调用
$stream = $client->chatBuilder()
    ->model('gpt-4o')
    ->user('计算 2 + 2 等于多少')
    ->tool('function', [
        'name' => 'calculator',
        'description' => '执行数学计算',
        'parameters' => [
            'type' => 'object',
            'properties' => [
                'expression' => [
                    'type' => 'string',
                    'description' => '要计算的表达式'
                ]
            ],
            'required' => ['expression']
        ]
    ])
    ->stream(true)
    ->send();

$stream->onToolCall(function($toolCall) {
    // 处理工具调用
    echo "执行计算: {$toolCall->arguments['expression']}\n";
    // 这里可以执行实际的计算并返回结果
})
->onContent(function($content) {
    echo $content;
})
->process();
```

### 图像生成

#### 1. 使用构建器模式

```php

// 生成高清方形图片
$response = $client->imageBuilder()
    ->prompt('未来城市的夜景，赛博朋克风格')
    ->model('dall-e-3')
    ->size('1024x1024')
    ->quality('hd')
    ->generate();

// 获取图片 URL
echo $response->getUrl();

// 直接保存到文件
$response->saveImage('future_city.png');

// 生成多张图片
$response = $client->imageBuilder()
    ->prompt('可爱的小狗')
    ->n(3)
    ->generate();

// 保存所有图片
$savedPaths = $response->saveAllImages('./images/', 'dog');
```

### 错误处理

```php
use think\ai\Exception\RateLimitException;
use think\ai\Exception\InvalidModelException;
use think\ai\Exception\QuotaExceededException;

try {
    $response = $client->ask('你好');
} catch (RateLimitException $e) {
    // 处理速率限制
    echo "请求太频繁，请等待 {$e->getRetryAfter()} 秒后重试";
} catch (InvalidModelException $e) {
    // 处理无效模型
    echo "无效的模型: {$e->getModel()}";
    echo "可用的模型: " . implode(', ', $e->getAvailableModels());
} catch (QuotaExceededException $e) {
    // 处理配额超限
    echo "配额已用完: {$e->getUsed()}/{$e->getLimit()}";
} catch (\Exception $e) {
    // 处理其他错误
    echo "错误: " . $e->getMessage();
}
```

### 中间件系统

```php
// 添加日志中间件
$client->addMiddleware(function ($handler) {
    return function ($request, $options) use ($handler) {
        echo "请求: " . $request->getUri() . "\n";
        
        $response = $handler($request, $options);
        
        echo "响应状态: " . $response->getStatusCode() . "\n";
        return $response;
    };
});

// 添加重试中间件
$client->addMiddleware(\GuzzleHttp\Middleware::retry(
    function ($retries, $request, $response, $exception) {
        // 重试逻辑
        if ($retries >= 3) {
            return false;
        }
        
        if ($exception instanceof \GuzzleHttp\Exception\ConnectException) {
            return true;
        }
        
        if ($response && $response->getStatusCode() >= 500) {
            return true;
        }
        
        return false;
    },
    function ($retries) {
        return 1000 * $retries;  // 延迟时间
    }
));
```

### 配置选项

```php
// 设置自定义端点
$client->setEndpoint('https://custom-ai.example.com/');

// 设置默认模型
$client->setDefaultModel('chat', 'gtp-4.1');
$client->setDefaultModel('image', 'dall-e-3');

// 使用环境变量
// 设置 THINK_AI_TOKEN 环境变量后，可以直接创建客户端
$client = Client::create();

// 添加模型兼容性中间件（自动处理不同模型的参数差异）
use think\ai\Middleware\ModelCompatibilityMiddleware;
$client->addMiddleware(ModelCompatibilityMiddleware::create());
```

### 模型兼容性

ThinkAI SDK 自动处理不同模型的参数差异：

```php
// 使用不同的模型，SDK 会自动调整参数
$models = [
    'gpt-4',
    'deepseek-chat',
    'glm-4',
    'qwen-turbo',
    'ernie-bot-4'
];

foreach ($models as $model) {
    $response = $client->chat(function($chat) use ($model) {
        $chat->model($model)
            ->user('你好，介绍一下你自己');
    })->send();
    
    echo "{$model}: " . $response->getContent() . "\n";
}
```

### 高级功能

#### 音频处理

```php
// 语音合成
$audio = $client->audio()->speech([
    'model' => 'tts-1',
    'input' => '你好，欢迎使用 ThinkAI',
    'voice' => 'alloy',
]);

// 语音识别
$transcription = $client->audio()->transcriptions([
    'model' => 'whisper-1',
    'file' => '@/path/to/audio.mp3',
]);
```

#### 视频生成

```php
// 生成视频
$video = $client->videos()->generations([
    'model' => 'video-1',
    'prompt' => '日出时分的海滩',
    'duration' => 5,
]);

// 查询视频状态
$status = $client->videos()->query([
    'video_id' => $video['video_id'],
]);
```

### 多轮函数调用

使用 `MultiTurnChatManager` 可以轻松处理需要多轮交互的函数调用场景。

#### 基本用法

```php
use think\ai\MultiTurnChatManager;

$manager = new MultiTurnChatManager($client);

// 注册工具函数
$manager->registerTool('calculator', function($args) {
    return eval('return ' . $args['expression'] . ';');
});

// 开始对话
$response = $manager->chat()
    ->model('gpt-4')
    ->system('你是一个数学助手')
    ->user('计算 123 + 456 等于多少？')
    ->function('calculator', '执行数学计算', [
        'type' => 'object',
        'properties' => [
            'expression' => [
                'type' => 'string',
                'description' => '要计算的表达式'
            ]
        ],
        'required' => ['expression']
    ])
    ->send();

// 如果有工具调用，自动继续对话
if ($response->hasToolCalls()) {
    $response = $manager->continueConversation($response);
}

echo $response->getContent();  // 输出：123 + 456 等于 579
```

#### 多工具协作

```php
// 注册多个工具
$manager->registerTools([
    'get_weather' => function($args) {
        // 模拟获取天气
        return [
            'city' => $args['city'],
            'weather' => '晴天',
            'temperature' => rand(15, 30)
        ];
    },
    'get_time' => function($args) {
        return [
            'time' => date('H:i'),
            'timezone' => $args['timezone'] ?? 'Asia/Shanghai'
        ];
    },
    'search_flights' => function($args) {
        // 模拟搜索航班
        return [
            'from' => $args['from'],
            'to' => $args['to'],
            'flights' => [
                ['flight_no' => 'CA123', 'time' => '08:00', 'price' => 1200],
                ['flight_no' => 'MU456', 'time' => '14:30', 'price' => 980]
            ]
        ];
    }
]);

// 复杂的多轮对话
$response = $manager->chat()
    ->model('gpt-4')
    ->system('你是一个旅行助手')
    ->user('我想明天从北京飞上海，天气怎么样？有什么航班？')
    ->function('get_weather', '获取天气信息', [
        'type' => 'object',
        'properties' => [
            'city' => ['type' => 'string']
        ],
        'required' => ['city']
    ])
    ->function('get_time', '获取当前时间', [
        'type' => 'object',
        'properties' => [
            'timezone' => ['type' => 'string']
        ]
    ])
    ->function('search_flights', '搜索航班', [
        'type' => 'object',
        'properties' => [
            'from' => ['type' => 'string'],
            'to' => ['type' => 'string'],
            'date' => ['type' => 'string']
        ],
        'required' => ['from', 'to']
    ])
    ->send();

// 自动处理所有工具调用
if ($response->hasToolCalls()) {
    $response = $manager->continueConversation($response);
}

echo $response->getContent();
// 输出示例：
// 明天北京的天气是晴天，温度22度。当前时间是15:30。
// 我为你找到了以下航班：
// 1. CA123 - 08:00起飞 - 价格1200元
// 2. MU456 - 14:30起飞 - 价格980元
```

#### 流式响应中的工具调用

```php
$stream = $manager->chat()
    ->model('gpt-4')
    ->user('帮我计算几个数学题')
    ->function('calculator', '数学计算器', [
        'type' => 'object',
        'properties' => [
            'expression' => ['type' => 'string']
        ],
        'required' => ['expression']
    ])
    ->stream(true)
    ->send();

$enhancedStream = $manager->processStreamWithTools($stream, function($functionCall) {
    echo "执行计算: {$functionCall->arguments['expression']}\n";
});

$enhancedStream->onContent(function($content) {
    echo $content;
})->process();
```

#### 错误处理和重试

```php
try {
    $response = $manager
        ->maxTurns(5)  // 设置最大轮次
        ->chat()
        ->model('gpt-4')
        ->user('执行复杂任务')
        ->send();
    
    if ($response->hasToolCalls()) {
        $response = $manager->continueConversation($response);
    }
    
    echo $response->getContent();
    
} catch (\RuntimeException $e) {
    echo "对话轮次过多: " . $e->getMessage();
} catch (\Exception $e) {
    echo "发生错误: " . $e->getMessage();
}
```

#### 查看对话历史

```php
// 获取完整的对话历史
$history = $manager->getMessageHistory();
foreach ($history as $message) {
    echo "[{$message['role']}] {$message['content']}\n";
}

// 清空历史开始新对话
$manager->clearHistory();
```

### 最佳实践

1. **工具设计**：确保工具函数返回结构化数据，便于模型理解
2. **错误处理**：在工具函数中捕获异常并返回友好的错误信息
3. **轮次控制**：设置合理的最大轮次，避免无限循环
4. **工具注册**：提前注册所有可能用到的工具函数
5. **流式处理**：对于长任务，使用流式响应提供更好的用户体验

#### 文本嵌入

```php
$embeddings = $client->embeddings()->create([
    'model' => 'text-embedding-ada-002',
    'input' => '机器学习是人工智能的一个分支',
]);
```
