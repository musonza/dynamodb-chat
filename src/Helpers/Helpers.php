<?php

namespace Musonza\LaravelDynamodbChat\Helpers;

use Illuminate\Support\Carbon;
use Tuupola\Ksuid;
use Tuupola\KsuidFactory;
class Helpers
{
    public static function generateKSUID(Carbon $date): string
    {
        $ksuid = KsuidFactory::fromTimestamp($date->getTimestamp());

        return $ksuid->string();
    }
}