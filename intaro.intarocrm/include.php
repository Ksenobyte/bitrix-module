<?php
CModule::AddAutoloadClasses(
    'intaro.intarocrm', // module name
    array (
        'RestNormalizer'                                   => 'classes/general/RestNormalizer.php',
        'Logger'                                           => 'classes/general/Logger.php',
        'RetailCrm\RestApi'                                => 'classes/general/RestApi.php',
        'RetailCrm\Response\ApiResponse'                   => 'classes/general/Response/ApiResponse.php',
        'ICrmOrderActions'                                 => 'classes/general/ICrmOrderActions.php',
        'ICMLLoader'                                       => 'classes/general/ICMLLoader.php',
        'ICrmOrderEvent'                                   => 'classes/general/events/ICrmOrderEvent.php',
        'RetailCrm\Exception\InvalidJsonException'         => 'classes/general/Exception/InvalidJsonException.php',
        'RetailCrm\Exception\CurlException'                => 'classes/general/Exception/CurlException.php',
        'RetailCrm\Exception\WrongParamException'          => 'classes/general/Exception/WrongParamException.php',
        'RetailCrm\Exception\DispatchInterruptException'   => 'classes/general/Exception/DispatchInterruptException.php',      
    )
);