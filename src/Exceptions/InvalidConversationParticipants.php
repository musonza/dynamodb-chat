<?php

namespace Musonza\LaravelDynamodbChat\Exceptions;

use InvalidArgumentException;

class InvalidConversationParticipants extends InvalidArgumentException
{
    public const REQUIRED_PARTICIPANT_COUNT = 'Direct conversation requires 2 participants to be specified';

    public const PARTICIPANTS_IMMUTABLE = 'Direct conversation participants can not be changed';

    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
