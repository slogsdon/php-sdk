<?php

namespace GlobalPayments\Api\Entities;

class DiscountDetails {

    /**
     * The dollar amount of discount applied to a product
     * 
     * @var float
     */
    public $discountAmount;

    /**
     * The name of the discount applied to a product. This does not impact transaction functionality. It is used for reporting purposes.
     * 
     * @var string
     */
    public $discountName;

    /**
     * The discount percentage applied to a product. Corresponds with productDiscountName. This does not impact transaction functionality. It is used for reporting purposes.
     * 
     * @var float|int
     */
    public $discountPercentage;

    /**
     * This field defines the transaction types that the discount can be applied to. Corresponds with productDiscountName. This does not impact transaction functionality. It is used for reporting purposes.
     * 
     * @var float
     */
    public $discountType;

    public function __construct($discountAmount = null, $discountName = null, $discountPercentage = null, $discountType = null)
    {        
        $this->discountAmount = $discountAmount;        
        $this->discountName = $discountName;        
        $this->discountPercentage = $discountPercentage;        
        $this->discountType = $discountType;
    }
}
