<?php

namespace Musonza\LaravelDynamodbChat\Entities;

class Participation extends Entity
{
    public const PARTICIPATION_PK_PREFIX = 'PARTICIPANT#%s';

    protected string $entityType = 'PARTICIPATION';

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
            'S' => sprintf(self::PARTICIPATION_PK_PREFIX, $this->getParticipantExternalId()),
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

    public function getPK(): string
    {
        return $this->getAttribute('ConversationId');
    }

    public function getGSI1(): array
    {
        return [
            Entity::GSI1_PARTITION_KEY => $this->getSortKey(),
            Entity::GSI1_SORT_KEY => $this->getPartitionKey(),
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

    public function getSK(): string
    {
        return '';
    }
}
