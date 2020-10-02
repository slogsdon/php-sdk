<?php

namespace GlobalPayments\Api\Entities;

/**
 * AKA Product in terms of TransIT gateway
 */
class CommercialLineItem {

    /**
     * The tax identification number of the merchant that reported the alternate tax amount.
     * 
     * @var string
     */
    public $alternateTaxId;

    /**
     * The international description code used to classify the item.
     * 
     * @var string
     */
    public $commodityCode;

    public $creditDebitIndicator;

    /**
     * The field used to add a note to a product.
     * 
     * @var string
     */
    public $description;
    
    public $discountDetails;

    /**
     * 
     * @var float
     */
    public $extendedAmount;

    /**
     * The name of the product / item.
     * 
     * @var string
     */
    public $name;


    public $netGrossIndicator;

    /**
     * The merchant assigned unique product identification code.
     * 
     * @var string
     */
    public $productCode;

    /**
     * 
     * @var float
     */
    public $quantity;

    public $taxAmount;
    public $totalAmount;

    /**
     * The product / item amount.
     * 
     * @var float
     */
    public $unitCost;

    /**
     * 
     * @var string
     */
    public $unitOfMeasure;

    public $UPC;
}
