<?php

namespace think\ai\api;

use think\ai\Api;

class Skill extends Api
{
    /**
     * 获取技能列表，可按分类筛选。
     *
     * @param array $params 支持 category_id 参数
     */
    public function list($params = [])
    {
        return $this->request('GET', 'skill', [
            'query' => $params,
        ]);
    }

    /**
     * 获取技能分类列表。
     */
    public function categories()
    {
        return $this->request('GET', 'skill/category');
    }
}
