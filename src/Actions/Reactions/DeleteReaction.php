<?php

namespace Musonza\LaravelDynamodbChat\Actions\Reactions;

use Bego\Condition;
use Bego\Exception;
use Musonza\LaravelDynamodbChat\Actions\Action;
use Musonza\LaravelDynamodbChat\Entities\MessageReaction;

class DeleteReaction extends Action
{
    private MessageReaction $messageReaction;

    public function __construct(MessageReaction $messageReaction)
    {
        $this->messageReaction = $messageReaction;
    }

    /**
     * @throws Exception
     */
    public function execute(): bool
    {
        $item = $this->getTable()
            ->query()
            ->key($this->messageReaction->getPK())
            ->condition(Condition::attribute('SK')->eq($this->messageReaction->getSK()))
            ->fetch()
            ->first();

        return $item && $this->getTable()->delete($item);
    }
}
