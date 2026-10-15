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

    // Mensagem aberta no WhatsApp pelo botão da tabela; {nome} vira o nome de saudação do lead.
    // *texto* = negrito no WhatsApp.
    'whatsapp_message' => <<<'TXT'
        Olá, {nome}! Tudo bem? Me chamo Higor Matheus Paes.

        Estou entrando em contato em nome da *Himap Systems*. Ajudamos empresas e profissionais de diferentes segmentos a organizar a gestão, otimizar processos e tornar a operação mais eficiente por meio de soluções em sistemas.

        Neste primeiro contato, a ideia é mais entender a realidade do seu negócio do que apresentar uma solução de imediato.

        Hoje, olhando para a sua rotina, existe algum processo ou área da empresa que acaba dando mais dor de cabeça ou que você sente que poderia funcionar melhor?

        Se fizer sentido, podemos bater um papo rápido, sem compromisso, para entender melhor o cenário e ver se conseguimos contribuir de alguma forma.
        TXT,

    // Quantos templates o sistema envia por dia, no máximo
    'outreach_daily_limit' => (int) env('OUTREACH_DAILY_LIMIT', 20),

];
