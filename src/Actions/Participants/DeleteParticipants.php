<?php

namespace Musonza\LaravelDynamodbChat\Actions\Participants;

use Aws\DynamoDb\DynamoDbClient;
use Aws\Result;
use Musonza\LaravelDynamodbChat\Actions\Action;
use Musonza\LaravelDynamodbChat\ConfigurationManager;
use Musonza\LaravelDynamodbChat\Entities\Conversation;
use Musonza\LaravelDynamodbChat\Entities\Participation;

class DeleteParticipants extends Action
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
                $this->deleteItems($batchItems);
                $batchItems = [];
                $batchItemsCount = 0;
            }

            $batchItems[] = [
                'DeleteRequest' => ['Key' => (new Participation($this->conversation, $id))->getPrimaryKey()]
            ];
            $batchItemsCount++;
        }

        if (!empty($batchItems)) {
            $this->deleteItems($batchItems);
        }

        $this->decrement($this->conversation, count($this->participantIds));
    }

    protected function decrement(Conversation $conversation, int $count): Result
    {
        /** @var DynamoDbClient $client */
        $client = app(DynamoDbClient::class);
        $params = [
            'TableName' => ConfigurationManager::getTableName(),
            'Key' => $conversation->getPrimaryKey(),
            'ExpressionAttributeValues' => [
                ':inc' => ['N' => $count]
            ],
            'UpdateExpression' => 'SET ParticipantCount = ParticipantCount - :inc',
            'ReturnValues' => 'UPDATED_NEW'
        ];

        return $client->updateItem($params);
    }
}