<?php

namespace Musonza\LaravelDynamodbChat\Actions\Conversations;

use Bego\Condition;
use InvalidArgumentException;
use Musonza\LaravelDynamodbChat\Actions\Action;
use Musonza\LaravelDynamodbChat\Entities\Conversation;
use Musonza\LaravelDynamodbChat\Exceptions\ConversationExistsException;
use Musonza\LaravelDynamodbChat\Exceptions\InvalidConversationParticipants;
use Musonza\LaravelDynamodbChat\Helpers\Helpers;

class CreateConversation extends Action
{
    protected Conversation $conversation;

    public function __construct(Conversation $conversation)
    {
        $this->conversation = $conversation;
    }

    public function execute(): Conversation
    {
        $participantIds = $this->conversation->getParticipantIds();
        $conditions = [];

        if ($this->conversation->isDirect()) {
            if (count($participantIds) !== 2) {
                throw new InvalidConversationParticipants(
                    $this->conversation,
                    InvalidConversationParticipants::REQUIRED_PARTICIPANT_COUNT
                );
            }

            $directConversationKey = Helpers::directConversationKey($participantIds[0], $participantIds[1]);
            $this->conversation->setId($directConversationKey);
            $this->conversation->setType(Conversation::ENTITY_TYPE_DIRECT);

            $conditions = [
                Condition::attribute('PK')->notEq($this->conversation->getPK())
            ];
        }

        $created = $this->getTable()->put($this->conversation->toArray(), $conditions);

        if (!$created) {
            throw new ConversationExistsException($this->conversation);
        }

        return $this->conversation;
    }
}