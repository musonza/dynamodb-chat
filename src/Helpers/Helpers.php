<?php

namespace Musonza\LaravelDynamodbChat\Helpers;

use Illuminate\Support\Carbon;
use Tuupola\KsuidFactory;

class Helpers
{
    public static function generateKSUID(Carbon $date): string
    {
        return KsuidFactory::fromTimestamp($date->getTimestamp())->string();
    }

    public static function directConversationKey(string $id1, string $id2): string
    {
        return strcmp($id1, $id2) < 0 ? "DMP1#{$id1}DMP2#{$id2}" : "DMP1#{$id2}DMP2#{$id1}";
    }
}