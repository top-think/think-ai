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
