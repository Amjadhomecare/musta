<?php

namespace App\Services;

use GuzzleHttp\Client;

use Illuminate\Support\Facades\Http;

class AzureDocService
{
    private string $endpoint;
    private string $key;
    private string $model;

    public function __construct()
    {
        $this->endpoint = rtrim(config('services.azure_doc.endpoint'), '/');
        $this->key      = config('services.azure_doc.key');
        $this->model    = config('services.azure_doc.model');
    }

    /**
     * @return array full JSON payload from GET …/analyzeResults/{resultId}
     * @throws \RuntimeException on failure
     */
    public function analyze(string $filePath): array
    {
        // 1️⃣  POST  …documentModels/{modelId}:analyze
        $postUrl = sprintf(
            '%s/documentintelligence/documentModels/%s:analyze?api-version=2024-11-30',
            $this->endpoint,
            $this->model
        );

        $post = Http::withHeaders([
                'Ocp-Apim-Subscription-Key' => $this->key,
                'Content-Type'              => 'application/octet-stream',
            ])
            ->withBody(file_get_contents($filePath), 'application/octet-stream')
            ->post($postUrl);

        if ($post->status() !== 202) {
            throw new \RuntimeException("Analyze POST failed: {$post->body()}");
        }

        $operationUrl = $post->header('Operation-Location');   // full URL

        // 2️⃣  Parse pieces we need for GET
        //  ➜ …documentModels/{modelId}/analyzeResults/{resultId}
        $parts    = parse_url($operationUrl);
        $segments = explode('/', $parts['path']);
        $resultId = end($segments);            // last piece

        // 3️⃣  Poll GET until status === succeeded
        $getUrl = sprintf(
            '%s/documentintelligence/documentModels/%s/analyzeResults/%s?api-version=2024-11-30',
            $this->endpoint,
            $this->model,
            $resultId
        );

        do {
            usleep(800_000);                   // 0.8 s
            $get = Http::withHeaders([
                    'Ocp-Apim-Subscription-Key' => $this->key,
                ])->get($getUrl)->json();

            $status = $get['status'] ?? 'unknown';
        } while (in_array($status, ['notStarted', 'running']));

        if ($status !== 'succeeded') {
            throw new \RuntimeException('Azure analysis ended with status: ' . $status);
        }

        return $get;   // entire JSON
    }
}