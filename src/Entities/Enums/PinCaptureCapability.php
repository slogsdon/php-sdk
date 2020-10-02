<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class PinCaptureCapability extends Enum
{
    const FourCharacters    = '4';
    const FiveCharacters    = '5';
    const SixCharacters     = '6';
    const SevenCharacters   = '7';
    const EightCharacters   = '8';
    const NineCharacters    = '9';
    const TenCharacters     = '10';
    const ElevenCharacters  = '11';
    const TwelveCharacters  = '12';
    const UNKNOWN           = 'UNKNOWN';
    const NONE              = 'NOT_SUPPORTED';
}
