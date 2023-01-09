<?php

namespace Musonza\LaravelDynamodbChat\Actions\Messages;

use Bego\Condition;
use Musonza\LaravelDynamodbChat\Actions\Action;
use Musonza\LaravelDynamodbChat\Entities\Entity;
use Musonza\LaravelDynamodbChat\Entities\Message;

class GetMessage extends Action
{
    private Message $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    public function execute(): Entity
    {
        $condition = Condition::attribute(Entity::SORT_KEY)->eq($this->message->getSK());
        $message = $this->query($this->message, $condition);

        return $message->first();
    }
}
