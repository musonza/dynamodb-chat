<?php

namespace Musonza\LaravelDynamodbChat\Entities;
use Aws\DynamoDb\Marshaler;
use Musonza\LaravelDynamodbChat\Helpers\Helpers;

class Message extends Entity implements Contract
{
    protected Participation $participation;
    protected string $originalMsgId = '';
    protected string $entityType = 'MSG';
    protected string $keyPrefix = 'MSG#';

    public function setSender(Participation $participation, Participation $recipient, string $originalMsgId): self
    {
        $gsi2 = [
            'GSI2PK' => ['S' => Helpers::gs1skFromParticipantIdentifier($participation->getParticipantExternalId())],
            'GSI2SK' => ['S' => Helpers::gs1skFromParticipantIdentifier($recipient->getParticipantExternalId())],
        ];

        $this->setGSI2($gsi2);

        $this->setOriginalAndClonedMessageKeys($recipient, $originalMsgId, $this->getId());

        return $this;
    }

    private function setOriginalAndClonedMessageKeys(
        Participation $recipient,
        string $originalMsgId,
        string $recipientMsgId
    ) {
        $this->originalMsgId = $originalMsgId;
        $this->setGSI1([
            Entity::GLOBAL_INDEX1_PK => ['S' => Helpers::gsi1PKForMessage($recipient)],
            Entity::GLOBAL_INDEX1_SK => ['S' => Helpers::gsi1SKForMessage($recipient, $recipientMsgId)]
        ]);
    }

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
            'S' => $this->getAttribute('ConversationId')
        ];
    }

    public function getSortKey(): array
    {
        return [
            'S' => $this->getId()
        ];
    }

    public function getPK(): string
    {
        return array_values($this->getPartitionKey())[0];
    }

    public function getSK(): string
    {
        return array_values($this->getSortKey())[0];
    }

    public function getMessage(): string
    {
        return $this->getAttribute('Message');
    }

    public function toItem(): array
    {
        $item = [
            ...$this->getPrimaryKey(),
            'Type' => ['S' => $this->getEntityType()],
            ...$this->getGSI1(),
            ...$this->getGSI2(),
            'CreatedAt' => ['S' => $this->getAttribute('CreatedAt')],
            'Message' => ['S' => $this->getMessage()],
            'Read' => ['N' => $this->getAttribute('Read')],
            'ReadCount' => ['N' => 0],
            'IsSender' => ['N' => $this->getAttribute('IsSender')],
            'ParentId' => ['S' => $this->originalMsgId ?? $this->getId()],
        ];

        $data = empty($this->getAttribute('Data'))
            ? []
            : (new Marshaler())->marshalJson(json_encode($this->getAttribute('Data')));

        if (!empty($data)) {
            $item['Data'] = ['S' => $data];
        }

        return $item;
    }
}