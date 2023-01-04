<?php

namespace Musonza\LaravelDynamodbChat\Entities;

class Participation extends Entity
{
    public const PARTICIPATION_PK_PREFIX = 'PARTICIPANT#%s';

    protected string $entityType = 'PARTICIPATION';

    public function getPK(): string
    {
        return $this->getAttribute('ConversationId');
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

    /**
     * @return array<string, array>
     */
    public function getPartitionKey(): array
    {
        return [
            'S' => $this->getAttribute('ConversationId'),
        ];
    }

    public function getSortKey(): array
    {
        return [
            'S' => sprintf(self::PARTICIPATION_PK_PREFIX, $this->getParticipantExternalId()),
        ];
    }

    public function getParticipantExternalId(): string
    {
        return $this->getAttribute('Id');
    }

    public function getGSI1(): array
    {
        return [
            Entity::GSI1_PARTITION_KEY => $this->getSortKey(),
            Entity::GSI1_SORT_KEY => $this->getPartitionKey(),
        ];
    }

    public function getSK(): string
    {
        return '';
    }
}
