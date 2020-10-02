<?php
require 'vendor/autoload.php';

use GlobalPayments\Api\ServicesConfig;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Entities\Customer;
use GlobalPayments\Api\Entities\Schedule;
use GlobalPayments\Api\Entities\Enums\AccountType;
use GlobalPayments\Api\Entities\Enums\CheckType;
use GlobalPayments\Api\Entities\Enums\SecCode;
use GlobalPayments\Api\Entities\Enums\ScheduleFrequency;
use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\PaymentMethods\ECheck;
use GlobalPayments\Api\PaymentMethods\RecurringPaymentMethod;
use GlobalPayments\Api\Services\BatchService;
use GlobalPayments\Api\Utils\GenerationUtils;

function config()
{
    $config = new ServicesConfig();
    $config->secretApiKey = 'skapi_cert_MUY-AgAh6mEAsBPlHjpF9b8tVy0-b0ksOKzHmBdCsQ';
    $config->serviceUrl = 'https://cert.api2.heartlandportico.com';
    return $config;
}

function getIdentifier($identifier)
{
    $todayDate = date('Ymd');
    $identifierBase = substr(
        sprintf('%s-%%s', GenerationUtils::getGuid()),
        0,
        10
    );
    return sprintf($identifierBase, $todayDate, $identifier);
}

ServicesContainer::configure(config());

$customer = new Customer();
$customer->id = getIdentifier('Person');
$customer->firstName = 'John';
$customer->lastName = 'Doe';
$customer->status = 'Active';
$customer->email = 'john.doe@example.com';
$customer->address = new Address();
$customer->address->streetAddress1 = '123 Main St.';
$customer->address->city = 'Dallas';
$customer->address->province = 'TX';
$customer->address->postalCode = '75024';
$customer->address->country = 'USA';
$customer->workPhone = '5551112222';

$customer = $customer->create();

error_log(print_r($customer, true));

$check = new ECheck();
$check->accountType = AccountType::CHECKING;
$check->checkType = CheckType::PERSONAL;
$check->secCode = SecCode::PPD;
$check->accountNumber = '1357902468';
$check->routingNumber = '122000030';
$check->driversLicenseNumber = '7418529630';
$check->driversLicenseState = 'TX';
$check->birthYear = 1989;
$check->checkHolderName = 'John Doe';

$paymentMethod = $customer->addPaymentMethod(
    getIdentifier('CheckPpd'),
    $check
)->create();

error_log(print_r($paymentMethod, true));

$response = $paymentMethod->charge(10)
    ->withCurrency('USD')
    ->execute();

error_log(print_r($response, true));
