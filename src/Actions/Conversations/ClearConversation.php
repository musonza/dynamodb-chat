<?php

namespace Musonza\LaravelDynamodbChat\Actions\Conversations;

use Bego\Condition;
use Musonza\LaravelDynamodbChat\Actions\Action;
use Musonza\LaravelDynamodbChat\Entities\Conversation;
use Musonza\LaravelDynamodbChat\Entities\Entity;
use Musonza\LaravelDynamodbChat\Entities\Participation;

class ClearConversation extends Action
{
    protected Conversation $conversation;
    protected Participation $participation;

    public function __construct(Conversation $conversation, Participation $participation)
    {
        $this->conversation = $conversation;
        $this->participation = $participation;
    }

    // TODO make this action queable
    public function execute()
    {
        $sk = "PARTICIPANT#{$this->participation->getParticipantIdentifier()}";
        $query = $this->getTable()
            ->query(Entity::GLOBAL_INDEX1)
            ->key($this->conversation->getPK())
            ->condition(Condition::attribute(Entity::GLOBAL_INDEX1_SK)->beginsWith($sk));

        $offset = null;

        do {
            $results = $query->fetch(1, $offset);
            $this->getTable()->deleteBatch($results->toArrayOfObjects());
            $offset = $results->getLastEvaluatedKey();
        } while (!is_null($offset));
    }
}