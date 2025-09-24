<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);

require_once('vendor/autoload.php');

use GlobalPayments\Api\Entities\Enums\Channel;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\Services\GpApiService;

class GenerateToken
{
    // const APP_ID = 'UJqPrAhrDkGzzNoFInpzKqoI8vfZtGRV';
    // const APP_KEY = 'zCFrbrn0NKly9sB4';

    const APP_ID = 'PKTOLb3S8eY9q6mUvpFYXMjYpC2YXdwu';
    const APP_KEY = '41Mq6JlQnwFUwgJ8';

    private static $instance = null;
    private $accessToken;

    private function __construct()
    {
        $config = new GpApiConfig();
        $config->appId = self::APP_ID;
        $config->appKey = self::APP_KEY;
        $config->channel = Channel::CardNotPresent;
        $config->permissions = ["PMT_POST_Create_Single"];//, "ACC_GET_Single"];

        $accessTokenInfo = GpApiService::generateTransactionKey($config);
        $this->accessToken = $accessTokenInfo->accessToken;
    }

    public static function getInstance()
    {
        if(!self::$instance) {
            self::$instance = new GenerateToken();
        }

        return self::$instance;
    }

    public function getAccessToken()
    {
        return $this->accessToken;
    }
}
