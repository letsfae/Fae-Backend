<?php

return array(

    'appNameIOS'     => array(
        'environment' =>'development',
        'certificate' =>'/usr/share/nginx/html/dev/Fae-Backend/certificates.pem',
        'passPhrase'  =>'faefae789!',
        'service'     =>'apns'
    ),
    'appNameAndroid' => array(
        'environment' =>'production',
        'apiKey'      =>'yourAPIKey',
        'service'     =>'gcm'
    )

);