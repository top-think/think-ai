<?php

declare(strict_types = 1);

namespace think\ai\tiktoken;

use Closure;
use RuntimeException;
use think\ai\Util;
use function array_map;
use function array_slice;
use function array_values;
use function assert;
use function count;
use function implode;
use function preg_last_error_msg;
use function preg_match_all;
use function range;
use function sprintf;
use const PHP_INT_MAX;

final class Encoder
{
    private const PATTERN = '/(?i:\'s|\'t|\'re|\'ve|\'m|\'ll|\'d)|[^\r\n\p{L}\p{N}]?\p{L}+|\p{N}{1,3}| ?[^\s\p{L}\p{N}]+[\r\n]*|\s*[\r\n]+|\s+(?!\S)|\s+/u';
    private Vocab $vocab;

    public function __construct()
    {
        $this->vocab = Vocab::fromUri(__DIR__ . '/cl100k_base.tiktoken');
    }

    public function encode(string $text): array
    {
        if ($text === '') {
            return [];
        }

        if (preg_match_all(self::PATTERN, $text, $matches) === false) {
            throw new RuntimeException(sprintf('Matching failed with error: %s', preg_last_error_msg()));
        }

        $tokens = [];

        foreach ($matches[0] as $match) {
            if ($match === '') {
                continue;
            }

            $piece = Util::toBytes($match);
            $rank  = $this->vocab->tryGetRank($piece);

            if ($rank !== null) {
                $tokens[] = $rank;

                continue;
            }

            foreach ($this->mergeBytePairs($piece) as $rank) {
                $tokens[] = $rank;
            }
        }

        return $tokens;
    }

    public function decode(array $tokens): string
    {
        if ($tokens === []) {
            return '';
        }

        return implode(array_map(Closure::fromCallable([$this->vocab, 'getToken']), $tokens));
    }

    private function mergeBytePairs(array $bytes): array
    {
        /** @var list<array{int, int}> $parts */
        $parts   = array_map(
            function (int $i) use ($bytes): array {
                if ($i + 1 < count($bytes)) {
                    $piece = array_slice($bytes, $i, 2);
                    assert(count($piece) === 2);

                    return [$i, $this->vocab->tryGetRank($piece) ?? PHP_INT_MAX];
                }

                return [$i, PHP_INT_MAX];
            },
            range(0, count($bytes)),
        );
        $getRank = function (array $parts, int $startIndex) use ($bytes): int {
            if ($startIndex + 2 >= count($parts)) {
                return PHP_INT_MAX;
            }

            $offset = $parts[$startIndex][0];
            $piece  = array_slice($bytes, $offset, $parts[$startIndex + 2][0] - $offset);
            assert(count($piece) > 0);

            return $this->vocab->tryGetRank($piece) ?? PHP_INT_MAX;
        };

        while (count($parts) > 1) {
            $minRank   = PHP_INT_MAX;
            $partIndex = 0;
            $stop      = count($parts) - 1;

            for ($i = 0; $i < $stop; $i++) {
                if ($minRank <= $parts[$i][1]) {
                    continue;
                }

                $minRank   = $parts[$i][1];
                $partIndex = $i;
            }

            if ($minRank === PHP_INT_MAX) {
                break;
            }

            unset($parts[$partIndex + 1]);
            $parts = array_values($parts);

            $parts[$partIndex][1] = $getRank($parts, $partIndex);

            if ($partIndex <= 0) {
                continue;
            }

            $parts[$partIndex - 1][1] = $getRank($parts, $partIndex - 1);
        }

        $stop = count($parts) - 1;
        $res  = [];

        for ($i = 0; $i < $stop; $i++) {
            $piece = array_slice($bytes, $parts[$i][0], $parts[$i + 1][0] - $parts[$i][0]);
            assert(count($piece) > 0);

            $res[] = $this->vocab->getRank($piece);
        }

        return $res;
    }
}
