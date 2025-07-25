<?php

declare(strict_types=1);

namespace App\Content\Desire\ImageExtraction;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class ExtractPicsApiService
{
    private const API_BASE_URL = 'https://api.extract.pics/v0/';
    private const EXTRACTIONS_ENDPOINT = 'extractions';

    private Client $client;

    public function __construct(
        #[Autowire('%env(EXTRACT_PICS_API_KEY)%')]
        private readonly string $apiKey,
    )
    {
        $this->client = new Client([
            'base_uri' => self::API_BASE_URL,
            'timeout' => 10.0,
            'headers' => [
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    public function startExtraction(string $url): PicsExtractionResult
    {
        try {
            $response = $this->client->post(self::EXTRACTIONS_ENDPOINT, [
                'body' => json_encode(['url' => $url]),
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            return PicsExtractionResult::fromArray($data['data']);
        } catch (GuzzleException $e) {
            throw new RuntimeException("API request failed: {$e->getMessage()}");
        }
    }

    public function checkExtractionStatus(string $id): PicsExtractionResult
    {
        try {
            $response = $this->client->get(self::EXTRACTIONS_ENDPOINT . '/' . $id);

            $data = json_decode($response->getBody()->getContents(), true);

            return PicsExtractionResult::fromArray($data['data']);
        } catch (GuzzleException $e) {
            throw new RuntimeException("Failed to fetch extraction status: {$e->getMessage()}");
        }
    }
}
