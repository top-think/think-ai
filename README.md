用法

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
