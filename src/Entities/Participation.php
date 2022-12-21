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
            'PK' => $this->getPartitionKey(),
            'SK' => $this->getSortKey(),
        ];
    }

    public function getSortKey(): array
    {
        return [
            'S' => sprintf(self::PARTICIPATION_PK_PREFIX,  $this->getParticipantIdentifier())
        ];
    }

    public function getPartitionKey(): array
    {
        return $this->conversation->getPartitionKey();
    }

    public function getGSI1(): array
    {
        return [
            'GSI1PK' => $this->getSortKey(),
            'GSI1SK' => $this->getPartitionKey(),
        ];
    }

    public function getParticipantIdentifier(): string
    {
        return $this->participantId;
    }

    public function toItem(): array
    {
        return[
            ...$this->getPrimaryKey(),
            'Type' => ['S' => self::ENTITY_TYPE],
            ...$this->getGSI1(),
            'CreatedAt' => ['S' => now()->toISOString()],
        ];
    }
}