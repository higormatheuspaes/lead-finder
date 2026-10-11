<?php

namespace App\Services\Places;

use App\Services\ApiUsageGuard;
use Illuminate\Support\Facades\Http;

class PlacesClient
{
    private const FIELDS = [
        'places.id', 'places.displayName', 'places.formattedAddress',
        'places.internationalPhoneNumber', 'places.websiteUri',
        'places.rating', 'places.userRatingCount', 'places.primaryType',
        'places.businessStatus', 'places.googleMapsUri', 'nextPageToken',
    ];

    public function __construct(private ApiUsageGuard $guard) {}

    public function searchText(string $query, ?string $pageToken = null): array
    {
        $this->guard->consume('google_places', config('services.google_places.monthly_limit'));

        $body = [
            'textQuery' => $query,
            'languageCode' => 'pt-BR',
            'regionCode' => 'BR',
            'pageSize' => 20,
        ];
        if ($pageToken) {
            $body['pageToken'] = $pageToken;
        }

        // Sem retry() de propósito: cada tentativa seria uma chamada cobrada fora do contador.
        return Http::withHeaders([
            'X-Goog-Api-Key' => config('services.google_places.key'),
            'X-Goog-FieldMask' => implode(',', self::FIELDS),
        ])
            ->timeout(20)
            ->post('https://places.googleapis.com/v1/places:searchText', $body)
            ->throw()
            ->json();
    }
}
