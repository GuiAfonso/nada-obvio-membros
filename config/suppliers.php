<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Destaques (tags) de avaliação
    |--------------------------------------------------------------------------
    |
    | Lista fixa de destaques que um membro pode marcar ao avaliar um
    | fornecedor. A cor referencia as classes .tag-{color} já existentes
    | em resources/views/dashboard.blade.php.
    |
    */
    'destaques' => [
        'rapido' => ['label' => 'Rápido', 'color' => 'green'],
        'preco_justo' => ['label' => 'Preço justo', 'color' => 'blue'],
        'bom_atendimento' => ['label' => 'Bom atendimento', 'color' => 'purple'],
        'qualidade' => ['label' => 'Qualidade', 'color' => 'yellow'],
        'recomendo' => ['label' => 'Recomendo', 'color' => 'orange'],
    ],
];
