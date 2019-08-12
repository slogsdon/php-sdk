<?php

namespace GlobalPayments\Api\Builders;

use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\Api\Entities\Exceptions\BuilderException;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\Gateways\ISecure3dProvider;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\PaymentMethods\Interfaces\IPaymentMethod;
use GlobalPayments\Api\PaymentMethods\Interfaces\ISecure3d;
use GlobalPayments\Api\PaymentMethods\RecurringPaymentMethod;
use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Entities\BrowserData;
use GlobalPayments\Api\Entities\MerchantDataCollection;
use GlobalPayments\Api\Entities\ThreeDSecure;
use GlobalPayments\Api\Entities\Transaction;
use GlobalPayments\Api\Entities\Enums\AddressType;
use GlobalPayments\Api\Entities\Enums\AgeIndicator;
use GlobalPayments\Api\Entities\Enums\AuthenticationRequestType;
use GlobalPayments\Api\Entities\Enums\AuthenticationSource;
use GlobalPayments\Api\Entities\Enums\MessageCategory;
use GlobalPayments\Api\Entities\Enums\Secure3dVersion;
use GlobalPayments\Api\Entities\Enums\TransactionType;


class Secure3dBuilder extends BaseBuilder {
    /** @var AgeIndicator */
    protected $accountAgeIndicator;
    /** @var DateTime */
    protected $accountChangeDate;
    /** @var DateTime */
    protected $accountCreateDate;
    /** @var AgeIndicator */
    protected $accountChangeIndicator;
    /** @var bool */
    protected $addressMatchIndicator;
    /** @var string|float */
    protected $amount;
    /** @var string */
    protected $applicationId;
    /** @var AuthenticationSource */
    protected $authenticationSource;
    /** @var AuthenticationRequestType */
    protected $authenticationRequestType;
    /** @var Address */
    protected $billingAddress;
    /** @var BrowserData */
    protected $browserData;
    /** @var string */
    protected $currency;
    /** @var string */
    protected $customerAccountId;
    /** @var string */
    protected $customerAuthenticationData;
    /** @var CustomerAuthenticationMethod */
    protected $customerAuthenticationMethod;
    /** @var DateTime */
    protected $customerAuthenticationTimestamp;
    /** @var string */
    protected $customerEmail;
    /** @var string */
    protected $deliveryEmail;
    /** @var DeliveryTimeFrame */
    protected $deliveryTimeframe;
    /** @var string */
    protected $encodedData;
    /** @var string */
    protected $ephemeralPublicKey;
    /** @var int */
    protected $giftCardCount;
    /** @var string */
    protected $giftCardCurrency;
    /** @var decimal */
    protected $giftCardAmount;
    /** @var string */
    protected $homeCountryCode;
    /** @var string */
    protected $homeNumber;
    /** @var int */
    protected $maxNumberOfInstallments;
    /** @var int */
    protected $maximumTimeout;
    /** @var MerchantDataCollection */
    protected $merchantData;
    /** @var MessageCategory */
    protected $messageCategory;
    /** @var AuthenticationRequestType */
    protected $merchantInitiatedRequestType;
    /** @var MessageVersion */
    protected $messageVersion;
    /** @var MethodUrlCompletion */
    protected $methodUrlCompletion;
    /** @var string */
    protected $mobileCountryCode;
    /** @var string */
    protected $mobileNumber;
    /** @var int */
    protected $numberOfAddCardAttemptsInLast24Hours;
    /** @var int */
    protected $numberOfPurchasesInLastSixMonths;
    /** @var int */
    protected $numberOfTransactionsInLast24Hours;
    /** @var int */
    protected $numberOfTransactionsInLastYear;
    /** @var DateTime */
    protected $orderCreateDate;
    /** @var string */
    protected $orderId;
    /** @var OrderTransactionType */
    protected $orderTransactionType;
    /** @var DateTime */
    protected $passwordChangeDate;
    /** @var AgeIndicator */
    protected $passwordChangeIndicator;
    /** @var DateTime */
    protected $paymentAccountCreateDate;
    /** @var AgeIndicator */
    protected $paymentAgeIndicator;
    /** @var string */
    protected $payerAuthenticationResponse;
    /** @var IPaymentMethod */
    protected $paymentMethod;
    /** @var DateTime */
    protected $preOrderAvailabilityDate;
    /** @var PreOrderIndicator */
    protected $preOrderIndicator;
    /** @var bool */
    protected $previousSuspiciousActivity;
    /** @var string */
    protected $priorAuthenticationData;
    /** @var PriorAuthenticationMethod */
    protected $priorAuthenticationMethod;
    /** @var string */
    protected $priorAuthenticationTransactionId;
    /** @var DateTime */
    protected $priorAuthenticationTimestamp;
    /** @var DateTime */
    protected $recurringAuthorizationExpiryDate;
    /** @var int */
    protected $recurringAuthorizationFrequency;
    /** @var string */
    protected $referenceNumber;
    /** @var ReorderIndicator */
    protected $reorderIndicator;
    /** @var SdkInterface */
    protected $sdkInterface;
    /** @var string */
    protected $sdkTransactionId;
    /** @var array<SdkUiType> */
    protected $sdkUiTypes;
    /** @var Address */
    protected $shippingAddress;
    /** @var DateTime */
    protected $shippingAddressCreateDate;
    /** @var AgeIndicator */
    protected $shippingAddressUsageIndicator;
    /** @var ShippingMethod */
    protected $shippingMethod;
    /** @var bool */
    protected $shippingNameMatchesCardHolderName;
    /** @var ThreeDSecure */
    protected $threeDSecure;
    /** @var TransactionType */
    protected $transactionType;
    /** @var string */
    protected $workCountryCode;
    /** @var string */
    protected $workNumber;

    public function __construct($transactionType) {
        parent::__construct();
        $this->authenticationSource = AuthenticationSource::BROWSER;
        $this->authenticationRequestType = AuthenticationRequestType::PAYMENT_TRANSACTION;
        $this->messageCategory = MessageCategory::PAYMENT_AUTHENTICATION;
        $this->transactionType = $transactionType;
    }

    /** @return AgeIndicator */
    public function getAccountAgeIndicator() {
        return $this->accountAgeIndicator;
    }
    /** @return DateTime */
    public function getAccountChangeDate() {
        return $this->accountChangeDate;
    }
    /** @return DateTime */
    public function getAccountCreateDate() {
        return $this->accountCreateDate;
    }
    /** @return AgeIndicator */
    public function getAccountChangeIndicator() {
        return $this->accountChangeIndicator;
    }
    /** @return bool */
    public function isAddressMatchIndicator() {
        return $this->addressMatchIndicator;
    }
    /** @return string|float */
    public function getAmount() {
        return $this->amount;
    }
    /** @return string */
    public function getApplicationId() {
        return $this->applicationId;
    }
    /** @return AuthenticationSource */
    public function getAuthenticationSource() {
        return $this->authenticationSource;
    }
    /** @return AuthenticationRequestType */
    public function getAuthenticationRequestType() {
        return $this->authenticationRequestType;
    }
    /** @return address */
    public function getBillingAddress() {
        return $this->billingAddress;
    }
    /** @return BrowserData */
    public function getBrowserData() {
        return $this->browserData;
    }
    /** @return string */
    public function getCurrency() {
        return $this->currency;
    }
    /** @return string */
    public function getCustomerAccountId() {
        return $this->customerAccountId;
    }
    /** @return string */
    public function getCustomerAuthenticationData() {
        return $this->customerAuthenticationData;
    }
    /** @return CustomerAuthenticationMethod */
    public function getCustomerAuthenticationMethod() {
        return $this->customerAuthenticationMethod;
    }
    /** @return DateTime */
    public function getCustomerAuthenticationTimestamp() {
        return $this->customerAuthenticationTimestamp;
    }
    /** @return string */
    public function getCustomerEmail() {
        return $this->customerEmail;
    }
    /** @return string */
    public function getDeliveryEmail() {
        return $this->deliveryEmail;
    }
    /** @return DeliveryTimeFrame */
    public function getDeliveryTimeframe() {
        return $this->deliveryTimeframe;
    }
    /** @return string */
    public function getEncodedData() {
        return $this->encodedData;
    }
    /** @return string */
    public function getEphemeralPublicKey() {
        return $this->ephemeralPublicKey;
    }
    /** @return int */
    public function getGiftCardCount() {
        return $this->giftCardCount;
    }
    /** @return string */
    public function getGiftCardCurrency() {
        return $this->giftCardCurrency;
    }
    /** @return decimal */
    public function getGiftCardAmount() {
        return $this->giftCardAmount;
    }
    /** @return string */
    public function getHomeCountryCode() {
        return $this->homeCountryCode;
    }
    /** @return string */
    public function getHomeNumber() {
        return $this->homeNumber;
    }
    /** @return int */
    public function getMaxNumberOfInstallments() {
        return $this->maxNumberOfInstallments;
    }
    /** @return int */
    public function getMaximumTimeout() {
        return $this->maximumTimeout;
    }
    /** @return MerchantDataCollection */
    public function getMerchantData() {
        return $this->merchantData;
    }
    /** @return MessageCategory */
    public function getMessageCategory() {
        return $this->messageCategory;
    }
    /** @return AuthenticationRequestType */
    public function getMerchantInitiatedRequestType() {
        return $this->merchantInitiatedRequestType;
    }
    /** @return MessageVersion */
    public function getMessageVersion() {
        return $this->messageVersion;
    }
    /** @return MethodUrlCompletion */
    public function getMethodUrlCompletion() {
        return $this->methodUrlCompletion;
    }
    /** @return string */
    public function getMobileCountryCode() {
        return $this->mobileCountryCode;
    }
    /** @return string */
    public function getMobileNumber() {
        return $this->mobileNumber;
    }
    /** @return int */
    public function getNumberOfAddCardAttemptsInLast24Hours() {
        return $this->numberOfAddCardAttemptsInLast24Hours;
    }
    /** @return int */
    public function getNumberOfPurchasesInLastSixMonths() {
        return $this->numberOfPurchasesInLastSixMonths;
    }
    /** @return int */
    public function getNumberOfTransactionsInLast24Hours() {
        return $this->numberOfTransactionsInLast24Hours;
    }
    /** @return int */
    public function getNumberOfTransactionsInLastYear() {
        return $this->numberOfTransactionsInLastYear;
    }
    /** @return DateTime */
    public function getOrderCreateDate() {
        return $this->orderCreateDate;
    }
    /** @return string */
    public function getOrderId() {
        return $this->orderId;
    }
    /** @return OrderTransactionType */
    public function getOrderTransactionType() {
        return $this->orderTransactionType;
    }
    /** @return DateTime */
    public function getPasswordChangeDate() {
        return $this->passwordChangeDate;
    }
    /** @return AgeIndicator */
    public function getPasswordChangeIndicator() {
        return $this->passwordChangeIndicator;
    }
    /** @return DateTime */
    public function getPaymentAccountCreateDate() {
        return $this->paymentAccountCreateDate;
    }
    /** @return AgeIndicator */
    public function getPaymentAgeIndicator() {
        return $this->paymentAgeIndicator;
    }
    /** @return string */
    public function getPayerAuthenticationResponse() {
        return $this->payerAuthenticationResponse;
    }
    /** @return IPaymentMethod */
    public function getPaymentMethod() {
        return $this->paymentMethod;
    }
    /** @return DateTime */
    public function getPreOrderAvailabilityDate() {
        return $this->preOrderAvailabilityDate;
    }
    /** @return PreOrderIndicator */
    public function getPreOrderIndicator() {
        return $this->preOrderIndicator;
    }
    /** @return bool */
    public function getPreviousSuspiciousActivity() {
        return $this->previousSuspiciousActivity;
    }
    /** @return string */
    public function getPriorAuthenticationData() {
        return $this->priorAuthenticationData;
    }
    /** @return PriorAuthenticationMethod */
    public function getPriorAuthenticationMethod() {
        return $this->priorAuthenticationMethod;
    }
    /** @return string */
    public function getPriorAuthenticationTransactionId() {
        return $this->priorAuthenticationTransactionId;
    }
    /** @return DateTime */
    public function getPriorAuthenticationTimestamp() {
        return $this->priorAuthenticationTimestamp;
    }
    /** @return DateTime */
    public function getRecurringAuthorizationExpiryDate() {
        return $this->recurringAuthorizationExpiryDate;
    }
    /** @return int */
    public function getRecurringAuthorizationFrequency() {
        return $this->recurringAuthorizationFrequency;
    }
    /** @return string */
    public function getReferenceNumber() {
        return $this->referenceNumber;
    }
    /** @return ReorderIndicator */
    public function getReorderIndicator() {
        return $this->reorderIndicator;
    }
    /** @return SdkInterface */
    public function getSdkInterface() {
        return $this->sdkInterface;
    }
    /** @return string */
    public function getSdkTransactionId() {
        return $this->sdkTransactionId;
    }
    /** @return array<SdkUiType> */
    public function getSdkUiTypes() {
        return $this->sdkUiTypes;
    }
    /** @return string */
    public function getServerTransactionId() {
        if (!empty($this->threeDSecure)) {
            return $this->threeDSecure->serverTransactionId;
        }
        return null;
    }
    /** @return Address */
    public function getShippingAddress() {
        return $this->shippingAddress;
    }
    /** @return DateTime */
    public function getShippingAddressCreateDate() {
        return $this->shippingAddressCreateDate;
    }
    /** @return AgeIndicator */
    public function getShippingAddressUsageIndicator() {
        return $this->shippingAddressUsageIndicator;
    }
    /** @return ShippingMethod */
    public function getShippingMethod() {
        return $this->shippingMethod;
    }
    /** @return bool */
    public function getShippingNameMatchesCardHolderName() {
        return $this->shippingNameMatchesCardHolderName;
    }
    /** @return ThreeDSecure */
    public function getThreeDSecure() {
        return $this->threeDSecure;
    }
    /** @return TransactionType */
    public function getTransactionType() {
        return $this->transactionType;
    }
    /** @return string */
    public function getWorkCountryCode() {
        return $this->workCountryCode;
    }
    /** @return string */
    public function getWorkNumber() {
        return $this->workNumber;
    }

    // HELPER METHOD FOR THE CONNECTOR
    /** @return bool */
    public function hasMobileFields() {
        return(
            !empty($this->applicationId) ||
            $this->ephemeralPublicKey != null ||
            $this->maximumTimeout != null ||
            $this->referenceNumber != null ||
            !empty($this->sdkTransactionId) ||
            !empty($this->encodedData) ||
            $this->sdkInterface != null ||
            $this->sdkUiTypes != null
        );
    }
    /** @return bool */
    public function hasPriorAuthenticationData() {
        return (
            $this->priorAuthenticationMethod != null ||
            !empty($this->priorAuthenticationTransactionId) ||
            $this->priorAuthenticationTimestamp != null ||
            !empty($this->priorAuthenticationData)
        );
    }
    /** @return bool */
    public function hasRecurringAuthData() {
        return (
            $this->maxNumberOfInstallments != null ||
            $this->recurringAuthorizationFrequency != null ||
            $this->recurringAuthorizationExpiryDate != null
        );
    }
    /** @return bool */
    public function hasPayerLoginData() {
        return (
            !empty($this->customerAuthenticationData) ||
            $this->customerAuthenticationTimestamp != null ||
            $this->customerAuthenticationMethod != null
        );
    }

    /** @return Secure3dBuilder */
    public function withAddress(Address $address, $type = AddressType::BILLING) {
        if ($type === AddressType::BILLING) {
            $this->billingAddress = $address;
        } else {
            $this->shippingAddress = $address;
        }
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withAccountAgeIndicator($ageIndicator) {
        $this->accountAgeIndicator = $ageIndicator;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withAccountChangeDate($accountChangeDate) {
        $this->accountChangeDate = $accountChangeDate;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withAccountCreateDate($accountCreateDate) {
        $this->accountCreateDate = $accountCreateDate;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withAccountChangeIndicator($accountChangeIndicator) {
        $this->accountChangeIndicator = $accountChangeIndicator;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withAddressMatchIndicator(bool $value) {
        $this->addressMatchIndicator = $value;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withAmount($value) {
        $this->amount = $value;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withApplicationId($applicationId) {
        $this->applicationId = $applicationId;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withAuthenticationSource($value) {
        $this->authenticationSource = $value;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withAuthenticationRequestType($value) {
        $this->authenticationRequestType = $value;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withBrowserData($value) {
        $this->browserData = $value;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withCustomerAccountId($customerAccountId) {
        $this->customerAccountId = $customerAccountId;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withCustomerAuthenticationData($customerAuthenticationData) {
        $this->customerAuthenticationData = $customerAuthenticationData;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withCustomerAuthenticationMethod($customerAuthenticationMethod) {
        $this->customerAuthenticationMethod = $customerAuthenticationMethod;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withCustomerAuthenticationTimestamp($customerAuthenticationTimestamp) {
        $this->customerAuthenticationTimestamp = $customerAuthenticationTimestamp;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withCurrency($value) {
        $this->currency = $value;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withCustomerEmail($value) {
        $this->customerEmail = $value;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withDeliveryEmail($deliveryEmail) {
        $this->deliveryEmail = $deliveryEmail;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withDeliveryTimeFrame($deliveryTimeframe) {
        $this->deliveryTimeframe = $deliveryTimeframe;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withEncodedData($encodedData) {
        $this->encodedData = $encodedData;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withEphemeralPublicKey($ephemeralPublicKey) {
        $this->ephemeralPublicKey = $ephemeralPublicKey;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withGiftCardCount($giftCardCount) {
        $this->giftCardCount = $giftCardCount;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withGiftCardCurrency($giftCardCurrency) {
        $this->giftCardCurrency = $giftCardCurrency;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withGiftCardAmount($giftCardAmount) {
        $this->giftCardAmount = $giftCardAmount;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withHomeNumber($countryCode, $number) {
        $this->homeCountryCode = $countryCode;
        $this->homeNumber = $number;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withMaxNumberOfInstallments($maxNumberOfInstallments) {
        $this->maxNumberOfInstallments = $maxNumberOfInstallments;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withMaximumTimeout($maximumTimeout) {
        $this->maximumTimeout = $maximumTimeout;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withMerchantData(MerchantDataCollection $value) {
        $this->merchantData = $value;
        if (!empty($this->merchantData)) {
            if (empty($this->threeDSecure)) {
                $this->threeDSecure = new ThreeDSecure();
            }
            $this->threeDSecure->setMerchantData($value);
        }
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withMessageCategory($value) {
        $this->messageCategory = $value;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withMerchantInitiatedRequestType($merchantInitiatedRequestType) {
        $this->merchantInitiatedRequestType = $merchantInitiatedRequestType;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withMessageVersion($value) {
        $this->messageVersion = $value;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withMethodUrlCompletion($value) {
        $this->methodUrlCompletion = $value;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withMobileNumber($countryCode, $number) {
        $this->mobileCountryCode = $countryCode;
        $this->mobileNumber = $number;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withNumberOfAddCardAttemptsInLast24Hours($numberOfAddCardAttemptsInLast24Hours) {
        $this->numberOfAddCardAttemptsInLast24Hours = $numberOfAddCardAttemptsInLast24Hours;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withNumberOfPurchasesInLastSixMonths($numberOfPurchasesInLastSixMonths) {
        $this->numberOfPurchasesInLastSixMonths = $numberOfPurchasesInLastSixMonths;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withNumberOfTransactionsInLast24Hours($numberOfTransactionsInLast24Hours) {
        $this->numberOfTransactionsInLast24Hours = $numberOfTransactionsInLast24Hours;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withNumberOfTransactionsInLastYear($numberOfTransactionsInLastYear) {
        $this->numberOfTransactionsInLastYear = $numberOfTransactionsInLastYear;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withOrderCreateDate($value) {
        $this->orderCreateDate = $value;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withOrderId($value) {
        $this->orderId = $value;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withOrderTransactionType($orderTransactionType) {
        $this->orderTransactionType = $orderTransactionType;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withPasswordChangeDate($passwordChangeDate) {
        $this->passwordChangeDate = $passwordChangeDate;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withPasswordChangeIndicator($passwordChangeIndicator) {
        $this->passwordChangeIndicator = $passwordChangeIndicator;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withPaymentAccountCreateDate($paymentAccountCreateDate) {
        $this->paymentAccountCreateDate = $paymentAccountCreateDate;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withPaymentAccountAgeIndicator($paymentAgeIndicator) {
        $this->paymentAgeIndicator = $paymentAgeIndicator;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withPayerAuthenticationResponse($value) {
        $this->payerAuthenticationResponse = $value;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withPaymentMethod($value) {
        $this->paymentMethod = $value;
        if ($this->paymentMethod instanceof ISecure3d) {
            $secureEcom = $this->paymentMethod->threeDSecure;
            if (!empty($secureEcom)) {
                $this->threeDSecure = $secureEcom;
            }
        }
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withPreOrderAvailabilityDate($preOrderAvailabilityDate) {
        $this->preOrderAvailabilityDate = $preOrderAvailabilityDate;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withPreOrderIndicator($preOrderIndicator) {
        $this->preOrderIndicator = $preOrderIndicator;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withPreviousSuspiciousActivity(bool $previousSuspiciousActivity) {
        $this->previousSuspiciousActivity = $previousSuspiciousActivity;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withPriorAuthenticationData($priorAuthenticationData) {
        $this->priorAuthenticationData = $priorAuthenticationData;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withPriorAuthenticationMethod($priorAuthenticationMethod) {
        $this->priorAuthenticationMethod = $priorAuthenticationMethod;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withPriorAuthenticationTransactionId($priorAuthencitationTransactionId) {
        $this->priorAuthenticationTransactionId = $priorAuthencitationTransactionId;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withPriorAuthenticationTimestamp($priorAuthenticationTimestamp) {
        $this->priorAuthenticationTimestamp = $priorAuthenticationTimestamp;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withRecurringAuthorizationExpiryDate($recurringAuthorizationExpiryDate) {
        $this->recurringAuthorizationExpiryDate = $recurringAuthorizationExpiryDate;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withRecurringAuthorizationFrequency($recurringAuthorizationFrequency) {
        $this->recurringAuthorizationFrequency = $recurringAuthorizationFrequency;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withReferenceNumber($referenceNumber) {
        $this->referenceNumber = $referenceNumber;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withReorderIndicator($reorderIndicator) {
        $this->reorderIndicator = $reorderIndicator;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withSdkInterface($sdkInterface) {
        $this->sdkInterface = $sdkInterface;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withSdkTransactionId($sdkTransactionId) {
        $this->sdkTransactionId = $sdkTransactionId;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withSdkUiTypes($sdkUiTypes) {
        $this->sdkUiTypes = $sdkUiTypes;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withServerTransactionId($value) {
        if (empty($this->threeDSecure)) {
            $this->threeDSecure = new ThreeDSecure();
        }
        $this->threeDSecure->serverTransactionId = $value;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withShippingAddressCreateDate($shippingAddressCreateDate) {
        $this->shippingAddressCreateDate = $shippingAddressCreateDate;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withShippingAddressUsageIndicator($shippingAddressUsageIndicator) {
        $this->shippingAddressUsageIndicator = $shippingAddressUsageIndicator;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withShippingMethod($shippingMethod) {
        $this->shippingMethod = $shippingMethod;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withShippingNameMatchesCardHolderName($shippingNameMatchesCardHolderName) {
        $this->shippingNameMatchesCardHolderName = $shippingNameMatchesCardHolderName;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withThreeDSecure(ThreeDSecure $threeDSecure) {
        $this->threeDSecure = $threeDSecure;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withTransactionType($transactionType) {
        $this->transactionType = $transactionType;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withWorkNumber($countryCode, $number) {
        $this->workCountryCode = $countryCode;
        $this->workNumber = $number;
        return $this;
    }

    /** 
     * @throws ApiException
     * @return ThreeDSecure */
    public function execute($version = Secure3dVersion::ANY) {
        // TODO Get validations working
        // parent::execute();

        // setup return object
        $rvalue = $this->threeDSecure;
        if (empty($rvalue)) {
            $rvalue = new ThreeDSecure();
            $rvalue->setVersion($version);
        }

        // working version
        if ($rvalue->getVersion() != null) {
            $version = $rvalue->getVersion();
        }

        // get the provider
        $provider = ServicesContainer::instance()->getSecure3d($version);
        if (!empty($provider)) {
            $canDowngrade = false;
            if ($provider->getVersion() === Secure3dVersion::TWO && $version === Secure3dVersion::ANY) {
                try {
                    $oneProvider = ServicesContainer::instance()->getSecure3d(Secure3dVersion::ONE);
                    $canDowngrade = ($oneProvider != null);
                } catch (ConfigurationException $exc) {
                    // NOT CONFIGURED
                }
            }

            // process the request, capture any exceptions which might have been thrown
            $response = null;
            try {
                $response = $provider->processSecure3d($this);
                if (empty($response) && $canDowngrade) {
                    return $this->execute(Secure3dVersion::ONE);
                }
            } catch (GatewayException $exc) {
                // check for not enrolled
                if ($exc->responseCode != null) {
                    if ($exc->responseCode == '110' && $provider->getVersion() === Secure3dVersion::ONE) {
                        return $rvalue;
                    }
                } else if ($canDowngrade && $this->transactionType === TransactionType::VERIFY_ENROLLED) { // check if we can downgrade
                    return $this->execute(Secure3dVersion::ONE);
                } else { // throw exception
                    throw $exc;
                }
            }

            // check the response
            if (!empty($response)) {
                switch ($this->transactionType) {
                    case TransactionType::VERIFY_ENROLLED: {
                        if (!empty($response->threeDSecure)) {
                            $rvalue = $response->threeDSecure;
                            if ($rvalue->enrolled) {
                                $rvalue->setAmount($this->amount);
                                $rvalue->setCurrency($this->currency);
                                $rvalue->setOrderId($response->orderId);
                                $rvalue->setVersion($provider->getVersion());
                            } else if ($canDowngrade) {
                                return $this->execute(Secure3dVersion::ONE);
                            }
                        } else if ($canDowngrade) {
                            return $this->execute(Secure3dVersion::ONE);
                        }
                    } break;
                    case TransactionType::INITIATE_AUTHENTICATION:
                    case TransactionType::VERIFY_SIGNATURE: {
                        $rvalue->merge($response->threeDSecure);
                    } break;
                }
            }
        }
        return $rvalue;
    }

    /** @return void */
    public function setupValidations() {
        $this->validations->of(TransactionType::VERIFY_ENROLLED)
            ->check('paymentMethod')->isNotNull();

        $this->validations->of(TransactionType::VERIFY_ENROLLED)
            ->when('paymentMethod')->isNotNull()
            ->check('paymentMethod')->isInstanceOf(ISecure3d::class);

        $this->validations->of(TransactionType::VERIFY_SIGNATURE)
            ->when('version')->isEqualTo(Secure3dVersion::ONE)
            ->check('threeDSecure')->isNotNull()
            ->when('version')->isEqualTo(Secure3dVersion::ONE)
            ->check('payerAuthenticationResponse')->isNotNull();

        $this->validations->of(TransactionType::VERIFY_SIGNATURE)
            ->when('version')->isEqualTo(Secure3dVersion::TWO)
            ->check('serverTransactionId')->isNotNull();

        $this->validations->of(TransactionType::INITIATE_AUTHENTICATION)
            ->check('threeDSecure')->isNotNull();

        $this->validations->of(TransactionType::INITIATE_AUTHENTICATION)
            ->when('paymentMethod')->isNotNull()
            ->check('paymentMethod')->isInstanceOf(ISecure3d::class);

        $this->validations->of(TransactionType::INITIATE_AUTHENTICATION)
            ->when('merchantInitiatedRequestType')->isNotNull()
            ->check('merchantInitiatedRequestType')->isNotEqualTo(AuthenticationRequestType::PAYMENT_TRANSACTION );

        $this->validations->of(TransactionType::INITIATE_AUTHENTICATION)
            ->when('accountAgeIndicator')->isNotNull()
            ->check('accountAgeIndicator')->isNotEqualTo(AgeIndicator::NO_CHANGE);

        $this->validations->of(TransactionType::INITIATE_AUTHENTICATION)
            ->when('passwordChangeIndicator')->isNotNull()
            ->check('passwordChangeIndicator')->isNotEqualTo(AgeIndicator::NO_ACCOUNT);

        $this->validations->of(TransactionType::INITIATE_AUTHENTICATION)
            ->when('shippingAddressUsageIndicator')->isNotNull()
            ->check('shippingAddressUsageIndicator')->isNotEqualTo(AgeIndicator::NO_CHANGE)
            ->when('shippingAddressUsageIndicator')->isNotNull()
            ->check('shippingAddressUsageIndicator')->isNotEqualTo(AgeIndicator::NO_ACCOUNT);
    }
}