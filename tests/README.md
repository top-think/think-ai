# ThinkAI SDK 测试套件

## 概述

本测试套件为 ThinkAI SDK v2.0 提供全面的单元测试和集成测试。

## 测试结构

```
tests/
├── Unit/                    # 单元测试
│   ├── ClientTest.php      # Client 类测试
│   ├── Response/           # 响应类测试
│   │   ├── ChatResponseTest.php
│   │   ├── ImageResponseTest.php
│   │   └── StreamResponseTest.php
│   ├── Builder/            # 构建器类测试
│   │   ├── ChatBuilderTest.php
│   │   └── ImageBuilderTest.php
│   ├── Exception/          # 异常类测试
│   │   └── ExceptionTest.php
│   └── Middleware/         # 中间件测试
│       └── ModelCompatibilityMiddlewareTest.php
├── Integration/            # 集成测试
│   ├── ChatIntegrationTest.php
│   └── ImageIntegrationTest.php
├── Fixtures/              # 测试数据
└── TestCase.php          # 测试基类
```

## 运行测试

### 安装依赖

```bash
composer install --dev
```

### 运行所有测试

```bash
composer test
```

或直接使用 PHPUnit：

```bash
./vendor/bin/phpunit
```

### 运行特定测试套件

```bash
# 只运行单元测试
./vendor/bin/phpunit --testsuite Unit

# 只运行集成测试
./vendor/bin/phpunit --testsuite Integration
```

### 运行特定测试类

```bash
./vendor/bin/phpunit tests/Unit/ClientTest.php
```

### 生成代码覆盖率报告

```bash
composer test-coverage
```

覆盖率报告将生成在 `coverage/` 目录中。

## 测试覆盖范围

### 单元测试

1. **Client 类**
   - 构造函数和初始化
   - API 方法返回值
   - 构建器创建
   - 中间件管理
   - HTTP 客户端配置

2. **响应类**
   - ChatResponse: 内容获取、token 统计、角色信息
   - ImageResponse: URL 获取、图片保存、批量处理
   - StreamResponse: 流式处理、事件回调、内容累积

3. **构建器类**
   - ChatBuilder: 参数设置、消息管理、链式调用
   - ImageBuilder: 图片参数、预设尺寸、模式切换

4. **异常类**
   - 各种异常类型的消息和属性
   - 异常层次结构

5. **中间件**
   - 模型兼容性处理
   - 参数自动调整

### 集成测试

1. **聊天功能**
   - 完整对话流程
   - 流式响应处理
   - 多轮对话
   - 错误处理

2. **图片生成**
   - 单图和多图生成
   - 不同格式处理
   - 图片保存功能

## 编写新测试

### 测试命名规范

- 测试类名以 `Test` 结尾
- 测试方法以 `test` 开头
- 使用描述性的方法名

### 示例

```php
public function testClientThrowsExceptionWithoutToken()
{
    $this->expectException(Exception::class);
    new Client();
}
```

### Mock 对象

使用 Mockery 创建 mock 对象：

```php
$mockClient = Mockery::mock(Client::class);
$mockClient->shouldReceive('chat')->andReturn($chatApi);
```

### 测试数据

使用 `TestCase` 基类提供的辅助方法：

```php
$response = $this->createMockResponse(['key' => 'value']);
$stream = $this->createMockStreamResponse($chunks);
```

## 持续集成

建议在 CI/CD 流程中运行测试：

```yaml
# GitHub Actions 示例
- name: Run tests
  run: composer test

- name: Upload coverage
  run: composer test-coverage
```

## 注意事项

1. 测试不会发送真实的 API 请求
2. 使用 mock 对象模拟 HTTP 响应
3. 测试环境变量在 `phpunit.xml` 中配置
4. 确保测试独立性，避免测试间依赖