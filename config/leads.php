<?php

return [

    // DDDs considerados "locais". Número de outro DDD = provável matriz de franquia
    'local_ddds' => ['47'],

    // Score mínimo para o lead virar "qualificado"
    'min_score' => 40,

    // Se o nome ou o site do negócio tiver uma dessas palavras, é descartado como franquia
    // (escreva em minúsculas; o código compara em minúsculas)
    'franchise_keywords' => [
        'selfit',
        'smart fit',
        'panobianco',
        'force one',
        '26fit',
        'bluefit',
        'sesc',
        'senac',
        'academia ad3',
    ],

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