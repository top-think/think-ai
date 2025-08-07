<?php

namespace think\ai\Response;

class PluginResponse extends BaseResponse
{
    /**
     * 获取插件调用返回内容
     */
    public function getContent(): string
    {
        return $this->data['plugin']['response'] ?? '';
    }

    /**
     * 设置插件数据
     */
    public function setPluginData(array $data)
    {
        $this->data['plugin'] = array_merge($this->data['plugin'], $data['plugin']);
    }
}