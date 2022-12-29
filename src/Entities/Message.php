<?php

namespace Musonza\LaravelDynamodbChat\Entities;
use Aws\DynamoDb\Marshaler;
use Musonza\LaravelDynamodbChat\Actions\Messages\CreateMessage;
use Musonza\LaravelDynamodbChat\Helpers\Helpers;

class Message extends Entity
{
    const ENTITY_TYPE = 'MSG';
    const MESSAGE_KEY_PREFIX = 'MSG#%s';

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
            $this->messageId = Helpers::generateKSUID(now());
        }
    }

    public static function createFrom(Participation $participant, string $messageId): Message
    {
        return (new static($participant, ''))->setId($messageId);
    }

    public function setSender(Participation $participation, Participation $recipient): self
    {
        $gsi2 = [
            'GSI2PK' => ['S' => "PARTICIPANT#{$participation->getParticipantIdentifier()}"],
            'GSI2SK' => ['S' => "PARTICIPANT#{$recipient->getParticipantIdentifier()}"]
        ];

        $this->setGSI2($gsi2);

        return $this;
    }

    public function setOriginalAndClonedMessageKeys(string $originalMsgId, string $recipientMsgId): self
    {
        $this->originalMsgId = $originalMsgId;

        $gsi1 = [
            'GSI1PK' => ['S' => "{$this->participation->getPK()}"],
            'GSI1SK' => ['S' => "PARTICIPANT#{$this->participation->getParticipantIdentifier()}#MSG{$recipientMsgId}"]
        ];

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
            'S' => sprintf(self::MESSAGE_KEY_PREFIX,  $this->getId())
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

        $marshaler = new Marshaler();
        $data = empty($this->data) ? [] : $marshaler->marshalJson(json_encode($this->data));

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