# ThinkAI SDK 更新日志

## [2.0.0] - 2025-01-01

### 新增功能

#### 1. 响应包装类
- `ChatResponse` - 聊天响应包装，提供 `getContent()`, `getTotalTokens()` 等便捷方法
- `ImageResponse` - 图片响应包装，支持 `getUrl()`, `saveImage()`, `saveAllImages()`
- `StreamResponse` - 流式响应基础类
- `EnhancedStreamResponse` - 增强流式响应，支持事件驱动
- `AudioResponse`, `VideoResponse` - 音频视频响应包装

#### 2. 构建器模式
- `ChatBuilder` - 聊天构建器，支持链式调用
- `ImageBuilder` - 图片构建器，支持预设尺寸和风格
- 新语法支持：`$client->chat(function($chat) {...})`

#### 3. 流式接口优化
- 事件驱动：`onStart()`, `onContent()`, `onEnd()`, `onError()`
- 高级功能：
  - `streamToBrowser()` - SSE 输出到浏览器
  - `streamToFile()` - 流式写入文件
  - `buffer()` - 缓冲区处理
  - `withProgress()` - 进度跟踪

#### 4. 便捷方法
- `$client->ask('你好')` - 快速聊天
- `$client->image('猫咪')` - 快速生成图片
- `Client::create()` - 静态工厂方法

#### 5. 配置系统
- 支持环境变量 `THINK_AI_TOKEN`
- 中间件系统：`$client->addMiddleware()`
- 默认模型设置：`$client->setDefaultModel()`

#### 6. 错误处理
- `RateLimitException` - 速率限制异常
- `InvalidModelException` - 无效模型异常
- `AuthenticationException` - 认证异常
- `QuotaExceededException` - 配额超限异常
- `ContentFilterException` - 内容过滤异常
- `NetworkException` - 网络异常

#### 7. 类型安全
- `Role` 枚举 - 消息角色常量

#### 8. 模型兼容性
- `ModelCompatibilityMiddleware` - 自动处理不同模型的参数差异
- 支持 GPT、DeepSeek、智谱、通义千问、文心一言等多种模型
- 统一的 API 接口

### 使用示例

```php
// 快速开始
$client = Client::create();
$response = $client->ask('你好');

// 流式聊天
$client->chat(function($chat) {
    $chat->model('gpt-4')
        ->user('写一首诗')
        ->stream(true);
})->send()
->onContent(fn($content) => echo $content)
->process();

// 图片生成
$image = $client->imageBuilder()
    ->prompt('夕阳下的海滩')
    ->hd()
    ->landscape()
    ->generate();
$image->saveImage('sunset.png');
```

### 升级指南

1. 原有的 API 调用方式仍然兼容
2. 建议使用新的构建器模式和响应包装类
3. 流式响应现在默认返回 `EnhancedStreamResponse`
4. 错误处理建议使用新的异常类型


## [1.0.0] - 之前版本

- 基础聊天功能
- 图片生成功能
- 音频、视频、嵌入等 API 支持