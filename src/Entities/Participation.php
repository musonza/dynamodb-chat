<?php

namespace Musonza\LaravelDynamodbChat\Entities;

class Participation extends Entity
{
    const PARTICIPATION_PK_PREFIX = 'PARTICIPANT#%s';
    protected Conversation $conversation;
    protected string $participantId;
    protected string $entityType = 'PARTICIPATION';

//    public function __construct(Conversation $conversation, string $participantId)
//    {
//        $this->conversation = $conversation;
//        $this->participantId = $participantId;
//    }

    /**
     * @return array<string, array>
     */
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

    /**
     * @return array<string, array>
     */
    public function getPartitionKey(): array
    {
        return [
            'S' => $this->getAttribute('ConversationId')
        ];
    }

    public function getPK(): string
    {
        return $this->getAttribute('ConversationId');
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
        return $this->getAttribute('Id');
    }

    public function toItem(): array
    {
        return[
            ...$this->getPrimaryKey(),
            'Type' => ['S' => $this->getEntityType()],
            ...$this->getGSI1(),
            'ParticipantId' => ['S' => $this->getParticipantExternalId()],
            'CreatedAt' => ['S' => now()->toISOString()],
        ];
    }
}