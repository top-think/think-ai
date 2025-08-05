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
use think\ai\Enum\Model;

$client = Client::create();

// 方法一：直接使用构建器
$response = $client->chatBuilder()
    ->model(Model::GPT_4)
    ->system('你是一个专业的程序员')
    ->user('如何实现快速排序？')
    ->temperature(0.7)
    ->maxTokens(1000)
    ->send();

// 方法二：使用回调函数（更优雅）
$response = $client->chat(function($chat) {
    $chat->model(Model::GPT_4)
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
    ->model(Model::GPT_35_TURBO)
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

// 方法5：使用缓冲区（每100字符处理一次）
$stream->buffer(100)
    ->onContent(function($bufferedContent) {
        // 处理缓冲的内容
        processContent($bufferedContent);
    })
    ->process();

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
    $chat->model(Model::GPT_4)
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
    ->model(Model::GPT_4)
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
    ->model(Model::GPT_4)
    ->system('你是一个有用的助手');

// 第一轮对话
$chatBuilder->user('北京的天气怎么样？');
$response1 = $chatBuilder->send();

// 继续对话
$chatBuilder->assistant($response1->getContent())
    ->user('那上海呢？');
$response2 = $chatBuilder->send();
```

#### 5. 使用类型安全的请求类

```php
use think\ai\Request\ChatRequest;
use think\ai\Enum\Model;

$request = ChatRequest::create()
    ->model(Model::DEEPSEEK_CHAT)
    ->system('你是一个数学老师')
    ->user('解释一下勾股定理')
    ->temperature(0.5)
    ->maxTokens(500);

$response = $client->chat()->completions($request->toArray());
```

### 图像生成

#### 1. 使用构建器模式

```php
use think\ai\Enum\Model;
use think\ai\Enum\ImageSize;

// 生成高清方形图片
$response = $client->imageBuilder()
    ->prompt('未来城市的夜景，赛博朋克风格')
    ->model(Model::DALL_E_3)
    ->size(ImageSize::SQUARE_HD)
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

#### 2. 使用预设尺寸

```php
// 横向图片
$landscape = $client->imageBuilder()
    ->prompt('美丽的山水风景')
    ->landscape()
    ->generate();

// 纵向图片
$portrait = $client->imageBuilder()
    ->prompt('时尚人像摄影')
    ->portrait()
    ->natural()  // 使用自然风格
    ->generate();
```

#### 3. 使用类型安全的请求类

```php
use think\ai\Request\ImageRequest;

$request = ImageRequest::create('梦幻般的星空')
    ->model(Model::DALL_E_3)
    ->hd()
    ->square()
    ->n(2);

$response = $client->images()->generations($request->toArray());
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
$client->setDefaultModel('chat', Model::GPT_4);
$client->setDefaultModel('image', Model::DALL_E_3);

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
    Model::GPT_4,
    Model::DEEPSEEK_CHAT,
    Model::GLM_4,
    Model::QWEN_TURBO,
    Model::ERNIE_BOT_4
];

foreach ($models as $model) {
    $response = $client->chat(function($chat) use ($model) {
        $chat->model($model)
            ->user('你好，介绍一下你自己');
    })->send();
    
    echo "{$model}: " . $response->getContent() . "\n";
}

// SDK 会自动处理：
// - GPT-4 Vision 的图片消息格式
// - DeepSeek 不支持的参数
// - GLM 的 temperature 范围限制
// - 文心一言的参数名差异
// - 通义千问的特殊参数
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

#### 文本嵌入

```php
$embeddings = $client->embeddings()->create([
    'model' => 'text-embedding-ada-002',
    'input' => '机器学习是人工智能的一个分支',
]);
```

### 模型枚举使用

```php
use think\ai\Enum\Model;

// 获取所有聊天模型
$chatModels = Model::getChatModels();

// 获取所有图像模型
$imageModels = Model::getImageModels();

// 检查模型是否支持视觉
if (Model::supportsVision(Model::GPT_4_VISION)) {
    // 使用视觉功能
}

// 获取模型的上下文长度
$contextLength = Model::getContextLength(Model::GPT_4_TURBO);
```
