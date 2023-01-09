<?php

namespace Musonza\LaravelDynamodbChat\Actions\Conversations;

use Bego\Condition;
use Bego\Query\Statement;
use Musonza\LaravelDynamodbChat\Actions\Action;
use Musonza\LaravelDynamodbChat\Entities\Conversation;
use Musonza\LaravelDynamodbChat\Entities\Entity;
use Musonza\LaravelDynamodbChat\Entities\Participation;
use Musonza\LaravelDynamodbChat\Helpers\Helpers;

class ClearConversation extends Action
{
    protected readonly Conversation $conversation;

    protected readonly Participation $participation;

    public function __construct(Conversation $conversation, Participation $participation)
    {
        $this->conversation = $conversation;
        $this->participation = $participation;
    }

    public function execute(): void
    {
        $this->clearConversation();
    }

    protected function clearConversation(): void
    {
        $query = $this->buildQuery();
        $offset = null;

        do {
            $results = $query->fetch(1, $offset);
            $this->getTable()->deleteBatch($results->toArrayOfObjects());
            $offset = $results->getLastEvaluatedKey();
        } while (! is_null($offset));
    }

    protected function buildQuery(): Statement
    {
        $sk = Helpers::gs1skFromParticipantIdentifier($this->participation->getId());

        return $this->getTable()
            ->query(Entity::GSI1_NAME)
            ->key($this->conversation->getPK())
            ->condition(Condition::attribute(Entity::GSI1_SORT_KEY)->beginsWith($sk));
    }
}
