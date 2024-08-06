<?php

namespace think\ai;

abstract class Api
{
    public function __construct(protected Client $client)
    {
    }

    protected function request($method, $uri, $options = [])
    {
        $client = $this->client->createHttpClient();

        $response = $client->request($method, $uri, $options);

        $contentType = $response->getHeaderLine('Content-Type');

        if (str_starts_with($contentType, 'text/event-stream')) {
            return $response->getBody();
        }

        $statusCode = $response->getStatusCode();
        $isOk       = $statusCode >= 200 && $statusCode < 300;
        $content    = $response->getBody()->getContents();
        $result     = $content ? json_decode($content, true) : null;

        if (!$isOk) {
            if ($statusCode == 422) {
                throw new Exception($content);
            }
            throw new Exception($result['message'] ?? 'Unknown error');
        }

        return $result;
    }
}
