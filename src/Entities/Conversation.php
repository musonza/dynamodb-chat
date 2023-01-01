<?php

namespace Musonza\LaravelDynamodbChat\Entities;

use Chat;
use Illuminate\Support\Carbon;

class Conversation extends Entity implements Contract
{
    const ENTITY_TYPE_DIRECT = 'CONVERSATION_DIRECT';
    protected string $subject = 'Conversation';
    protected Carbon $createdAt;
    /**
     * Specifies whether the conversation is private or public.
     * @var bool
     */
    protected bool $isPrivate = false;
    protected string $entityType = 'CONVERSATION';

    public function isDirect(): bool
    {
        return $this->getAttribute('IsDirect', false);
    }

    public function getSubject(): ?string
    {
        return $this->getAttribute('Subject');
    }

    public function getPrimaryKey(): array
    {
        return [
            'PK' => $this->getPartitionKey(),
            'SK' => $this->getPartitionKey(),
        ];
    }

    public function getPartitionKey(): array
    {
        return [
            'S' => $this->getId()
        ];
    }

    public function toItem(): array
    {
        return[
            ...$this->getPrimaryKey(),
            'Type' => ['S' => $this->getEntityType()],
            'Subject' => ['S' => $this->getAttribute('Subject')],
            'ParticipantCount' => ['N' => 0],
            'CreatedAt' => ['S' => $this->getAttribute('CreatedAt')],
        ];
    }

    public function getPK(): string
    {
        return array_values($this->getPartitionKey())[0];
    }

    public function getSK(): string
    {
        return $this->getPK();
    }
}