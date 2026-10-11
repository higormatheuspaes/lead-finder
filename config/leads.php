<?php

return [

    // Score mínimo para o lead virar "qualificado"
    'min_score' => 40,

    // Sites nesses domínios contam como "só rede social" (website_type = social)
    'social_domains' => [
        'instagram.com',
        'facebook.com',
        'linktr.ee',
        'wa.me',
        'api.whatsapp.com',
        'linkr.bio',
    ],

    // Quantos templates o sistema envia por dia, no máximo
    'outreach_daily_limit' => (int) env('OUTREACH_DAILY_LIMIT', 20),

];
