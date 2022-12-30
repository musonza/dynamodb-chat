<?php

namespace Musonza\LaravelDynamodbChat\Helpers;

use Illuminate\Support\Carbon;
use Musonza\LaravelDynamodbChat\Entities\Conversation;
use Musonza\LaravelDynamodbChat\Entities\Participation;
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
        return strcmp($id1, $id2) < 0
            ? "CONVERSATION#DIRECT#P1#{$id1}P2#{$id2}"
            : "CONVERSATION#DIRECT#P1#{$id2}P2#{$id1}";
    }

    public static function gs1skFromParticipantIdentifier(string $id): string
    {
        return "PARTICIPANT#{$id}";
    }

    public static function gs1PKForMessage(Participation $participation): string
    {
        return $participation->getConversation()->getId() . '#PARTICIPANT#' . $participation->getParticipantExternalId();
    }
}