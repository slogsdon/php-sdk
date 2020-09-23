<?php

namespace GlobalPayments\Api\Entities\TransIT;

class ProductDiscountDetails
{
    /**
     *
     * @var string
     */
    public $productDiscountIndicator;
    
    /**
     *
     * @var string
     */
    public $productCommodityCode;
    
    /**
     *
     * @var string
     */
    public $alternateTaxID;
    
    /**
     *
     * @var string
     */
    public $creditIndicator;
    
    /**
     *
     * @var string
     */
    public $productDiscountName;
    
    /**
     *
     * @var int
     */
    public $productDiscountAmount;
    
    /**
     *
     * @var float
     */
    public $productDiscountPercentage;
    
    /**
     *
     * @var string
     */
    public $productDiscountType;
    
    /**
     *
     * @var int
     */
    public $priority;
    
    /**
     *
     * @var bool
     */
    public $stackable;
    
    /**
     *
     * @var object array ProductModifierDetails
     */
    public $productModifierDetails;
    
    /**
     *
     * @var object array ProductTaxDetails
     */
    public $productTaxDetails;
}
