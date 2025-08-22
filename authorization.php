<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

require_once('vendor/autoload.php');
require_once('GenerateToken.php');

use GlobalPayments\Api\Entities\Enums\Environment;
use GlobalPayments\Api\Entities\Enums\Channel;
use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\Services\Secure3dService;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Entities\Enums\Secure3dStatus;
use GlobalPayments\Api\PaymentMethods\CreditCardData;

$requestData = $_REQUEST;
$serverTransactionId = $requestData['serverTransactionId'];
$paymentToken = $requestData['tokenResponse'];
$skip3ds = !empty($requestData['skip-3ds']);

function console_log($data)
{
    $data = htmlspecialchars($data, ENT_NOQUOTES);
    echo '<script>';
    echo 'if(' . $data . ') {';
    echo 'console.log(' . json_encode($data) . ')';
    echo '}';
    echo '</script>';
}

// configure client & request settings
$config = new GpApiConfig();
$config->appId = GenerateToken::APP_ID;
$config->appKey = GenerateToken::APP_KEY;
$config->environment = Environment::TEST;
$config->country = 'IE';
$config->channel = Channel::CardNotPresent;
$config->methodNotificationUrl = 'https://eowdgj59t49mm2z.m.pipedream.net/?host=' . str_replace('https://', '', $_SERVER['HTTP_ORIGIN']); //$_SERVER['HTTP_ORIGIN'] . '/methodNotificationUrl.php';;
$config->merchantContactUrl = "https://www.example.com/about";
$config->challengeNotificationUrl =  'https://eo8tvks4h47e12.m.pipedream.net/?host=' . str_replace('https://', '', $_SERVER['HTTP_ORIGIN']); // $_SERVER['HTTP_ORIGIN'] . '/challengeNotificationUrl.php';
ServicesContainer::configureService($config);

// possible GET params from ExpressPay with examples
// [
// // details
// atob('eyJwaG9uZUNvdW50cnlDb2RlIjoiKzEiLCJiaWxsaW5nQ291bnRyeSI6IlVTIiwiZW1haWwiOiJzaGFuZUBzaGFuZWxvZ3Nkb24uY29tIiwicGhvbmVOdW1iZXIiOiI1NTU1NTU1NTU1IiwiYmlsbGluZ0FkZHJlc3MiOiIxIGhlYXJ0bGFuZCB3YXksIGplZmZlcnNvbnZpbGxlLCBJTiw0NzEzMCIsInNoaXBwaW5nQWRkcmVzcyI6IjEgaGVhcnRsYW5kIHdheSwgamVmZmVyc29udmlsbGUsIElOLDQ3MTMwIiwic2hpcHBpbmdBZGRyZXNzTmFtZSI6InNoYW5lIGxvZ3Nkb24iLCJzaGlwcGluZ0NvdW50cnkiOiJVUyIsIm5hbWVPbkNhcmQiOiJzaGFuZSBsb2dzZG9uIiwicGF5bWVudFRva2VuIjoiUE1UX2E4NmMwNDIyLTBjZGEtNGY4Ny04NjhiLWUxMmQwYTdmNzcyZiIsIm1hc2tlZENhcmROdW1iZXIiOiJYWFhYWFhYWFhYWFg0MjQyIiwiZXhwaXJ5TW9udGgiOiIxMiIsImV4cGlyeVllYXIiOiIyMDMwIiwiY2FyZEJyYW5kIjoidmlzYSJ9'),
// // {"phoneCountryCode":"+1","billingCountry":"US","email":"shane@shanelogsdon.com","phoneNumber":"5555555555","billingAddress":"1 heartland way, jeffersonville, IN,47130","shippingAddress":"1 heartland way, jeffersonville, IN,47130","shippingAddressName":"shane logsdon","shippingCountry":"US","nameOnCard":"shane logsdon","paymentToken":"PMT_a86c0422-0cda-4f87-868b-e12d0a7f772f","maskedCardNumber":"XXXXXXXXXXXX4242","expiryMonth":"12","expiryYear":"2030","cardBrand":"visa"}
// // notifications
// atob('eyJyZXR1cm5VcmwiOiJodHRwOi8vbG9jYWxob3N0OjgwMDEvYXV0aG9yaXphdGlvbi5waHAiLCJjYW5jZWxVcmwiOiJodHRwOi8vbG9jYWxob3N0OjgwMDEvY2FuY2VsLnBocCJ9'),
// // {"returnUrl":"http://localhost:8001/authorization.php","cancelUrl":"http://localhost:8001/cancel.php"}
// // options
// atob('eyJpc1NoaXBwaW5nUmVxdWlyZWQiOnRydWUsInBheUJ1dHRvbkxhYmVsIjoiIn0='),
// // {"isShippingRequired":true,"payButtonLabel":""}
// // merchantInfo
// atob('eyJtZXJjaGFudElkIjoiTUVSXzgzMGU5ZTdiZGU0YTRmYjBiNjdmYTkyYjZiZTk1NDU0In0='),
// // {"merchantId":"MER_830e9e7bde4a4fb0b67fa92b6be95454"}
// ]

if (!empty($_GET['details'])) {
    $details = json_decode(base64_decode($_GET['details']));
    console_log($details);
    // {
    //     "phoneCountryCode":"+1",
    //     "billingCountry":"US",
    //     "email":"shane@shanelogsdon.com",
    //     "phoneNumber":"5555555555",
    //     "billingAddress":"1 heartland way, jeffersonville, IN,47130",
    //     "shippingAddress":"1 heartland way, jeffersonville, IN,47130",
    //     "shippingAddressName":"shane logsdon",
    //     "shippingCountry":"US",
    //     "nameOnCard":"shane logsdon",
    //     "paymentToken":"PMT_a86c0422-0cda-4f87-868b-e12d0a7f772f",
    //     "maskedCardNumber":"XXXXXXXXXXXX4242",
    //     "expiryMonth":"12",
    //     "expiryYear":"2030",
    //     "cardBrand":"visa"
    // }
    $paymentToken = $details['paymentToken'];
    $skip3ds = true;
}

?>
<!DOCTYPE html>
<html>

<head>
    <title>3D Secure 2 Authentication</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>

<body>
    <?php if (!$skip3ds): ?>
        <?php
        console_log($serverTransactionId);
        try {
            $secureEcom = Secure3dService::getAuthenticationData()
                ->withServerTransactionId($serverTransactionId)
                ->execute();
        } catch (ApiException $e) {
            //TODO: Add your error handling here
            var_dump('Obtain Authentication error:', $e);
        }

        $authenticationValue = $secureEcom->authenticationValue;
        $dsTransId = $secureEcom->directoryServerTransactionId;
        $messageVersion = $secureEcom->messageVersion;
        $eci = $secureEcom->eci;
        ?>
            <h2>3D Secure 2 Authentication</h2>
        <?php
        $condition = ($secureEcom->liabilityShift != 'YES' ||
            !in_array(
                $secureEcom->status,
                [
                    Secure3dStatus::SUCCESS_AUTHENTICATED,
                    Secure3dStatus::SUCCESS_ATTEMPT_MADE
                ]
            ));
        if (empty($condition) && !$condition) {
            echo "<p><strong>Hurray! Your trasaction was authenticated successfully!</strong></p>";
        } else {
            echo "<p><strong>Oh Dear! Your trasaction was not authenticated successfully!</strong></p>";
        }
        ?>
        <p>Server Trans ID: <?= !empty($serverTransactionId) ? htmlspecialchars($serverTransactionId, ENT_NOQUOTES) : "" ?></p>
        <p>Authentication Value: <?= !empty($authenticationValue) ? $authenticationValue : "" ?></p>
        <p>DS Trans ID: <?= $dsTransId ?></p>
        <p>Message Version: <?= $messageVersion ?></p>
        <p>ECI: <?= $eci ?></p>

        <pre>
        <?php
        print_r(htmlspecialchars(json_encode($secureEcom), ENT_NOQUOTES));
        ?>
        </pre>
    <?php endif; ?>

    <h2>Transaction details:</h2>
    <?php
    if (!$condition) {
        $paymentMethod = new CreditCardData();
        $paymentMethod->token = $paymentToken;
        if (!$skip3ds) {
            $paymentMethod->threeDSecure = $secureEcom;
        }
        // proceed to authorization with liability shift
        try {
            $response = $paymentMethod->charge(100)
                ->withCurrency('EUR')
                ->execute();
        } catch (ApiException $e) {
            // TODO: Add your error handling here
            var_dump('Error message:', $e->getMessage());
        }
        if (!empty($response)) {
            $transactionId =  $response->transactionId;
            $transactionStatus =  $response->responseMessage;
        }
    }
    ?>
    <p>Payment Token: <?= $paymentToken ?? null ?></p>
    <p>Trans ID: <?= $transactionId ?? null ?></p>
    <p>Trans status: <?= $transactionStatus ?? null ?></p>
    <pre>
<?php
if (!empty($response)) {
    print_r($response);
}
?>
</pre>