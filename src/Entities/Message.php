<?php

namespace Musonza\LaravelDynamodbChat\Entities;
use Aws\DynamoDb\Marshaler;
use Bego\Component\Resultset;
use Musonza\LaravelDynamodbChat\Helpers\Helpers;

class Message extends Entity implements Contract
{
    const ENTITY_TYPE = 'MSG';

    protected Participation $participation;
    protected array $gsi1 = [];
    protected array $gsi2 = [];
    protected string $originalMsgId = '';

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

    public function getGSI1(): array
    {
        return $this->gsi1;
    }

    public function getGSI2(): array
    {
        return $this->gsi2;
    }

    public function setGSI2(array $gsi)
    {
        $this->gsi2 = $gsi;
    }

    public function setGSI1(array $gsi)
    {
        $this->gsi1 = $gsi;
    }

    public function getId(): string
    {
        return $this->getAttribute('Id');
    }

    public function getMessage(): string
    {
        return $this->getAttribute('Message');
    }

    public function toItem(): array
    {
        $item = [
            ...$this->getPrimaryKey(),
            'Type' => ['S' => self::ENTITY_TYPE],
            ...$this->getGSI1(),
            ...$this->getGSI2(),
            'CreatedAt' => ['S' => now()->toISOString()],
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

    public function setResultSet(Resultset $resultset)
    {
        // TODO: Implement setResultSet() method.
    }

    public function getResultSet(): ?Resultset
    {
        // TODO: Implement getResultSet() method.
    }

    public function getKeyPrefix(): string
    {
        return 'MSG#';
    }
}