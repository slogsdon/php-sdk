<?php

namespace GlobalPayments\Api\Gateways;

use DOMDocument;
use DOMElement;
use GlobalPayments\Api\Builders\AuthorizationBuilder;
use GlobalPayments\Api\Builders\BaseBuilder;
use GlobalPayments\Api\Builders\ManagementBuilder;
use GlobalPayments\Api\Builders\ReportBuilder;
use GlobalPayments\Api\Builders\TransactionBuilder;
use GlobalPayments\Api\Entities\BatchSummary;
use GlobalPayments\Api\Entities\Transaction;
use GlobalPayments\Api\Entities\Enums\AccountType;
use GlobalPayments\Api\Entities\Enums\AliasAction;
use GlobalPayments\Api\Entities\Enums\CheckType;
use GlobalPayments\Api\Entities\Enums\EntryMethod;
use GlobalPayments\Api\Entities\Enums\PaymentMethodType;
use GlobalPayments\Api\Entities\Enums\TaxType;
use GlobalPayments\Api\Entities\Enums\TransactionModifier;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\Entities\Exceptions\UnsupportedTransactionException;
use GlobalPayments\Api\Entities\Reporting\CheckData;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\PaymentMethods\ECheck;
use GlobalPayments\Api\PaymentMethods\GiftCard;
use GlobalPayments\Api\PaymentMethods\Interfaces\IBalanceable;
use GlobalPayments\Api\PaymentMethods\Interfaces\ICardData;
use GlobalPayments\Api\PaymentMethods\Interfaces\IEncryptable;
use GlobalPayments\Api\PaymentMethods\Interfaces\IPaymentMethod;
use GlobalPayments\Api\PaymentMethods\Interfaces\IPinProtected;
use GlobalPayments\Api\PaymentMethods\Interfaces\ITokenizable;
use GlobalPayments\Api\PaymentMethods\Interfaces\ITrackData;
use GlobalPayments\Api\PaymentMethods\RecurringPaymentMethod;
use GlobalPayments\Api\PaymentMethods\TransactionReference;
use GlobalPayments\Api\Entities\Enums\ReportType;
use GlobalPayments\Api\Entities\Reporting\TransactionSummary;
use GlobalPayments\Api\Entities\Reporting\SearchCriteria;
use GlobalPayments\Api\Entities\Reporting\SearchCriteriaBuilder;
use GlobalPayments\Api\PaymentMethods\CreditTrackData;
use GlobalPayments\Api\Services\ReportingService;

class MerchantwareConnector extends XmlGateway implements IPaymentGateway
{
    /**
     * Portico's XML namespace
     *
     * @var string
     */
    const XML_NAMESPACE = 'http://schemas.merchantwarehouse.com/merchantware/v45/';
    public $merchantName;
    public $merchantSiteId;
    public $merchantKey;
    public $registerNumber;
    public $terminalId;

    public $supportsHostedPayments = false;

    public function processAuthorization($builder)
    {
        $xml = new DOMDocument();
        $paymentMethod = $builder->paymentMethod;

        $transaction = $xml->createElement($this->mapRequestType($builder));
        $transaction->setAttribute('xmlns', static::XML_NAMESPACE);

        // Credentials
        $credentials = $xml->createElement('Credentials');
        $credentials->appendChild($xml->createElement('MerchantName', $this->merchantName));
        $credentials->appendChild($xml->createElement('MerchantSiteId', $this->merchantSiteId));
        $credentials->appendChild($xml->createElement('MerchantKey', $this->merchantKey));
        
        $transaction->appendChild($credentials);

        // Payment Data
        $paymentData = $xml->createElement('PaymentData');

        if ($paymentMethod instanceof CreditCardData) {
            $card = $paymentMethod;
            if ($card->token != null) {
                if ($card->mobileType != null) {
                    $paymentData->appendChild($xml->createElement('Source', 'Wallet'));
                    $paymentData->appendChild($xml->createElement('WalletId', $this->mapWalletId($card->mobileType)));
                    $paymentData->appendChild($xml->createElement('EncryptedPaymentData', $card->token));
                } else {
                    $paymentData->appendChild($xml->createElement('Source', 'Vault'));
                    $paymentData->appendChild($xml->createElement('VaultToken', $card->token));
                }
            } else {
                $paymentData->appendChild($xml->createElement('Source', 'Keyed'));
                $paymentData->appendChild($xml->createElement('CardNumber', $card->number));
                $paymentData->appendChild($xml->createElement('ExpirationDate', $card->getShortExpiry()));
                $paymentData->appendChild($xml->createElement('CardHolder', $card->cardHolderName));
                $paymentData->appendChild($xml->createElement('CardVerificationValue', $card->cvn));
            }
        } elseif ($paymentMethod instanceof CreditTrackData) {
            $paymentData->appendChild($xml->createElement('Source', 'READER'));

            $track = $paymentMethod;
            $paymentData->appendChild($xml->createElement('TrackData', $track->value));
        }

        // AVS
        if (!empty($builder->billingAddress)) {
            $paymentData->appendChild($xml->createElement('AvsStreetAddress', $builder->billingAddress->streetAddress1));
            $paymentData->appendChild($xml->createElement('AvsZipCode', $builder->billingAddress->postalCode));
        }

        $transaction->appendChild($paymentData);

        // Request
        $request = $xml->createElement('Request');
        $request->appendChild($xml->createElement('Amount', $builder->amount));
        $request->appendChild($xml->createElement('CashbackAmount', $builder->cashBackAmount));
        $request->appendChild($xml->createElement('SurchargeAmount', $builder->convenienceAmount));
        $request->appendChild($xml->createElement('AuthorizationCode', $builder->offlineAuthCode));

        if ($builder->autoSubstantiation != null) {
            $healthcare = $xml->createElement('HealthCareAmountDetails');

            $auto = $builder->autoSubstantiation;
            $healthcare->appendChild($xml->createElement('CopayAmount', $auto->getCopaySubTotal()));
            $healthcare->appendChild($xml->createElement('ClinicalAmount', $auto->getClinicSubTotal()));
            $healthcare->appendChild($xml->createElement('DentalAmount', $auto->getDentalSubTotal()));
            $healthcare->appendChild($xml->createElement('HealthCareTotalAmount', $auto->getTotalHealthcareAmount()));
            $healthcare->appendChild($xml->createElement('PrescriptionAmount', $auto->getPrescriptionSubTotal()));
            $healthcare->appendChild($xml->createElement('VisionAmount', $auto->getVisionSubTotal()));
            
            $request->appendChild($healthcare);
        }

        $request->appendChild($xml->createElement('InvoiceNumber', $builder->invoiceNumber));
        $request->appendChild($xml->createElement('RegisterNumber', $this->registerNumber));
        $request->appendChild($xml->createElement('MerchantTransactionId', $builder->clientTransactionId));
        $request->appendChild($xml->createElement('CardAcceptorTerminalId', $this->terminalId));
        // invoice object
        $request->appendChild($xml->createElement('EnablePartialAuthorization', $builder->allowPartialAuth));
        $request->appendChild($xml->createElement('ForceDuplicate', $builder->allowDuplicates));

        $transaction->appendChild($request);

        $response = $this->doTransaction($this->buildEnvelope($xml, $transaction));
        return $this->mapResponse($builder, $response);
    }

    public function manageTransaction($builder)
    {
        $xml = new DOMDocument();
        $transactionType = $builder->transactionType;

        $transaction = $xml->createElement($this->mapRequestType($builder));
        $transaction->setAttribute('xmlns', 'http://schemas.merchantwarehouse.com/merchantware/v45/');

        // Credentials
        $credentials = $xml->createElement('Credentials');
        $credentials->appendChild($xml->createElement('MerchantName', $this->merchantName));
        $credentials->appendChild($xml->createElement('MerchantSiteId', $this->merchantSiteId));
        $credentials->appendChild($xml->createElement('MerchantKey', $this->merchantKey));
        
        $transaction->appendChild($credentials);

        // Payment Data
        if ($transactionType === TransactionType::REFUND) {
            $paymentData = $xml->createElement('PaymentData');

            $paymentData->appendChild($xml->createElement('Source', 'PreviousTransaction'));
            $paymentData->appendChild($xml->createElement('Token', $builder->transactionId));

            $transaction->appendChild($paymentData);
        }

        // Request
        $request = $xml->createElement('Request');
        if ($transactionType !== TransactionType::REFUND) {
            $request->appendChild($xml->createElement('Token', $builder->transactionId));
        }
        $request->appendChild($xml->createElement('Amount', $builder->amount + $builder->gratuity));
        if (!empty($builder->invoiceNumber)) {
            $request->appendChild($xml->createElement('InvoiceNumber', $builder->invoiceNumber));
        }
        if (!empty($builder->registerNumber)) {
            $request->appendChild($xml->createElement('RegisterNumber', $this->registerNumber));
        }
        if (!empty($builder->clientTransactionId)) {
            $request->appendChild($xml->createElement('MerchantTransactionId', $builder->clientTransactionId));
        }
        if (!empty($builder->terminalId)) {
            $request->appendChild($xml->createElement('CardAcceptorTerminalId', $this->terminalId));
        }

        if ($transactionType === TransactionType::TOKEN_DELETE || $transactionType === TransactionType::TOKEN_UPDATE) {
            $card = $builder->paymentMethod;

            $request->appendChild($xml->createElement('VaultToken', $card->token));
            if ($transactionType === TransactionType::TOKEN_UPDATE) {
                $request->appendChild($xml->createElement('ExpirationDate', $card->getShortExpiry()));
            }
        }

        $transaction->appendChild($request);

        $response = $this->doTransaction($this->buildEnvelope($xml, $transaction));
        return $this->mapResponse($builder, $response);
    }

    public function serializeRequest($builder)
    {
        throw new UnsupportedTransactionException();
    }

    public function buildEnvelope(DOMDocument $xml, DOMElement $transaction)
    {
        $soapEnvelope = $xml->createElement('soapenv:Envelope');
        $soapEnvelope->setAttribute(
            'xmlns:soapenv',
            'http://schemas.xmlsoap.org/soap/envelope/'
        );
        $soapEnvelope->setAttribute('xmlns', static::XML_NAMESPACE);

        $soapBody = $xml->createElement('soapenv:Body');

        $soapBody->appendChild($transaction);
        $soapEnvelope->appendChild($soapBody);
        $xml->appendChild($soapEnvelope);

        return $xml->saveXML();
    }

    public function mapRequestType(TransactionBuilder $builder)
    {
        switch ($builder->transactionType) {
            case TransactionType::AUTH:
                if ($builder->transactionModifier === TransactionModifier::OFFLINE) {
                    return 'ForceCapture';
                }
                return 'Authorize';
            case TransactionType::BATCH_CLOSE:
                return 'SettleBatch';
            case TransactionType::CAPTURE:
                return 'Capture';
            case TransactionType::EDIT:
                return 'AdjustTip';
            case TransactionType::REFUND:
                return 'Refund';
            case TransactionType::SALE:
                return 'Sale';
            case TransactionType::TOKEN_DELETE:
                return 'UnboardCard';
            case TransactionType::TOKEN_UPDATE:
                return 'UpdateBoardedCard';
            case TransactionType::VERIFY:
                return 'BoardCard';
            case TransactionType::VOID:
                return 'Void';
            default:
                throw new UnsupportedTransactionException();
        }
    }

    public function mapWalletId($mobileType)
    {
        switch ($mobileType) {
            case 'apple-pay':
                return 'ApplePay';
            default:
                return 'Unknown';
        }
    }

    public function mapResponse($builder, $rawResponse)
    {
        $root = $this->xml2object($rawResponse);

        $errorCode = $root->errorCode;
        $errorMessage = $root->errorMessage;

        if (!empty($errorMessage)) {
            throw new GatewayException(
                sprintf(
                    'Unexpected Gateway Response: %s - %s. ',
                    $errorCode,
                    $errorMessage
                )
            );
        }

        $item = $root->{$this->mapRequestType($builder).'Result'};

        $response = new Transaction();

        $response->responseCode = '00';
        $response->responseMessage = $item->ApprovalStatus;
        $response->transactionId = $item->Token;
        $response->authorizationCode = $item->AuthorizationCode;
        $response->hostResponseDate = $item->TransactionDate;
        $response->authorizedAmount = $item->Amount;
        $response->availableBalance = $item->RemainingCardBalance;
        $response->cardType = $item->CardType;
        $response->avsResponseCode = $item->AvsResponse;
        $response->cvnResponseCode = $item->CvResponse;
        $response->token = (string)$item->VaultToken;

        if (isset($item->BatchStatus)) {
            $response->batchSummary = new BatchSummary();
            $response->batchSummary->status = (string)$item->BatchStatus;
            $response->batchSummary->totalAmount = (string)$item->BatchAmount;
            $response->batchSummary->transactionCount = (string)$item->TransactionCount;
        }

        return $response;
    }

    /**
     * Converts a XML string to a simple object for use,
     * removing extra nodes that are not necessary for
     * handling the response
     *
     * @param string $xml Response XML from the gateway
     *
     * @return SimpleXMLElement
     */
    protected function xml2object($xml)
    {
        $envelope = simplexml_load_string(
            $xml,
            'SimpleXMLElement',
            0,
            'http://schemas.xmlsoap.org/soap/envelope/'
        );

        foreach ($envelope->Body as $response) {
            $children = $response->children(static::XML_NAMESPACE);
            foreach ($children as $item) {
                return $item;
            }
        }

        throw new Exception('XML from gateway could not be parsed');
    }

    public function processReport($builder)
    {
    }
}
