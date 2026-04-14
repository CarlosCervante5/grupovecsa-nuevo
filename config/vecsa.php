<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Prefijo de tablas MySQL (VECSA)
    |--------------------------------------------------------------------------
    |
    | Usar config() en modelos en lugar de env() directo, para que el valor
    | siga disponible cuando exista config en caché (p. ej. deploy en Railway).
    |
    */
    'db_table_prefix' => env('DB_TABLE_PREFIX', ''),

    /*
    |--------------------------------------------------------------------------
    | Experience: posts (WordPress) tratados como eventos en agenda
    |--------------------------------------------------------------------------
    |
    | Si wp_category_label contiene alguna de estas subcadenas (sin distinguir
    | mayúsculas) y el post tiene event_begin_date >= hoy, se mezclan en
    | /api/experience/upcoming_events con los MarketingEvent.
    |
    */
    'experience_event_category_keywords' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('EXPERIENCE_EVENT_WP_CATEGORY_KEYWORDS', 'evento,eventos,rodada'))
    ))),

];
