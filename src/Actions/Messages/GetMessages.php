<?php

namespace Musonza\LaravelDynamodbChat\Actions\Messages;

use Bego\Component\Resultset;
use Bego\Condition;
use Musonza\LaravelDynamodbChat\Actions\Action;
use Musonza\LaravelDynamodbChat\ConfigurationManager;
use Musonza\LaravelDynamodbChat\Entities\Conversation;
use Musonza\LaravelDynamodbChat\Entities\Entity;
use Musonza\LaravelDynamodbChat\Entities\Participation;

class GetMessages extends Action
{
    private Conversation $conversation;
    private Participation $participation;

    public function __construct(Conversation $conversation, Participation $participation)
    {
        $this->conversation = $conversation;
        $this->participation = $participation;
    }

    public function execute(array $offset = null): Resultset
    {
        // TODO resolve IDs cleanly
        $gsi1skStartsWith = "PARTICIPANT#{$this->participation->getParticipantIdentifier()}";
        $query = $this->getTable()
            ->query(Entity::GLOBAL_INDEX1)
            ->key($this->conversation->getPK())
            ->condition(Condition::attribute(Entity::GLOBAL_INDEX1_SK)->beginsWith($gsi1skStartsWith))
            ->limit(ConfigurationManager::getPaginatorLimit());

        return $query->fetch(ConfigurationManager::getPaginatorPages(), $offset);
    }
}