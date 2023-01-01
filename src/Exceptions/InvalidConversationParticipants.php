<?php

namespace Musonza\LaravelDynamodbChat\Exceptions;

use InvalidArgumentException;
use Musonza\LaravelDynamodbChat\Entities\Conversation;

class InvalidConversationParticipants extends InvalidArgumentException
{
    public const REQUIRED_PARTICIPANT_COUNT = "Direct conversation requires 2 participants to be specified";
    public const PARTICIPANTS_IMMUTABLE = "Direct conversation participants can not be changed";
    public Conversation $conversation;
    public function __construct(Conversation $conversation, string $message)
    {
        parent::__construct($message);
        $this->conversation = $conversation;
    }
}