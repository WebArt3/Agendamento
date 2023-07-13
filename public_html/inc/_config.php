<?php

    // seta tempo
    @date_default_timezone_set('America/Bahia');

    // Definir constantes

    # System
    define("SYSTEMKEY", md5('th3b4ck3ndm4st3rDefaultARTHURAtendimento##@1'));
    define("MAPS_KEY", "");
    
    define("WHATSAPP_ID", "");
    define("WHATSAPP_KEY", "");
    define("WHATSAPP_WEBHOOK_KEY", "");

    define("PAYMENT_KEY", 'default');
    define("ID_SPLIT_WEBART3", '');
    define("ID_SPLIT", '');
    define("WEBART3_PORCENTAGEM", 30);

    $base = __DIR__."/../storage/";

    if (!is_dir($base)) {
        mkdir($base, 0700);
    }

    define("PATH_UPLOADS", $base);

    // Charset UTF-8
    header('Content-Type: text/html; charset=utf-8');

?>