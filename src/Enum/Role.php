<?php

namespace think\ai\Enum;

/**
 * 消息角色枚举
 */
class Role
{
    const SYSTEM = 'system';
    const USER = 'user';
    const ASSISTANT = 'assistant';
    const FUNCTION = 'function';
    const TOOL = 'tool';
    
    /**
     * 获取所有角色
     */
    public static function all(): array
    {
        return [
            self::SYSTEM,
            self::USER,
            self::ASSISTANT,
            self::FUNCTION,
            self::TOOL,
        ];
    }
    
    /**
     * 验证角色是否有效
     */
    public static function isValid(string $role): bool
    {
        return in_array($role, self::all());
    }
}