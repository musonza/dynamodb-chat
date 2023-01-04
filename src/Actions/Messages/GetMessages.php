<?php

namespace Musonza\LaravelDynamodbChat\Actions\Messages;

use Bego\Component\Resultset;
use Bego\Condition;
use Musonza\LaravelDynamodbChat\Actions\Action;
use Musonza\LaravelDynamodbChat\Configuration;
use Musonza\LaravelDynamodbChat\Entities\Conversation;
use Musonza\LaravelDynamodbChat\Entities\Entity;
use Musonza\LaravelDynamodbChat\Entities\Participation;

class GetMessages extends Action
{
    private readonly Conversation $conversation;

    private readonly Participation $participation;

    public function __construct(Conversation $conversation, Participation $participation)
    {
        $this->conversation = $conversation;
        $this->participation = $participation;
    }

    public function execute(array $offset = null): Resultset
    {
        $gsi1skStartsWith = "PARTICIPANT#{$this->participation->getParticipantExternalId()}";
        $query = $this->getTable()
            ->query(Entity::GSI1_NAME)
            ->key($this->conversation->getPK())
            ->condition(Condition::attribute(Entity::GSI1_SORT_KEY)->beginsWith($gsi1skStartsWith))
            ->reverse()
            ->limit(Configuration::getPaginatorLimit());

        return $query->fetch(Configuration::getPaginatorPages(), $offset);
    }
}
