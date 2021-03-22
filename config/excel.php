<?php

return [
    'chunk_size' => env('EXCEL_IMPORT_CHUNK_SIZE', 1000),
    'batch_size' => env('EXCEL_IMPORT_BATCH_SIZE', 10),
    //trim: 取り込み時に trim するかしないか
    //( full:全角スペースも含めてtrim / normal:通常のtrim（半角スペース/タブ/改行/NULL文字) / false:trimしない )
    'trim'  => env('EXCEL_IMPORT_TRIM', 'full'),
];
