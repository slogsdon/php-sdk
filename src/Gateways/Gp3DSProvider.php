<?php

namespace GlobalPayments\Api\Gateways;

use GlobalPayments\Api\Builders\Secure3dBuilder;
use GlobalPayments\Api\Entities\ThreeDSecure;
use GlobalPayments\Api\Entities\Transaction;
use GlobalPayments\Api\Entities\Enums\Secure3dVersion;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\Utils\GenerationUtils;

class Gp3DSProvider extends RestGateway implements ISecure3dProvider {
    /** @var string */
    private $accountId;
    /** @var string */
    private $challengeNotificationUrl;
    /** @var string */
    private $merchantContactUrl;
    /** @var string */
    private $merchantId;
    /** @var string */
    private $methodNotificationUrl;
    /** @var string */
    private $sharedSecret;

    /** @return Secure3dVersion */
    public function getVersion(){
        return Secure3dVersion::TWO;
    }

    /** @return void */
    public function setAccountId(string $accountId){
        $this->accountId = $accountId;
    }
    /** @return void */
    public function setMerchantId(string $merchantId){
        $this->merchantId = $merchantId;
    }
    /** @return void */
    public function setSharedSecret(string $sharedSecret){
        $this->sharedSecret = $sharedSecret;
    }
    /** @return void */
    public function setChallengeNotificationUrl(string $challengNotificationUrl){
        $this->challengeNotificationUrl = $challengeNotificationUrl;
    }
    /** @return void */
    public function setMerchantContactUrl(string $merchantContactUrl){
        $this->merchantContactUrl = $merchantContactUrl;
    }
    /** @return void */
    public function setMethodNotificationUrl(string $methodNotificationUrl){
        $this->methodNotificationUrl = $methodNotificationUrl;
    }

    /** 
     * @throws ApiException
     * @return Transaction */
    public function processSecure3d(Secure3dBuilder $builder) {
        $transType = $builder->getTransactionType();
        $timestamp = date('yyyy-MM-dd\Thh:mm:ss.SSSSSS');
        $cardData = $builder->getPaymentMethod();

        $request = [];
        if ($transType === TransactionType::VERIFY_ENROLLED) {
            $hash = GenerationUtils::generateHash($this->sharedSecret, $timestamp . $this->merchantId . $cardData->number);

            $request['Authorization'] = sprintf('securehash %s', $hash);
            $request['request_timestamp'] = $timestamp;
            $request['merchant_id'] = $this->merchantId;
            $request['account_id'] = $this->accountId;
            $request['number'] = $cardData->number;
            $request['scheme'] = strtoupper($this->mapCardScheme($cardData->getCardType()));
            $request['method_notification_url'] = $this->methodNotificationUrl;

            $rawResponse = $this->doTransaction('POST', 'protocol-versions', json_encode($request));
            return $this->mapResponse($rawResponse);
        } else if ($transType === TransactionType::VERIFY_SIGNATURE) {
            $hash = GenerationUtils::generateHash($this->sharedSecret, $timestamp . $this->merchantId . $builder->getServerTransactionId());

            $request['Authorization'] = sprintf('securehash %s', $hash);

            $queryValues = [];
            $queryValues['merchant_id'] = $this->merchantId;
            $queryValues['request_timestamp'] = $timestamp;

            $rawResponse = $this->doTransaction('GET', sprintf('authentication/%s', $builder->getServerTransactionId()), json_encode($request), $queryValues);
            return $this->mapResponse($rawResponse);
        } else if ($transType === TransactionType::INITIATE_AUTHENTICATION) {
            $orderId = $builder->orderId;
            if (empty($orderId)) {
                $orderId = GenerationUtils::generateOrderId();
            }
            $secureEcom = $cardData->threeDSecure;

            $hash = GenerationUtils::generateHash($this->sharedSecret, $timestamp . $this->merchantId . $cardData->number . $secureEcom->serverTransactionId);
            $request['Authorization'] = sprintf('securehash %s', $hash);

            $request['request_timestamp'] = $timestamp;
            $request['authentication_source'] = $builder->getAuthenticationSource();
            $request['authentication_request_type'] = $builder->getAuthenticationRequestType();
            $request['message_category'] = $builder->getMessageCategory();
            $request['message_version'] = '2.1.0';
            $request['server_trans_id'] = $secureEcom->serverTransactionId;
            $request['merchant_id'] = $this->merchantId;
            $request['account_id'] = $this->accountId;
            $request['challenge_notification_url'] = $this->challengeNotificationUrl;
            $request['method_url_completion'] = $builder->getMethodUrlCompletion();
            $request['merchant_contact_url'] = $this->merchantContactUrl;

            // card details
            $request['card_detail']['number'] = $cardData->number;
            $request['card_detail']['scheme'] = strtoupper($cardData->getCardType());
            $request['card_detail']['expiry_month'] = $cardData->expMonth;
            $request['card_detail']['expiry_year'] = $cardData->expYear;
            $request['card_detail']['full_name'] = $cardData->cardHolderName;

            // order details
            $request['order']['amount'] = (string)$builder->getAmount();
            $request['order']['currency'] = $builder->getCurrency();
            $request['order']['id'] = $orderId;
            $request['order']['address_match_indicator'] = ($builder->isAddressMatchIndicator() ? 'true' : 'false');
            if ($builder->getOrderCreateDate() != null) {
                $request['order']['date_time_created'] = date('yyyy-MM-dd\Thh:mm\Z', $builder->getOrderCreateDate());
            }

            // shipping address
            $shippingAddress = $builder->getShippingAddress();
            if (!empty($shippingAddress)) {
                $request['order']['shipping_address']['line1'] = $shippingAddress->streetAddress1;
                $request['order']['shipping_address']['line2'] = $shippingAddress->streetAddress2;
                $request['order']['shipping_address']['line3'] = $shippingAddress->streetAddress3;
                $request['order']['shipping_address']['city'] = $shippingAddress->city;
                $request['order']['shipping_address']['post_code'] = $shippingAddress->postalCode;
                $request['order']['shipping_address']['state'] = $shippingAddress->state;
                $request['order']['shipping_address']['country'] = $shippingAddress->country;
            }

            // payer
            $request['payer']['email'] = $builder->getCustomerEmail();

            // billing details
            $billingAddress = $builder->getBillingAddress();
            if (!empty($billingAddress)) {
                $request['payer']['billing_address']['line1'] = $billingAddress->streetAddress1;
                $request['payer']['billing_address']['line2'] = $billingAddress->streetAddress2;
                $request['payer']['billing_address']['line3'] = $billingAddress->streetAddress3;
                $request['payer']['billing_address']['city'] = $billingAddress->city;
                $request['payer']['billing_address']['post_code'] = $billingAddress->postalCode;
                $request['payer']['billing_address']['state'] = $billingAddress->state;
                $request['payer']['billing_address']['country'] = $billingAddress->country;
            }

            // mobile phone
            if (!empty($builder->getMobileNumber)) {
                $request['payer']['mobile_phone']['country_code'] = $builder->getMobileCountryCode();
                $request['payer']['mobile_phone']['subscriber_number'] = $builder->getMobileNumber();
            }

            // browser_data
            $browserData = $builder->getBrowserData();
            if (!empty($browserData)) {
                $request['browser_data']['accept_header'] = $browserData->acceptHeader;
                $request['browser_data']['color_depth'] = $browserData->colorDepth;
                $request['browser_data']['ip'] = $browserData->ipAddress;
                $request['browser_data']['java_enabled'] = $browserData->javaEnabled;
                $request['browser_data']['javascript_enabled'] = $browserData->javaScriptEnabled;
                $request['browser_data']['language'] = $browserData->language;
                $request['browser_data']['screen_height'] = $browserData->screenHeight;
                $request['browser_data']['screen_width'] = $browserData->screenWidth;
                $request['browser_data']['challenge_window_size'] = $browserData->challengWindowSize;
                $request['browser_data']['timezone'] = $browserData->timeZone;
                $request['browser_data']['user_agent'] = $browserData->userAgent;
            }

            $rawResponse = $this->doTransaction('POST', 'authentications', json_encode($request));
            return $this->mapResponse($rawResponse);
        }

        throw new ApiException(sprintf('Unknown transaction type %s.', $transType));
    }

    /** @return Transaction */
    private function mapResponse(string $rawResponse) {
        $doc = json_decode($rawResponse, true);

        $secureEcom = new ThreeDSecure();

        // check enrolled
        $secureEcom->serverTransactionId = $doc['server_trans_id'];
        if (array_key_exists('enrolled', $doc)) {
            $secureEcom->enrolled = (bool)$doc['enrolled'];
        }
        $secureEcom->issuerAcsUrl = $doc['method_url'] . $doc['challenge_request_url'];

        // get authentication data
        $secureEcom->acsTransactionId = $doc['acs_trans_id'];
        $secureEcom->directoryServerTransactionId = $doc['ds_trans_id'];
        $secureEcom->authenticationType = $doc['authentication_type'];
        $secureEcom->authenticationValue = $doc['authentication_value'];
        $secureEcom->eci = $doc['eci'];
        $secureEcom->status = $doc['status'];
        $secureEcom->statusReason = $doc['status_reason'];
        $secureEcom->authenticationSource = $doc['authentication_source'];
        $secureEcom->messageCategory = $doc['message_category'];
        $secureEcom->messageVersion = $doc['message_version'];

        // challenge mandated
        if (array_key_exists('challenge_mandated', $doc)) {
            $secureEcom->challengeMandated = (bool)$doc['challenge_mandated'];
        }

        // initiate authentication
        $secureEcom->cardHolderResponseInfo = $doc['cardHolder_response_info'];

        // device_render_options
        if (array_key_exists('device_render_options', $doc)) {
            $renderOptions = $doc['device_render_options'];
            $secureEcom->sdkInterface = $renderOptions['sdk_interface'];
            $secureEcom->sdkUiType = $renderOptions['sdk_ui_type'];
        }

        // message_extension
        if (array_key_exists('message_extension', $doc)) {
            $secureEcom->criticalityIndicator = $doc['message_extension']['criticality_indicator'];
            $secureEcom->messageExtensionId = $doc['message_extension']['id'];
            $secureEcom->messageExtensionName = $doc['message_extension']['name'];
        }

        // versions
        $secureEcom->directoryServerEndVersion = $doc['ds_protocol_version_end'];
        $secureEcom->directoryServerStartVersion = $doc['ds_protocol_version_start'];
        $secureEcom->acsEndVersion = $doc['acs_protocol_version_end'];
        $secureEcom->acsStartVersion = $doc['acs_protocol_version_start'];

        // payer authentication request
        if (array_key_exists('method_data', $doc)) {
            $methodData = $doc['method_data'];
            $secureEcom->payerAuthenticationRequest = $methodData['encoded_method_data'];
        } else if (array_key_exists('encoded_creq', $doc)) {
            $secureEcom->payerAuthenticationRequest = $doc['encoded_creq'];
        }

        $response = new Transaction();
        $response->threeDSecure = $secureEcom;
        return $response;
    }

    private function mapCardScheme(string $cardType) {
        if ($cardType == "MC") {
            return "MASTERCARD";
        } else if ($cardType == "DINERSCLUB") {
            return "DINERS";
        } else {
            return $cardType;
        }
    }

    /** 
     * @throws GatewayException
     * @return string */
    private function handleResponse(GatewayResponse $response) {
        if ($response->statusCode != 200 && $response->statusCode != 204) {
            $parsed = json_decode($response->rawResponse, true);
            if (array_key_exists('error', $parsed)) {
                $error = $parsed['error'];
                throw new GatewayException(sprintf("Status code: %s - %s", $response->statusCode, $error));
            }
            throw new GatewayException(sprintf("Status code: %s - %s", $response->statusCode, $error));
        }
        return $response->rawResponse;
    }
}