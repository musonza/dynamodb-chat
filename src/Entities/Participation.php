<?php

namespace Musonza\LaravelDynamodbChat\Entities;

class Participation extends Entity
{
    const PARTICIPATION_PK_PREFIX = 'PARTICIPANT#%s';
    const ENTITY_TYPE = 'PARTICIPATION';
    protected Conversation $conversation;
    protected string $participantId;

    public function __construct(Conversation $conversation, string $participantId)
    {
        $this->conversation = $conversation;
        $this->participantId = $participantId;
    }

    public function getPrimaryKey(): array
    {
        return [
            Entity::PARTITION_KEY => $this->getPartitionKey(),
            Entity::SORT_KEY => $this->getSortKey(),
        ];
    }

    public function getSortKey(): array
    {
        return [
            'S' => sprintf(self::PARTICIPATION_PK_PREFIX,  $this->getParticipantExternalId())
        ];
    }

    public function getConversation(): Conversation
    {
        return $this->conversation;
    }

    public function getPartitionKey(): array
    {
        return $this->conversation->getPartitionKey();
    }

    public function getPK(): string
    {
        return array_values($this->getPartitionKey())[0];
    }

    public function getGSI1(): array
    {
        return [
            Entity::GLOBAL_INDEX1_PK => $this->getSortKey(),
            Entity::GLOBAL_INDEX1_SK => $this->getPartitionKey(),
        ];
    }

    public function getParticipantExternalId(): string
    {
        return $this->participantId;
    }

    public function toItem(): array
    {
        return[
            ...$this->getPrimaryKey(),
            'Type' => ['S' => self::ENTITY_TYPE],
            ...$this->getGSI1(),
            'ParticipantId' => ['S' => $this->getParticipantExternalId()],
            'CreatedAt' => ['S' => now()->toISOString()],
        ];
    }
}