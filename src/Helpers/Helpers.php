<?php

namespace Musonza\LaravelDynamodbChat\Helpers;

use Illuminate\Support\Carbon;
use Tuupola\KsuidFactory;

class Helpers
{
    public static function generateId(string $prefix, Carbon $date): string
    {
        return $prefix . self::generateKsuid($date);
    }

    public static function generateKSUID(Carbon $date): string
    {
        return KsuidFactory::fromTimestamp($date->getTimestamp())->string();
    }

    public static function directConversationKey(string $id1, string $id2): string
    {
        return strcmp($id1, $id2) < 0 ? "DIRECT#P1#{$id1}P2#{$id2}" : "DIRECT#P1#{$id2}P2#{$id1}";
    }
}