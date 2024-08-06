<?php

namespace topthink\ai\tiktoken;

use Closure;
use Countable;
use InvalidArgumentException;
use OutOfBoundsException;
use RuntimeException;
use think\ai\Util;

class Vocab implements Countable
{
    private array $tokenToRankMap;

    private array $rankToTokenMap;

    private function __construct(array $tokenRankMap)
    {
        $this->tokenToRankMap = $tokenRankMap;

        $this->rankToTokenMap = array_map(Closure::fromCallable('strval'), array_flip($tokenRankMap));

        if (count($this->tokenToRankMap) !== count($this->rankToTokenMap)) {
            throw new InvalidArgumentException('The map of tokens and ranks has duplicates of rank');
        }
    }

    public static function fromUri($uri): self
    {
        $stream = fopen($uri, 'r');
        if ($stream === false) {
            throw new RuntimeException(sprintf('Could not open stream for URI: %s', $uri));
        }
        try {
            $meta = stream_get_meta_data($stream);

            if ($meta['seekable']) {
                rewind($stream);
            }

            $line   = fgets($stream);
            $lineNo = 1;
            $map    = [];

            while ($line !== false) {
                [$encodedToken, $rank] = explode(' ', $line);
                $token = base64_decode($encodedToken);

                if ($token === false) {
                    throw new RuntimeException(sprintf('Could not decode token "%s" at line %d', $encodedToken, $lineNo));
                }

                assert($token !== '');

                $map[$token] = (int) $rank;

                $line = fgets($stream);
                $lineNo++;
            }

            return new self($map);
        } finally {
            fclose($stream);
        }
    }

    public function tryGetRank(array $bytes): int|null
    {
        return $this->tokenToRankMap[Util::fromBytes($bytes)] ?? null;
    }

    public function getRank(array $bytes): int
    {
        return $this->tokenToRankMap[Util::fromBytes($bytes)] ?? throw new OutOfBoundsException(sprintf(
            'No rank for bytes vector: [%s]',
            implode(', ', $bytes),
        ));
    }

    public function getToken(int $rank): string
    {
        return $this->rankToTokenMap[$rank] ?? throw new OutOfBoundsException(sprintf('No token for rank: %d', $rank));
    }

    public function count(): int
    {
        return count($this->tokenToRankMap);
    }
}
