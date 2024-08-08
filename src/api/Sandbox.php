<?php

namespace think\ai\api;

use think\ai\Api;

class Sandbox extends Api
{
    public function create()
    {
        return $this->request('POST', 'sandbox');
    }

    public function execute($id, $code, $files = [])
    {
        return $this->request("POST", "sandbox/{$id}/execute", [
            'json' => [
                'code'  => $code,
                'files' => $files,
            ],
        ]);
    }

    public function delete($id)
    {
        $this->request('DELETE', "sandbox/{$id}", []);
    }
}
