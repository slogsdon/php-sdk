<?php

namespace GlobalPayments\Api\Entities\TransIT;

class TransDiscount
{
    /**
     *
     * @var string
     */
    public $transDiscountName;
    
    /**
     *
     * @var int
     */
    public $transDiscountAmount;
    
    /**
     *
     * @var float
     */
    public $transDiscountPercentage;
    
    /**
     * Indicates the priority order in which discounts are applied at both the order and product levels.
     * Min Length = 1
     * Max Length = 3
     *
     * @var int
     */
    public $priority;
    
    /**
     *
     * @var bool
     */
    public $stackable;
}
