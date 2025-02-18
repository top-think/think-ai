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
### 聊天
```php
use think\ai\Client;

$client = new Client('YOUR_TOKEN');

//非流式输出
$result = $client->chat()->completions([
    'model' => 'gpt-3.5-turbo',
    'messages' => [
        ['role' => 'user', 'content' => 'Hello!'],
    ],
    'stream'=>false,
]);
dump($result);

//流式输出
$result = $client->chat()->completions([
    'model' => 'gpt-3.5-turbo',
    'messages' => [
        ['role' => 'user', 'content' => 'Hello!'],
    ],
    'stream'=>true,
]);
foreach($result as $chunk){
    dump($chunk);
}
```

### 图像
```php
use think\ai\Client;

$client = new Client('YOUR_TOKEN');

//画图
$client->images()->generations($params);
//涂抹编辑
$client->images()->inpainting($params);
//图像扩展
$client->images()->outpainting($params);
```

...其他用法类似
