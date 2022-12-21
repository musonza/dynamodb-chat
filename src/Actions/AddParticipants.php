<?php

namespace Musonza\LaravelDynamodbChat\Actions;

use Aws\DynamoDb\DynamoDbClient;
use Musonza\LaravelDynamodbChat\ConfigurationManager;
use Musonza\LaravelDynamodbChat\Entities\Conversation;
use Musonza\LaravelDynamodbChat\Entities\Participation;

class AddParticipants extends Action
{
    protected Conversation $conversation;
    protected array $participantIds;

    public function __construct(Conversation $conversation, array $participantIds)
    {
        $this->conversation = $conversation;
        $this->participantIds = $participantIds;
    }

    public function execute()
    {
        $batchItems = [];
        $batchItemsCount = 0;

        foreach ($this->participantIds as $id) {
            if ($batchItemsCount == ConfigurationManager::getBatchLimit()) {
                $this->saveItems($batchItems);
                $batchItems = [];
                $batchItemsCount = 0;
            }

            $batchItems[] = [
                'PutRequest' => ['Item' => (new Participation($this->conversation, $id))->toItem()]
            ];

            $batchItemsCount++;
        }

        if (!empty($batchItems)) {
            $this->saveItems($batchItems);
        }
    }

    protected function saveItems(array $batchItems)
    {
        $client = app(DynamoDbClient::class);
        $client->batchWriteItem([
            'RequestItems' => [
                ConfigurationManager::getTableName() => [
                    ...$batchItems
                ]
            ]
        ]);
    }
}