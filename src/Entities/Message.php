<?php

namespace Musonza\LaravelDynamodbChat\Entities;

use Aws\DynamoDb\Marshaler;
use JsonException;
use Musonza\LaravelDynamodbChat\Helpers\Helpers;

class Message extends Entity
{
    protected string $originalMsgId = '';

    protected string $entityType = 'MSG';

    protected string $keyPrefix = 'MSG#';

    public function setSender(Participation $participation, Participation $recipient, string $originalMsgId): self
    {
        $gsi2 = [
            'GSI2PK' => ['S' => Helpers::gs1skFromParticipantIdentifier($participation->getId())],
            'GSI2SK' => ['S' => Helpers::gs1skFromParticipantIdentifier($recipient->getId())],
        ];

        $this->setGSI2($gsi2);

        $this->setOriginalAndClonedMessageKeys($recipient, $originalMsgId, $this->getId());

        return $this;
    }

    private function setOriginalAndClonedMessageKeys(
        Participation $recipient,
        string $originalMsgId,
        string $recipientMsgId
    ): void {
        $this->originalMsgId = $originalMsgId;
        $this->setGSI1([
            Entity::GSI1_PARTITION_KEY => ['S' => Helpers::gsi1PKForMessage($recipient)],
            Entity::GSI1_SORT_KEY => ['S' => Helpers::gsi1SKForMessage($recipient, $recipientMsgId)],
        ]);
    }

    public function getPK(): string
    {
        return $this->attribute('ConversationId');
    }

    public function getPartitionKey(): array
    {
        return [
            'S' => $this->getPK(),
        ];
    }

    public function getSK(): string
    {
        return $this->getId();
    }

    public function getSortKey(): array
    {
        return [
            'S' => $this->getSK(),
        ];
    }

    /**
     * @throws JsonException
     */
    public function toItem(): array
    {
        $item = [
            ...$this->getPrimaryKey(),
            'Type' => ['S' => $this->getEntityType()],
            ...$this->getGSI1(),
            ...$this->getGSI2(),
            'CreatedAt' => ['S' => $this->attribute('CreatedAt')],
            'Message' => ['S' => $this->attribute('Message')],
            'Read' => ['N' => $this->attribute('Read')],
            'ReadCount' => ['N' => 0],
            'IsSender' => ['N' => $this->attribute('IsSender')],
            'ParentId' => ['S' => $this->originalMsgId],
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
            'SK' => $this->getSortKey(),
        ];
    }
}
