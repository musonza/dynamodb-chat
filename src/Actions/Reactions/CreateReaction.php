<?php

namespace Musonza\LaravelDynamodbChat\Actions\Reactions;

use Bego\Condition;
use Musonza\LaravelDynamodbChat\Actions\Action;
use Musonza\LaravelDynamodbChat\Entities\MessageReaction;

class CreateReaction extends Action
{
    private MessageReaction $messageReaction;

    public function __construct(MessageReaction $messageReaction)
    {
        $this->messageReaction = $messageReaction;
    }

    /**
     * @throws \Exception
     */
    public function execute(): MessageReaction
    {
        $conditions = [
            Condition::attribute('SK')->exists(false),
        ];

        $success = $this->getTable()->put($this->messageReaction->toArray(), $conditions);

        if (! $success) {
            throw new \Exception('Unable to create reaction. Reaction might already exist');
        }

        return $this->messageReaction;
    }
}
