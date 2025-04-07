<?php

namespace App\Services;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class ApiService
{
    protected Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://exsrv.asarta.ru/api/test-task/',
            'headers' => [
                'Authorization' => 'Bearer 213b7sHEbEqNqmbLbmRcaQ27HMsrzmMcQqT5THqU5cMLv0B',
                'Accept' => 'text/plain',
            ],
            'verify' => false,
        ]);
    }

    public function getOnuData(): string
    {
        try {
            $response = $this->client->get('get_onu_data.php');
            return $response->getBody()->getContents();
        } catch (GuzzleException $e) {
            throw new Exception('Ошибка при получении ONU Data: ' . $e->getMessage());
        }
    }

    public function getOnuStats(): string
    {
        try {
            $response = $this->client->get('get_onu_stats.php');
            return $response->getBody()->getContents();
        } catch (GuzzleException $e) {
            throw new Exception('Ошибка при получении ONU Stats: ' . $e->getMessage());
        }
    }
}
