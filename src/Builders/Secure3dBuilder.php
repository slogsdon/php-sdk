<?php

namespace GlobalPayments\Api\Builders;

use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\Gateways\ISecure3dProvider;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\PaymentMethods\IPaymentMethod;
use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Entities\BrowserData;
use GlobalPayments\Api\Entities\MerchantDataCollection;
use GlobalPayments\Api\Entities\ThreeDSecure;
use GlobalPayments\Api\Entities\Transaction;
use GlobalPayments\Api\Entities\Enums\AddressType;
use GlobalPayments\Api\Entities\Enums\AuthenticationRequestType;
use GlobalPayments\Api\Entities\Enums\AuthenticationSource;
use GlobalPayments\Api\Entities\Enums\MessageCategory;
use GlobalPayments\Api\Entities\Enums\Secure3dVersion;
use GlobalPayments\Api\Entities\Enums\TransactionType;


class Secure3dBuilder extends BaseBuilder {
    /** @var bool */
    private $addressMatchIndicator;
    /** @var string|float */
    private $amount;
    /** @var AuthenticationSource */
    private $authenticationSource;
    /** @var AuthenticationRequestType */
    private $authenticationRequestType;
    /** @var Address */
    private $billingAddress;
    /** @var BrowserData */
    private $browserData;
    /** @var string */
    private $currency;
    /** @var string */
    private $customerEmail;
    /** @var MerchantDataCollection */
    private $merchantData;
    /** @var MessageCategory */
    private $messageCategory;
    /** @var MessageVersion */
    private $messageVersion;
    /** @var MethodUrlCompletion */
    private $methodUrlCompletion;
    /** @var string */
    private $mobileCountryCode;
    /** @var string */
    private $mobileNumber;
    /** @var DateTime */
    private $orderCreateDate;
    /** @var string*/
    private $orderId;
    /** @var string */
    private $payerAuthenticationResponse;
    /** @var CreditCardData */
    private $paymentMethod;
    /** @var Address */
    private $shippingAddress;
    /** @var ThreeDSecure */
    private $threeDSecure;
    /** @var TransactionType */
    private $transactionType;

    public function __construct(TransactionType $transactionType) {
        $this->authenticationSource = AuthenticationSource::BROWSER;
        $this->authenticationRequestType = AuthenticationRequestType::PAYMENT_TRANSACTION;
        $this->messageCategory = MessageCategory::PAYMENT_AUTHENTICATION;
        $this->transactionType = $transactionType;
    }

    /** @return bool */
    public function isAddressMatchIndicator() {
        return $this->addressMatchIndicator;
    }

    /** @return string|float */
    public function getAmount() {
        return $this->amount;
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
    public function getCustomerEmail() {
        return $this->customerEmail;
    }

    /** @return MerchantDataCollection */
    public function getMerchantData() {
        return $this->merchantData;
    }

    /** @return MessageCategory */
    public function getMessageCategory() {
        return $this->messageCategory;
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

    /** @return DateTime */
    public function getOrderCreateDate() {
        return $this->orderCreateDate;
    }

    /** @return string */
    public function getOrderId() {
        return $this->orderId;
    }

    /** @return string */
    public function getPayerAuthenticationResponse() {
        return $this->payerAuthenticationResponse;
    }

    /** @return IPaymentMethod */
    public function getPaymentMethod() {
        return $this->paymentMethod;
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

    /** @return ThreeDSecure */
    public function getThreeDSecure() {
        return $this->threeDSecure;
    }

    /** @return TransactionType */
    public function getTransactionType() {
        return $this->transactionType;
    }

    /** @return Secure3dBuilder */
    public function withAddress(Address $address, AddressType $type = AddressType::BILLING) {
        if ($type === AddressType::BILLING) {
            $this->billingAddress = $address;
        } else {
            $this->shippingAddress = $address;
        }
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
    public function withAuthenticationSource(AuthenticationSource $value) {
        $this->authenticationSource = $value;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withAuthenticationRequestType(AuthenticationRequestType $value) {
        $this->authenticationRequestType = $value;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withBrowserData(BrowserData $value) {
        $this->browserData = $value;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withCurrency(string $value) {
        $this->currency = $value;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withCustomerEmail(string $value) {
        $this->customerEmail = $value;
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
    public function blank() {
        return $this->blank;
    }

    /** @return Secure3dBuilder */
    public function withMessageCategory(MessageCategory $value) {
        $this->messageCategory = $value;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withMessageVersion(MessageVersion $value) {
        $this->messageVersion = $value;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withMethodUrlCompletion(MethodUrlCompletion $value) {
        $this->methodUrlCompletion = $value;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withMobileNumber(string $countryCode, string $number) {
        $this->mobileCountryCode = $countryCode;
        $this->mobileNumber = $number;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withOrderCreateDate(DateTime $value) {
        $this->orderCreateDate = $value;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withOrderId(string $value) {
        $this->orderId = $value;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withPayerAuthenticationResponse(string $value) {
        $this->payerAuthenticationResponse = $value;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withPaymentMethod(CreditCardData $value) {
        $this->paymentMethod = $value;
        if (!empty($this->paymentMethod->threeDSecure)) {
            $this->threeDSecure = $this->paymentMethod->threeDSecure;
        }
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withServerTransactionId(string $value) {
        if (empty($this->threeDSecure)) {
            $this->threeDSecure = new ThreeDSecure();
        }
        $this->threeDSecure->serverTransactionId = $value;
        return $this;
    }

    /** 
     * @throws ApiException
     * @return ThreeDSecure */
    public function execute(Secure3dVersion $version = Secure3dVersion::ANY) {
        $this->validations->validate($this);

        // setup return object
        $rvalue = $this->threeDSecure;
        if (empty($rvalue)) {
            $rvalue = new ThreeDSecure();
            $rvalue->version = $version;
        }

        // working version
        if ($rvalue->version != null) {
            $version = $rvalue->version;
        }

        // get the provider
        $provider = ServicesContainer::instance()->getSecure3d($version);
        if (!empty($provider)) {
            $canDowngrade = false;
            
            if ($provider->getVersion() === Secure3dVersion::TWO && $version === Secure3dVersion::ANY) {
                try {
                    $oneProvider = ServicesContainer::instance()->getSecure3d(Secure3dVersion::ONE);
                    $canDowngrade = (!empty($oneProvider));
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
                    if ($exc->responseCode == "110" && $provider->getVersion() === Secure3dVersion::ONE) {
                        return $rvalue;
                    }
                } else if ($canDowngrade) { // check if we can downgrade
                    return $this->execute(Secure3dVersion::ONE);
                } else { // throw exception
                    throw $exc;
                }
            }

            // check the response
            if (!empty($response)) {
                switch ($this->transactionType) {
                    case VERIFY_ENROLLED: {
                        if (!empty($response->getThreeDSecure())) {
                            $rvalue = $response->getThreeDSecure();
                            if ($rvalue->enrolled) {
                                $rvalue->setAmount($this->amount);
                                $rvalue->setCurrency($this->currency);
                                $rvalue->setOrderId($response->getOrderId());
                                $rvalue->version = $provider->getVersion();
                            } else if ($canDowngrade && $transactionType === TransactionType::VERIFY_ENROLLED) {
                                return $this->execute(Secure3dVersion::ONE);
                            }
                        } else if ($canDowngrade) {
                            return $this->execute(Secure3dVersion::ONE);
                        }
                    } break;
                    case INITIATE_AUTHENTICATION:
                    case VERIFY_SIGNATURE: {
                        $rvalue->merge($response->getThreeDSecure());
                    } break;
                }
            }
        }

        return $rvalue;
    }

    /** @return void */
    public function setupValidations() {
        $this->validations->of(TransactionType::VERIFY_ENROLLED)
            ->check('paymentMethod').isNotNull();

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
    }
}