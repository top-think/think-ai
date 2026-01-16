<?php

namespace think\ai\api;

use Psr\Http\Message\StreamInterface;
use think\ai\Api;
use think\ai\StreamIterator;

class Sandbox extends Api
{
    public function create($name = null)
    {
        return $this->request('POST', 'sandbox', [
            'json' => [
                'name' => $name,
            ],
        ]);
    }

    public function get($name)
    {
        return $this->request('GET', "sandbox/{$name}");
    }

    public function delete($name)
    {
        $this->request('DELETE', "sandbox/{$name}");
    }

    public function listFile($id, $path = null)
    {
        return $this->request('GET', "sandbox/{$id}/list", [
            'query' => [
                'path' => $path,
            ],
        ]);
    }

    public function readFile($id, $path)
    {
        return $this->request('GET', "sandbox/{$id}/read", [
            'query' => [
                'path' => $path,
            ],
        ]);
    }

    public function writeFile($id, $path, $content)
    {
        return $this->request('POST', "sandbox/{$id}/write", [
            'json' => [
                'path'    => $path,
                'content' => $content,
            ],
        ]);
    }

    public function editFile($id, $path, $oldStr, $newStr = '')
    {
        return $this->request('POST', "sandbox/{$id}/edit", [
            'json' => [
                'path'    => $path,
                'old_str' => $oldStr,
                'new_str' => $newStr,
            ],
        ]);
    }

    public function uploadFile($id, $path, $file)
    {
        if ($file instanceof \SplFileInfo) {
            $file = $file->getRealPath();
        } elseif (!is_resource($file)) {
            $file = fopen($file, 'r');
        }

        return $this->request('POST', "sandbox/{$id}/upload", [
            'multipart' => [
                [
                    'name'     => 'path',
                    'contents' => $path,
                ],
                [
                    'name'     => 'file',
                    'contents' => $file,
                    'filename' => basename($path),
                ],
            ],
        ]);
    }

    public function downloadFile($id, $path)
    {
        return $this->request('GET', "sandbox/{$id}/download", [
            'query' => [
                'path' => $path,
            ],
            'stream' => true,
        ]);
    }

    public function runCode($id, $code, $files = [])
    {
        return $this->request('POST', "sandbox/{$id}/code", [
            'json' => [
                'code'  => $code,
                'files' => $files,
            ],
        ]);
    }

    public function runCommand($id, $command, $stream = false)
    {
        $res = $this->request('POST', "sandbox/{$id}/command", [
            'json' => [
                'command' => $command,
                'stream'  => $stream,
            ],
            'stream' => $stream,
        ]);

        if ($res instanceof StreamInterface) {
            return new StreamIterator($res);
        }

        return $res;
    }
}
