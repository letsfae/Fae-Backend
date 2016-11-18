<?php

return array(

    'appNameIOS'     => array(
        'environment' =>'development',
        'certificate' =>'/usr/share/nginx/html/dev/Fae-Backend/certificate.pem',
        'passPhrase'  =>'A1234567',
        'service'     =>'apns'
    ),
    'appNameAndroid' => array(
        'environment' =>'production',
        'apiKey'      =>'yourAPIKey',
        'service'     =>'gcm'
    )

);