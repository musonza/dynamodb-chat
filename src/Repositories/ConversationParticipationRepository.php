<?php

namespace Musonza\LaravelDynamodbChat\Repositories;

use Musonza\LaravelDynamodbChat\ConfigurationManager;
use Musonza\LaravelDynamodbChat\Entities\Conversation;
use Musonza\LaravelDynamodbChat\Entities\Participation;

class ConversationParticipationRepository extends BaseRepository
{
    protected Conversation $conversation;
    public function __construct(Conversation $conversation)
    {
        $this->conversation = $conversation;
    }

    public function addParticipants(array $participants): void
    {
        $batchItems = [];

        foreach ($participants as $participant) {
            $batchItems[] = [
                'PutRequest' => ['Item' => (new Participation($this->conversation, $participant))->toItem()]
            ];
        }

        $this->getClient()->batchWriteItem([
            'RequestItems' => [
                ConfigurationManager::getTableName() => [
                    ...$batchItems
                ]
            ]
        ]);
    }
}