<?php

namespace Musonza\LaravelDynamodbChat\Entities;

use Aws\DynamoDb\Marshaler;

class Conversation extends Entity
{
    protected string $subject = 'Conversation';

    protected string $keyPrefix = 'CONVERSATION#';

    /**
     * Specifies whether the conversation is private or public.
     *
     * @var bool
     */
    protected bool $isPrivate = false;

    protected string $entityType = 'CONVERSATION';

    public function isDirect(): bool
    {
        return $this->attribute('IsDirect', false);
    }

    public function getSubject(): ?string
    {
        return $this->attribute('Subject');
    }

    public function toItem(): array
    {
        $item = [
            ...$this->getPrimaryKey(),
            'Type' => ['S' => $this->getEntityType()],
            'Subject' => ['S' => $this->attribute('Subject')],
            'ParticipantCount' => ['N' => 0],
            'CreatedAt' => ['S' => $this->attribute('CreatedAt')],
        ];

        $data = empty($this->attribute('Data'))
            ? []
            : (new Marshaler())->marshalJson(json_encode($this->attribute('Data'), JSON_THROW_ON_ERROR));

        if (! empty($data)) {
            $item['Data'] = ['S' => $data];
        }

        return $item;
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
            'S' => $this->getId(),
        ];
    }

    public function getSK(): string
    {
        return $this->getPK();
    }

    public function getPK(): string
    {
        return $this->getId();
    }
}
