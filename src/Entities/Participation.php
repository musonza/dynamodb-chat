<?php

namespace Musonza\LaravelDynamodbChat\Entities;

class Participation extends Entity
{
    public const PARTICIPATION_PK_PREFIX = 'PARTICIPANT#%s';

    protected string $entityType = 'PARTICIPATION';

    public function getPK(): string
    {
        return $this->attribute('ConversationId');
    }

    public function toItem(): array
    {
        return[
            ...$this->getPrimaryKey(),
            'Type' => ['S' => $this->getEntityType()],
            ...$this->getGSI1(),
            'ParticipantId' => ['S' => $this->getId()],
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
     * @return array<string, string>
     */
    public function getPartitionKey(): array
    {
        return [
            'S' => $this->getPK(),
        ];
    }

    public function getSortKey(): array
    {
        return [
            'S' => sprintf(self::PARTICIPATION_PK_PREFIX, $this->getId()),
        ];
    }

    public function getId(): string
    {
        return $this->attribute('Id');
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
