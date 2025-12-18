<?php

namespace think\ai\api;

use SplFileInfo;
use think\ai\Api;

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

    public function readFile($name, $path)
    {
        return $this->request("POST", "sandbox/{$name}/read", [
            'json' => [
                'path' => $path,
            ],
        ]);
    }

    public function writeFile($name, $path, $content)
    {
        return $this->request("POST", "sandbox/{$name}/write", [
            'json' => [
                'path'    => $path,
                'content' => $content,
            ],
        ]);
    }

    public function uploadFile($name, $path, $file)
    {
        if ($file instanceof SplFileInfo) {
            $file = $file->getRealPath();
        }
        try {
            $content = fopen($file, 'r');

            return $this->request("POST", "sandbox/{$name}/upload", [
                'multipart' => [
                    [
                        'name'     => 'path',
                        'contents' => $path,
                    ],
                    [
                        'name'     => 'file',
                        'contents' => $content,
                        'filename' => basename($path),
                    ],
                ],
            ]);
        } finally {
            fclose($content);
        }
    }

    public function runCode($name, $code, $files = [])
    {
        return $this->request("POST", "sandbox/{$name}/code", [
            'json' => [
                'code'  => $code,
                'files' => $files,
            ],
        ]);
    }

    public function runCommand($name, $command)
    {
        return $this->request("POST", "sandbox/{$name}/command", [
            'json' => [
                'command' => $command,
            ],
        ]);
    }

    public function delete($name)
    {
        $this->request('DELETE', "sandbox/{$name}", []);
    }
}
