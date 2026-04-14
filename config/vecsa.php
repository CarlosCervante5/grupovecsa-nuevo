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

];
