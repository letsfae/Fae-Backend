<?php

return array(

    'appNameIOS'     => array(
        'environment' =>'development',
        'certificate' =>'/usr/share/nginx/html/sonnytest/Fae-Backend/certificate.pem',
        'passPhrase'  =>'password',
        'service'     =>'apns'
    ),
    'appNameAndroid' => array(
        'environment' =>'production',
        'apiKey'      =>'yourAPIKey',
        'service'     =>'gcm'
    )

);