<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\TransITConnector\Certification;

use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\PaymentMethods\CreditCardData;

/**
 * Info provided during certification
 */
class TestData {
    static function getVisa1 () {
        $card = new CreditCardData;
        $card->number   = 4012000098765439;
        $card->expYear  = 20;
        $card->expMonth = 12;
        $card->cvn      = 999;
        return $card;
    }

    public function getVisa2 () {
        $card = new CreditCardData;
        $card->number   = 4012881888818888;
        $card->expYear  = 20;
        $card->expMonth = 12;
        $card->cvn      = 999;
        return $card;
    }

    public function getMCUnclassifiedTIC () {
        $card = new CreditCardData;
        $card->number   = 5146315000000055;
        $card->expYear  = 20;
        $card->expMonth = 12;
        $card->cvn      = 998;
        return $card;
    }

    public function getMCSwipeTIC () {
        $card = new CreditCardData;
        $card->number   = 5146312200000035;
        $card->expYear  = 20;
        $card->expMonth = 12;
        $card->cvn      = 998;
        return $card;
    }

    public function getMCKeyedTIC () {
        $card = new CreditCardData;
        $card->number   = 5146312620000045;
        $card->expYear  = 20;
        $card->expMonth = 12;
        $card->cvn      = 998;
        return $card;
    }

    public function getMC2BIN () {
        $card = new CreditCardData;
        $card->number   = 2223000048400011;
        $card->expYear  = 25;
        $card->expMonth = 12;
        $card->cvn      = 998;
        return $card;
    }

    public function getAmex () {
        $card = new CreditCardData;
        $card->number   = 371449635392376;
        $card->expYear  = 25;
        $card->expMonth = 12;
        $card->cvn      = 9997;
        return $card;
    }

    public function getDiscover () {
        $card = new CreditCardData;
        $card->number   = 6011000993026909;
        $card->expYear  = 20;
        $card->expMonth = 12;
        $card->cvn      = 996;
        return $card;
    }

    public function getDiscoverCUP () {
        $card = new CreditCardData;
        $card->number   = 6282000123842342;
        $card->expYear  = 20;
        $card->expMonth = 12;
        $card->cvn      = 996;
        return $card;
    }

    public function getDiscoverCUP2 () {
        $card = new CreditCardData;
        $card->number   = 6221261111112650;
        $card->expYear  = 20;
        $card->expMonth = 12;
        $card->cvn      = 996;
        return $card;
    }

    public function getDiners () {
        $card = new CreditCardData;
        $card->number   = 3055155515160018;
        $card->expYear  = 20;
        $card->expMonth = 12;
        $card->cvn      = 996;
        return $card;
    }

    public function getJCB () {
        $card = new CreditCardData;
        $card->number   = 3530142019945859;
        $card->expYear  = 20;
        $card->expMonth = 12;
        $card->cvn      = 996;
        return $card;
    }

    public function getAVSData () {
        $address = new Address();
        $address->streetAddress1    = '8320';
        $address->postalCode        = '85284';
        return $address;
    }
}
