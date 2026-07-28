<?php

return [

    /*
    |--------------------------------------------------------------------------
    | PIN Otorisasi Form Input Uniformity
    |--------------------------------------------------------------------------
    |
    | Menggantikan PIN_OTORISASI yang dulu hardcode di JavaScript (kelihatan
    | siapa saja lewat "View Page Source"). Sekarang divalidasi di server,
    | nilainya diambil dari .env supaya tidak ikut ter-commit ke git.
    |
    | Tambahkan di .env:
    | UNIFORMITY_INPUT_PIN=rphujbg22
    |
    */

    'input_pin' => env('UNIFORMITY_INPUT_PIN', 'rphujbg22'),

];
