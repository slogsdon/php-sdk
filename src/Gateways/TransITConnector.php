<?php

namespace GlobalPayments\Api\Gateways;

use DOMDocument;
use DOMElement;
use GlobalPayments\Api\Builders\AuthorizationBuilder;
use GlobalPayments\Api\Builders\ManagementBuilder;
use GlobalPayments\Api\Builders\ReportBuilder;
use GlobalPayments\Api\Builders\TransactionBuilder;
use GlobalPayments\Api\Entities\BatchSummary;
use GlobalPayments\Api\Entities\Transaction;
use GlobalPayments\Api\Entities\Enums\PaymentMethodType;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\Entities\Exceptions\UnsupportedTransactionException;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\PaymentMethods\Interfaces\ITrackData;
use GlobalPayments\Api\Entities\Enums\TrackNumber;
use GlobalPayments\Api\Entities\Exceptions\ConfigurationException;
use GlobalPayments\Api\Utils\StringUtils;

class TransITConnector extends XmlGateway implements IPaymentGateway
{

    
    public $merchantId ;
    public $deviceId ;
    public $transactionKey;
    public $manifest;
    public $userId;
    public $password;
    public $developerId;
    
    public $supportsHostedPayments = false;

    public function processAuthorization(AuthorizationBuilder $builder)
    {
        if (empty($this->transactionKey) && empty($this->manifest)) {
            throw new ConfigurationException('transactionKey/manifest is required for this transaction.');
        }

        $xml = new DOMDocument();
        $paymentMethod = $builder->paymentMethod;
        
        $transaction = $xml->createElement($this->mapRequestType($builder));
        
        $transaction->appendChild($xml->createElement('deviceID', $this->deviceId));
        $transaction->appendChild($xml->createElement('transactionKey', $this->transactionKey));
        
        if ($paymentMethod instanceof CreditCardData) {
            $card = $paymentMethod;
            if ($card->token != null) {
                $transaction->appendChild($xml->createElement('cardDataSource', 'MANUAL'));
                
                if (!empty($builder->amount)) {
                    $transaction->appendChild($xml->createElement('transactionAmount', $builder->amount));
                }
                
                $transaction->appendChild($xml->createElement('cardNumber', $card->token));
                $transaction->appendChild($xml->createElement('expirationDate', '1225'));
            } else {
                $transaction->appendChild($xml->createElement('cardDataSource', 'MANUAL'));
                
                if (!empty($builder->amount)) {
                    $transaction->appendChild($xml->createElement('transactionAmount', $builder->amount));
                }
                
                $transaction->appendChild($xml->createElement('cardNumber', $card->number));
                $transaction->appendChild($xml->createElement('expirationDate', $card->getShortExpiry()));
            }
        } elseif ($paymentMethod instanceof ITrackData) {
            $transaction->appendChild($xml->createElement('cardDataSource', 'SWIPE'));
            if (!empty($builder->amount)) {
                $transaction->appendChild($xml->createElement('transactionAmount', $builder->amount));
            }
            $track = $paymentMethod;
            $trackField = ($track->trackNumber == TrackNumber::TRACK_TWO) ? 'track2Data' : 'track1Data';
            $transaction->appendChild($xml->createElement($trackField, $track->trackData));
            if ($paymentMethod->paymentMethodType === PaymentMethodType::DEBIT) {
                $transaction->appendChild($xml->createElement('pin', $track->pinBlock));
                $transaction->appendChild($xml->createElement('pinKsn', $track->encryptionData->ksn));
            }
        }
        
        if (!empty($builder->cashTendered)) {
            $transaction->appendChild($xml->createElement('cashTendered', $builder->cashTendered));
        }
        
        if (!empty($builder->discountDetails)) {
            $transaction->appendChild($this->addDiscountDetails($xml, $builder->discountDetails));
        }
        
        if (!empty($builder->productData)) {
            $transaction->appendChild($this->addProductDetails($xml, $builder->productData));
        }
        
        $transaction->appendChild($xml->createElement('developerID', $this->developerId));
        
        $response = $this->doTransaction($xml->saveXML($transaction));
        return $this->mapResponse($builder, $response);
    }

    public function manageTransaction(ManagementBuilder $builder)
    {
        if (empty($this->transactionKey) && empty($this->manifest)) {
            throw new ConfigurationException('transactionKey/manifest is required for this transaction.');
        }
        
        $xml = new DOMDocument();
        $paymentMethod = $builder->paymentMethod;
        
        $transaction = $xml->createElement($this->mapRequestType($builder));
        
        $transaction->appendChild($xml->createElement('deviceID', $this->deviceId));
        $transaction->appendChild($xml->createElement('transactionKey', $this->transactionKey));
        
        if (!empty($builder->gratuity)) {
            $transaction->appendChild($xml->createElement('tip', $builder->gratuity));
        }
        
        if (!empty($paymentMethod->transactionId)) {
            $transaction->appendChild($xml->createElement('transactionID', $paymentMethod->transactionId));
        }
        
        if ($builder->transactionType === TransactionType::BATCH_CLOSE) {
            $transaction->appendChild($xml->createElement('operatingUserID', $this->userId));
        } else {
            $transaction->appendChild($xml->createElement('developerID', $this->developerId));
        }
        
        if (!empty($builder->description) && $builder->transactionType == TransactionType::VOID) {
            $transaction->appendChild($xml->createElement('voidReason', $builder->description));
        }
        
        $response = $this->doTransaction($xml->saveXML($transaction));
        return $this->mapResponse($builder, $response);
    }

    public function serializeRequest(AuthorizationBuilder $builder)
    {
        throw new UnsupportedTransactionException();
    }

    public function mapRequestType(TransactionBuilder $builder)
    {
        switch ($builder->transactionType) {
            case TransactionType::AUTH:
                return 'Auth';
            case TransactionType::CAPTURE:
                return 'Capture';
            case TransactionType::SALE:
                if ($builder->paymentMethod->paymentMethodType === PaymentMethodType::DEBIT) {
                    return 'DebitSale';
                } elseif ($builder->paymentMethod->paymentMethodType === PaymentMethodType::CASH) {
                    return 'CashSale';
                }
                return 'Sale';
            case TransactionType::BALANCE:
                return 'BalanceInquiry';
            case TransactionType::VERIFY:
                if ($builder->requestMultiUseToken === true) {
                    return 'GetOnusToken';
                } else {
                    return 'CardVerification';
                }
            case TransactionType::EDIT:
                return 'TipAdjustment';
            case TransactionType::VOID:
                return 'Void';
            case TransactionType::BATCH_CLOSE:
                return 'BatchClose';
            case TransactionType::REFUND:
                return 'Return';
            default:
                throw new UnsupportedTransactionException();
        }
    }

    public function mapResponse($builder, $rawResponse)
    {
        $root = $this->xml2object($rawResponse);
        
        $this->checkResponse($root);

        $response = new Transaction();

        $response->responseCode = '00';
        $response->responseMessage = (string) $root->responseMessage;
        $response->transactionId = (string) $root->transactionID;
        $response->hostResponseDate = (string) $root->transactionTimestamp;
        $response->authorizedAmount = (string) $root->transactionAmount;
        $response->avsResponseCode = (string) $root->addressVerificationCode;
        $response->cardType = (string) $root->cardType;
        $response->cardLast4 = (string) $root->maskedCardNumber;
        $response->customerReceipt = (string) $root->customerReceipt;
        $response->merchantReceipt = (string) $root->merchantReceipt;
        $response->token = (string)$root->token;
        $response->authorizationCode = (string)$root->authCode;
        $response->transactionKey = (string)$root->transactionKey;
        
        if (!empty($builder) && $builder->transactionType === TransactionType::BATCH_CLOSE) {
            $response->batchSummary = new BatchSummary();
            $response->batchSummary->totalAmount = (string)$root->batchInfo->saleAmount;
            $response->batchSummary->transactionCount = (string)$root->batchInfo->saleCount;
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
            'SimpleXMLElement'
        );

        return $envelope;
    }

    public function processReport(ReportBuilder $builder)
    {
    }
    
    private function addDiscountDetails(DOMDocument $xml, $discountDetails)
    {
        $transDiscountDetails = $xml->createElement('transDiscountDetails');
        
        if (!empty($discountDetails->transTotalDiscountAmount)) {
            $transDiscountDetails->appendChild(
                $xml->createElement('transTotalDiscountAmount', $discountDetails->transTotalDiscountAmount)
            );
        }

        if (!empty($discountDetails->transDiscount)) {
            foreach ($discountDetails->transDiscount as $transDiscount) {
                $transDiscountEle = $xml->createElement("transDiscount");
                
                if (!empty($transDiscount->transDiscountName)) {
                    $transDiscountEle->appendChild($xml->createElement(
                        'transDiscountName',
                        $transDiscount->transDiscountName
                    ));
                }
                
                if (!empty($transDiscount->transDiscountAmount)) {
                    $transDiscountEle->appendChild($xml->createElement(
                        'transDiscountAmount',
                        $transDiscount->transDiscountAmount
                    ));
                }
                
                if (!empty($transDiscount->transDiscountPercentage)) {
                    $transDiscountEle->appendChild($xml->createElement(
                        'transDiscountPercentage',
                        $transDiscount->transDiscountPercentage
                    ));
                }
                
                if (!empty($transDiscount->priority)) {
                    $transDiscountEle->appendChild($xml->createElement(
                        'priority',
                        $transDiscount->priority
                    ));
                }
                
                if (isset($transDiscount->stackable)) {
                    $transDiscountEle->appendChild($xml->createElement(
                        'stackable',
                        ($transDiscount->stackable) ? 'YES' : 'NO'
                    ));
                }
                
                $transDiscountDetails->appendChild($transDiscountEle);
            }
        }

        return $transDiscountDetails;
    }
    
    private function addProductDetails(DOMDocument $xml, $productDataCollection)
    {
        $productDetails = $xml->createElement('productDetails');
        
        foreach ($productDataCollection as $productData) {
            if (!empty($productData->productCode)) {
                $productDetails->appendChild(
                    $xml->createElement('productCode', $productData->productCode)
                );
            }

            if (!empty($productData->productName)) {
                $productDetails->appendChild(
                    $xml->createElement('productName', $productData->productName)
                );
            }

            if (!empty($productData->price)) {
                $productDetails->appendChild(
                    $xml->createElement('price', $productData->price)
                );
            }

            if (!empty($productData->quantity)) {
                $productDetails->appendChild(
                    $xml->createElement('quantity', $productData->quantity)
                );
            }

            if (!empty($productData->measurementUnit)) {
                $productDetails->appendChild(
                    $xml->createElement('measurementUnit', $productData->measurementUnit)
                );
            }

            if (!empty($productData->productNotes)) {
                $productDetails->appendChild(
                    $xml->createElement('productNotes', $productData->productNotes)
                );
            }
            
            if (!empty($productData->productDiscountDetails)) {
                $this->addProductDiscountDetails($xml, $productDetails, $productData->productDiscountDetails);
            }
            
            if (!empty($productData->productVariation)) {
                $productDetails->appendChild(
                    $xml->createElement('productVariation', $productData->productVariation)
                );
            }
            
            if (!empty($productData->productModifierDetails)) {
                $this->addProductModifierDetails(
                    $xml,
                    $productDetails,
                    $productData->productModifierDetails
                );
            }
            
            if (!empty($productData->productTaxDetails)) {
                $this->addProductTaxDetails(
                    $xml,
                    $productDetails,
                    $productData->productTaxDetails
                );
            }
        }

        return $productDetails;
    }
    
    private function addProductDiscountDetails($xml, $productDetails, $productDiscountDetails)
    {
        foreach ($productDiscountDetails as $discountDetails) {
            $discountDetailsEle = $xml->createElement("productDiscountDetails");
            
            if (!empty($discountDetails->productModifierDetails)) {
                $this->addProductModifierDetails(
                    $xml,
                    $discountDetailsEle,
                    $discountDetails->productModifierDetails
                );
            }

            if (!empty($discountDetails->productDiscountIndicator)) {
                $discountDetailsEle->appendChild($xml->createElement(
                    'productDiscountIndicator',
                    $discountDetails->productDiscountIndicator
                ));
            }
            
            if (!empty($discountDetails->productCommodityCode)) {
                $discountDetailsEle->appendChild($xml->createElement(
                    'productCommodityCode',
                    $discountDetails->productCommodityCode
                ));
            }
            
            if (!empty($discountDetails->creditIndicator)) {
                $discountDetailsEle->appendChild($xml->createElement(
                    'creditIndicator',
                    $discountDetails->creditIndicator
                ));
            }
            
            if (!empty($discountDetails->productDiscountName)) {
                $discountDetailsEle->appendChild($xml->createElement(
                    'productDiscountName',
                    $discountDetails->productDiscountName
                ));
            }

            if (!empty($discountDetails->productDiscountAmount)) {
                $discountDetailsEle->appendChild($xml->createElement(
                    'productDiscountAmount',
                    $discountDetails->productDiscountAmount
                ));
            }

            if (!empty($discountDetails->productDiscountPercentage)) {
                $discountDetailsEle->appendChild($xml->createElement(
                    'productDiscountPercentage',
                    $discountDetails->productDiscountPercentage
                ));
            }

            if (!empty($discountDetails->productDiscountType)) {
                $discountDetailsEle->appendChild($xml->createElement(
                    'productDiscountType',
                    $discountDetails->productDiscountType
                ));
            }

            if (!empty($discountDetails->priority)) {
                $discountDetailsEle->appendChild($xml->createElement(
                    'priority',
                    $discountDetails->priority
                ));
            }

            if (isset($discountDetails->stackable)) {
                $discountDetailsEle->appendChild($xml->createElement('stackable', ($discountDetails->stackable) ? 'YES' : 'NO'));
            }
            
            if (!empty($discountDetails->productTaxDetails)) {
                $this->addProductTaxDetails(
                    $xml,
                    $discountDetailsEle,
                    $discountDetails->productTaxDetails
                );
            }
            
            $productDetails->appendChild($discountDetailsEle);
        }
    }
    
    private function addProductTaxDetails($xml, $productDetails, $productTaxDetails)
    {
        foreach ($productTaxDetails as $taxDetails) {
            $taxDetailsEle = $xml->createElement("productTaxDetails");

            if (!empty($taxDetails->productTaxName)) {
                $taxDetailsEle->appendChild($xml->createElement('productTaxName', $taxDetails->productTaxName));
            }

            if (!empty($taxDetails->productTaxAmount)) {
                $taxDetailsEle->appendChild($xml->createElement('productTaxAmount', $taxDetails->productTaxAmount));
            }

            if (!empty($taxDetails->productTaxPercentage)) {
                $taxDetailsEle->appendChild($xml->createElement('productTaxPercentage', $taxDetails->productTaxPercentage));
            }

            $productDetails->appendChild($taxDetailsEle);
        }
    }
    
    private function addProductModifierDetails($xml, $productDetails, $productModifierDetails)
    {
        foreach ($productModifierDetails as $modifierDetails) {
            $modifierDetailsEle = $xml->createElement("productModifierDetails");

            if (!empty($modifierDetails->modifierName)) {
                $modifierDetailsEle->appendChild($xml->createElement('modifierName', $modifierDetails->modifierName));
            }

            if (!empty($modifierDetails->modifierValue)) {
                $modifierDetailsEle->appendChild($xml->createElement('modifierValue', $modifierDetails->modifierValue));
            }

            if (!empty($modifierDetails->modifierPrice)) {
                $modifierDetailsEle->appendChild($xml->createElement('modifierPrice', $modifierDetails->modifierPrice));
            }

            $productDetails->appendChild($modifierDetailsEle);
        }
    }
    
    protected function checkResponse($root)
    {
        $acceptedCodes = [ '00', 'A0000' ];

        $responseCode = (string)$root->hostResponseCode;
        $responseMessage = (string)$root->responseMessage;
        $status = (string)$root->status;

        if (!in_array($responseCode, $acceptedCodes) && $status !== 'PASS') {
            throw new GatewayException(
                sprintf('Unexpected Gateway Response: %s - %s', $responseCode, $responseMessage),
                $responseCode,
                $responseMessage
            );
        }
    }
    
    public function getTransactionKey()
    {
        $xml = new DOMDocument();
        
        $transaction = $xml->createElement('GenerateKey');
        
        $transaction->appendChild($xml->createElement('mid', $this->merchantId));
        $transaction->appendChild($xml->createElement('userID', $this->userId));
        $transaction->appendChild($xml->createElement('password', $this->password));
        
        if (!empty($this->transactionKey)) {
            $transaction->appendChild($xml->createElement('transactionKey', $this->transactionKey));
        }
        
        $response = $this->doTransaction($xml->saveXML($transaction));
        return $this->mapResponse(null, $response);
    }
    
    public function createManifest()
    {
        $sEncryptedData = "";
        $now = new \DateTime();
        $dateFormatString = $now->format('mdY');
        $plainText = StringUtils::asPaddedAtEndString($this->merchantId, 20, ' ')
                . StringUtils::asPaddedAtEndString($this->deviceId, 24, ' ')
                . '000000000000'
                . StringUtils::asPaddedAtEndString($dateFormatString, 8, ' ');
        $tempTransactionKey = substr($this->transactionKey, 0, 16);
        $encrypted = openssl_encrypt(
            $plainText,
            'aes-128-cbc',
            $tempTransactionKey,
            OPENSSL_ZERO_PADDING,
            $tempTransactionKey
        );
        $sEncryptedData = bin2hex(base64_decode($encrypted));
        $hashKey = hash_hmac('md5', $this->transactionKey, $this->transactionKey);
        return substr($hashKey, 0, 4) . $sEncryptedData . substr($hashKey, -4, 4);
    }
}
