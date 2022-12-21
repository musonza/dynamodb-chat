<?php

namespace Musonza\LaravelDynamodbChat\Entities;
use Musonza\LaravelDynamodbChat\Helpers\Helpers;

class Message extends Entity
{
    const ENTITY_TYPE = 'MSG';
    const MESSAGE_KEY_PREFIX = 'MSG#%s';

    protected Participation $participation;
    protected string $message;
    protected bool $isSender;
    protected int $read = 0;
    protected array $gsi2 = [];

    public function __construct(Participation $participation, string $message, bool $isSender = false)
    {
        $this->participation = $participation;
        $this->message = $message;
        $this->isSender = $isSender;

        if ($isSender) {
            $this->setSender($participation, $participation);
        }
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
            'S' => sprintf(self::MESSAGE_KEY_PREFIX,  $this->getMessageId())
        ];
    }

    public function getGSI2(): array
    {
        return $this->gsi2;
    }

    public function setGSI2(array $gsi)
    {
        $this->gsi2 = $gsi;
    }

    public function getMessageId(): string
    {
        return Helpers::generateKSUID(now());
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
        return[
            ...$this->getPrimaryKey(),
            'Type' => ['S' => self::ENTITY_TYPE],
            ...$this->getGSI2(),
            'CreatedAt' => ['S' => now()->toISOString()],
            'Message' => ['S' => $this->getMessage()],
            'Read' => ['N' => $this->read],
        ];
    }

    public function toArray(): array
    {
        $item = $this->toItem();
        $arr = [];

        foreach ($item as $key => $value) {
            $arr[$key] = array_values($value)[0];
        }

        return $arr;
    }
}