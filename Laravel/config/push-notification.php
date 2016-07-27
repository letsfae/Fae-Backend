<?php

return array(

    'appNameIOS'     => array(
        'environment' =>'development',
        'certificate' =>'/usr/share/nginx/html/Fae-Backend/certificate.pem',
        'passPhrase'  =>'',
        'service'     =>'apns'
    ),
    'appNameAndroid' => array(
        'environment' =>'production',
        'apiKey'      =>'yourAPIKey',
        'service'     =>'gcm'
    )

);