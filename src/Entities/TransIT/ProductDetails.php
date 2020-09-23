<?php

namespace GlobalPayments\Api\Entities\TransIT;

class ProductDetails
{
    /**
     *
     * @var string
     */
    public $productCode;
    
    /**
     *
     * @var string
     */
    public $productName;
    
    /**
     *
     * @var int
     */
    public $price;
    
    /**
     *
     * @var int
     */
    public $quantity;
    
    /**
     *
     * @var int
     */
    public $measurementUnit;
    
    /**
     *
     * @var string
     */
    public $productNotes;
    
    /**
     *
     * @var string
     */
    public $productVariation;
    
    /**
     *
     * @var object array ProductDiscountDetails
     */
    public $productDiscountDetails;
}
