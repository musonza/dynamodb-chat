<?php

namespace Musonza\LaravelDynamodbChat\Entities;

class MessageReaction extends Entity
{
    protected string $keyPrefix = 'MsgReaction#';

    protected string $entityType = 'REACTION';

    public function getPrimaryKey(): array
    {
        return [
            'PK' => $this->getPartitionKey(),
            'SK' => $this->getSortKey(),
        ];
    }

    public function getPartitionKey(): array
    {
        return [
            'S' => $this->getPK(),
        ];
    }

    public function getSortKey(): array
    {
        return [
            'S' => $this->getSK(),
        ];
    }

    public function getPK(): string
    {
        return "{$this->attribute('ConversationId')}"
            ."{$this->attribute('MessageId')}"
            ."#REACTION#{$this->attribute('ReactionName')}";
    }

    public function getSK(): string
    {
        return "#PARTICIPANT#{$this->attribute('ParticipantId')}";
    }

    public function toItem(): array
    {
        return [
            ...$this->getPrimaryKey(),
            'Type' => ['S' => $this->getEntityType()],
            'CreatedAt' => ['S' => $this->attribute('CreatedAt')],
        ];
    }
}
