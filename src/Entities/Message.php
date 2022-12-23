<?php

namespace Musonza\LaravelDynamodbChat\Entities;
use Aws\DynamoDb\Marshaler;
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
    protected array $gsi2 = [];
    protected string $messageId = '';

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

    /**
     * Participant to display message to
     * @return array
     */
    public function getGSI2(): array
    {
        return $this->gsi2;
    }

    /**
     * Message sender
     * @param array $gsi
     * @return void
     */
    public function setGSI2(array $gsi)
    {
        $this->gsi2 = $gsi;
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
        $marshaler = new Marshaler();
        $data = empty($this->data) ? [] : $marshaler->marshalJson(json_encode($this->data));

        return[
            ...$this->getPrimaryKey(),
            'Type' => ['S' => self::ENTITY_TYPE],
            ...$this->getGSI2(),
            'CreatedAt' => ['S' => now()->toISOString()],
            'Message' => ['S' => $this->getMessage()],
            'Data' => ['S' => $data],
            'Read' => ['N' => $this->read],
            'IsSender' => ['N' => $this->isSender],
        ];
    }

    public function setData(array $data)
    {
        $this->data = $data;
        return $this;
    }
}