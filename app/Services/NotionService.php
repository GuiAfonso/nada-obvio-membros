<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class NotionService
{
    private string $token;
    private string $version;
    private string $baseUrl = 'https://api.notion.com/v1';

    public function __construct()
    {
        $this->token   = config('notion.token');
        $this->version = config('notion.version');
    }

    private function http()
    {
        return Http::withHeaders([
            'Authorization'  => 'Bearer ' . $this->token,
            'Notion-Version' => $this->version,
            'Content-Type'   => 'application/json',
        ]);
    }

    public function findUserByEmail(string $email): ?array
    {
        $response = $this->http()->post("{$this->baseUrl}/databases/" . config('notion.users_db') . '/query', [
            'filter' => [
                'property' => 'email',
                'email'    => ['equals' => $email],
            ],
        ]);

        if (! $response->successful()) {
            return null;
        }

        return $response->json('results')[0] ?? null;
    }

    public function getSuppliers(): array
    {
        $results    = [];
        $cursor     = null;
        $dbId       = config('notion.content_db');

        do {
            $body = (object) [
                'page_size' => 100,
                'sorts'     => [['property' => 'Nome do fornecedor', 'direction' => 'ascending']],
            ];

            if ($cursor) {
                $body->start_cursor = $cursor;
            }

            $response = $this->http()->post("{$this->baseUrl}/databases/{$dbId}/query", $body);

            if (! $response->successful()) break;

            $results = array_merge($results, $response->json('results') ?? []);
            $cursor  = $response->json('has_more') ? $response->json('next_cursor') : null;

        } while ($cursor);

        return $results;
    }
}
