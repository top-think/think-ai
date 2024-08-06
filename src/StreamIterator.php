<?php

namespace think\ai;

use Iterator;
use Psr\Http\Message\StreamInterface;
use ReturnTypeWillChange;

class StreamIterator implements Iterator
{

    protected $index = 0;
    protected $data;

    public function __construct(protected StreamInterface $stream)
    {
    }

    #[ReturnTypeWillChange]
    public function current()
    {
        return $this->data;
    }

    #[ReturnTypeWillChange]
    public function next()
    {
        ++$this->index;
    }

    #[ReturnTypeWillChange]
    public function key()
    {
        return $this->index;
    }

    #[ReturnTypeWillChange]
    public function valid()
    {
        $buffer = '';

        while (!$this->stream->eof()) {
            $text   = $this->stream->read(1);
            $buffer .= $text;
            if ($text === "\n") {
                if (preg_match('/data:(?<data>.*)/', trim($buffer), $match)) {
                    $data = trim($match['data']);
                    if ($data != '[DONE]') {
                        $this->data = json_decode($data, true);
                        return true;
                    }
                }
            }
        }
        return false;
    }

    #[ReturnTypeWillChange]
    public function rewind()
    {
        $this->index = 0;
    }
}
