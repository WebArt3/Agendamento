<?php

    // seta tempo
    @date_default_timezone_set('America/Bahia');

    // Definir constantes

    # System
    define("SYSTEMKEY", md5('th3b4ck3ndm4st3rDefaultARTHUR##@1'));
    define("MAPS_KEY", "");
    
    define("WHATSAPP_ID", "117278378010671");
    define("WHATSAPP_KEY", "EAAWBajHLst0BAJ4xsaZBfkA555XD0QnZAYo8JLzLznuiGhB9qK9TxDYswp6VaJQasOfgBdyB7gtwj5XA9Lij4gltJNeBhzMyZCNyxQZA6vaCICjAZAMNUZAvgrpR0atJsGnMLZBmCVcqzdSvZAdYbr4zbQ8rxM7sNCLx1KVZALkBZB5803ZAwsZAmdcc");
    define("WHATSAPP_WEBHOOK_KEY", "58bd09c8-ad0b-4a60-bdc0-ab30b169ff5d");

    define("PAYMENT_KEY", 'default');
    define("ID_SPLIT_WEBART3", 'rp_rv7zAeoU1os6kpVb');
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