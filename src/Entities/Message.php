<?php

namespace Musonza\LaravelDynamodbChat\Entities;
use Aws\DynamoDb\Marshaler;
use Musonza\LaravelDynamodbChat\Helpers\Helpers;

class Message extends Entity
{
    const ENTITY_TYPE = 'MSG';
    const MESSAGE_KEY_PREFIX = 'MSG#';

    protected Participation $participation;
    protected string $message;
    protected array $data = [];
    protected bool $isSender;
    protected int $read = 0;
    protected array $gsi1 = [];
    protected array $gsi2 = [];
    protected string $messageId = '';
    protected string $originalMsgId = '';

    public function __construct(Participation $participation, string $message, bool $isSender = false)
    {
        $this->participation = $participation;
        $this->message = $message;
        $this->isSender = $isSender;

        if ($isSender) {
            $this->setSender($participation, $participation);
        }

        if (!$this->messageId) {
            $this->messageId = Helpers::generateId(self::MESSAGE_KEY_PREFIX, now());
        }
    }

    public static function createFrom(Participation $participant, string $messageId): Message
    {
        return (new static($participant, ''))->setId($messageId);
    }

    public function setSender(Participation $participation, Participation $recipient): self
    {
        $gsi2 = [
            'GSI2PK' => ['S' => Helpers::gs1skFromParticipantIdentifier($participation->getParticipantExternalId())],
            'GSI2SK' => ['S' => Helpers::gs1skFromParticipantIdentifier($recipient->getParticipantExternalId())],
        ];

        $this->setGSI2($gsi2);

        return $this;
    }

    public function setOriginalAndClonedMessageKeys(string $originalMsgId, string $recipientMsgId): self
    {
        $this->originalMsgId = $originalMsgId;
        $gsi1sk = Helpers::gs1skFromParticipantIdentifier($this->participation->getParticipantExternalId()) . $recipientMsgId;
        $gsi1 = [
            'GSI1PK' => ['S' => $this->participation->getPK()],
            'GSI1SK' => ['S' => $gsi1sk]
        ];

        // "PARTICIPANT#{$this->participation->getParticipantExternalId()}"

        $this->setGSI1($gsi1);

        return $this;
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
        return $this->participation->getPartitionKey();
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
        return $this->messageId;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setRead(bool $isRead): self
    {
        $this->read = $isRead;
        return $this;
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
            'Read' => ['N' => $this->read],
            'ReadCount' => ['N' => 0],
            'IsSender' => ['N' => $this->isSender],
            'ParentId' => ['S' => $this->originalMsgId ?? $this->getId()],
        ];

        $data = empty($this->data) ? [] : (new Marshaler())->marshalJson(json_encode($this->data));

        if (!empty($data)) {
            $item['Data'] = ['S' => $data];
        }

        return $item;
    }

    public function setData(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    public function setId(string $messageId): self
    {
        $this->messageId = $messageId;
        return $this;
    }
}