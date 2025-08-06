<?php

namespace think\ai\Builder;

use think\ai\Client;

class EmbeddingsBuilder extends BaseBuilder
{
    
    /**
     * 设置输入文本（单个或多个）
     */
    public function input($input): self
    {
        $this->params['input'] = $input;
        return $this;
    }
    
    /**
     * 添加单个输入文本
     */
    public function addInput(string $text): self
    {
        if (!isset($this->params['input'])) {
            $this->params['input'] = [];
        }
        
        if (is_string($this->params['input'])) {
            $this->params['input'] = [$this->params['input']];
        }
        
        $this->params['input'][] = $text;
        return $this;
    }
    
    /**
     * 设置编码格式
     */
    public function encodingFormat(string $format): self
    {
        $this->params['encoding_format'] = $format;
        return $this;
    }
    
    /**
     * 设置维度
     */
    public function dimensions(int $dimensions): self
    {
        $this->params['dimensions'] = $dimensions;
        return $this;
    }
    
    
    
    /**
     * 创建嵌入向量
     */
    public function create()
    {
        $this->validateRequired(['model', 'input']);
        
        return $this->client->embeddings()->create($this->params);
    }
    
    /**
     * 批量创建嵌入向量
     */
    public function batch(array $inputs)
    {
        $this->validateRequired(['model']);
        
        $this->params['input'] = $inputs;
        
        return $this->client->embeddings()->create($this->params);
    }
    
    /**
     * 获取嵌入向量并返回数组
     */
    public function get(): array
    {
        $response = $this->create();
        
        if (isset($response['data']) && is_array($response['data'])) {
            return array_map(function($item) {
                return $item['embedding'] ?? [];
            }, $response['data']);
        }
        
        return [];
    }
    
    /**
     * 获取单个嵌入向量
     */
    public function getOne(): array
    {
        $embeddings = $this->get();
        return $embeddings[0] ?? [];
    }
    
    /**
     * 计算两个向量的余弦相似度
     */
    public static function cosineSimilarity(array $vec1, array $vec2): float
    {
        if (count($vec1) !== count($vec2)) {
            throw new \InvalidArgumentException('向量维度必须相同');
        }
        
        $dotProduct = 0;
        $norm1 = 0;
        $norm2 = 0;
        
        for ($i = 0; $i < count($vec1); $i++) {
            $dotProduct += $vec1[$i] * $vec2[$i];
            $norm1 += $vec1[$i] * $vec1[$i];
            $norm2 += $vec2[$i] * $vec2[$i];
        }
        
        $norm1 = sqrt($norm1);
        $norm2 = sqrt($norm2);
        
        if ($norm1 == 0 || $norm2 == 0) {
            return 0;
        }
        
        return $dotProduct / ($norm1 * $norm2);
    }
    
    /**
     * 计算欧氏距离
     */
    public static function euclideanDistance(array $vec1, array $vec2): float
    {
        if (count($vec1) !== count($vec2)) {
            throw new \InvalidArgumentException('向量维度必须相同');
        }
        
        $sum = 0;
        for ($i = 0; $i < count($vec1); $i++) {
            $diff = $vec1[$i] - $vec2[$i];
            $sum += $diff * $diff;
        }
        
        return sqrt($sum);
    }
}