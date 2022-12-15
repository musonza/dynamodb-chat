<?php

namespace Musonza\LaravelDynamodbChat\Repositories;

use Aws\DynamoDb\DynamoDbClient;
use Musonza\LaravelDynamodbChat\ConfigurationManager;
use Musonza\LaravelDynamodbChat\Entities\Conversation;

class ConversationRepository extends BaseRepository
{
    protected Conversation $conversation;

    public function __construct(Conversation $conversation)
    {
        $this->conversation = $conversation;
    }

    public function setSubject(string $subject): self
    {
        $this->conversation->setSubject($subject);
        return $this;
    }

    public function save()
    {
        $this->getClient()->putItem(array(
            'TableName' => ConfigurationManager::getTableName(),
            'Item' => $this->conversation->toItem(),
        ));

        return $this;
    }

    public function getConversation(): Conversation
    {
        return $this->conversation;
    }

    public function participation(): ConversationParticipationRepository
    {
        return new ConversationParticipationRepository($this->conversation);
    }
}