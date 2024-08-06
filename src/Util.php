<?php

namespace think\ai;

use Closure;
use topthink\ai\tiktoken\Encoder;

final class Util
{
    protected static ?Encoder $encoder = null;

    public static function toBytes(string $text): array
    {
        return array_map(Closure::fromCallable('hexdec'), str_split(bin2hex($text), 2));
    }

    public static function fromBytes(array $bytes): string
    {
        return pack('C*', ...$bytes);
    }

    public static function tikToken($messages)
    {
        $perMessage = 3;
        $perName    = 1;

        if (self::$encoder === null) {
            self::$encoder = new Encoder();
        }

        $encoder = self::$encoder;

        if (is_string($messages)) {
            return count($encoder->encode($messages));
        }

        $nums = 0;

        foreach ($messages as $message) {
            $nums += $perMessage;
            foreach ($message as $key => $value) {
                if ($key == 'tool_calls') {
                    foreach ($value as $call) {
                        foreach ($call as $cKey => $cValue) {
                            $nums += count($encoder->encode($cKey));
                            if ($cKey == 'function') {
                                foreach ($cValue as $fKey => $fValue) {
                                    $nums += count($encoder->encode($fKey));
                                    $nums += count($encoder->encode($fValue));
                                }
                            } else {
                                $nums += count($encoder->encode($cValue));
                            }
                        }
                    }
                } else {
                    if (is_array($value)) {
                        $text = '';
                        foreach ($value as $v) {
                            if (is_array($v)) {
                                switch ($v['type']) {
                                    case 'text':
                                        $text .= $v['text'];
                                        break;
                                    case 'image_url':
                                        $detail = $v['image_url']['detail'] ?? 'high';
                                        $nums   += $detail == 'low' ? 85 : 1000;
                                        break;
                                }
                            }
                        }
                        $value = $text;
                    }
                    if (is_string($value)) {
                        $nums += count($encoder->encode($value));
                    }
                }

                if ($key == 'name') {
                    $nums += $perName;
                }
            }
        }

        $nums += 3;

        return $nums;
    }

    public static function mergeDeep(array ...$arrays): array
    {
        $result = [];
        foreach ($arrays as $array) {
            foreach ($array as $key => $value) {
                if (is_integer($key)) {
                    $result[] = $value;
                } elseif (isset($result[$key]) && is_array($result[$key]) && is_array($value)) {
                    $result[$key] = self::mergeDeep(
                        $result[$key],
                        $value,
                    );
                } else {
                    $result[$key] = $value;
                }
            }
        }
        return $result;
    }
}
