<?php

namespace Musonza\LaravelDynamodbChat\Actions\Conversations;

use Aws\DynamoDb\DynamoDbClient;
use Bego\Condition;
use Musonza\LaravelDynamodbChat\Actions\Action;
use Musonza\LaravelDynamodbChat\ConfigurationManager;
use Musonza\LaravelDynamodbChat\Entities\Conversation;
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

    public function execute()
    {
        $sk = "PARTICIPANT#{$this->participation->getParticipantIdentifier()}";
        $result = $this->getTable()
            ->query('GS1')
            ->key($this->conversation->getPK())
            ->condition(Condition::attribute('GSI1SK')->beginsWith($sk))
            ->fetch();

        // $result->getLastEvaluatedKey(); // if not null we have more items
        $this->getTable()->deleteBatch($result->toArrayOfObjects());
    }
}