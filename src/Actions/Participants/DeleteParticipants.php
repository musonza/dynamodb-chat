<?php

namespace Musonza\LaravelDynamodbChat\Actions\Participants;

use Aws\DynamoDb\DynamoDbClient;
use Musonza\LaravelDynamodbChat\Actions\Action;
use Musonza\LaravelDynamodbChat\Actions\ConversationClient;
use Musonza\LaravelDynamodbChat\Configuration;
use Musonza\LaravelDynamodbChat\Entities\Conversation;
use Musonza\LaravelDynamodbChat\Entities\Participation;

class DeleteParticipants extends Action
{
    protected ConversationClient $conversationClient;

    protected readonly Conversation $conversation;

    protected readonly Participation $participation;

    protected readonly array $participantIds;

    public function __construct(
        ConversationClient $conversationClient,
        Conversation $conversation,
        Participation $participation,
        array $participantIds
    ) {
        $this->conversationClient = $conversationClient;
        $this->conversation = $conversation;
        $this->participation = $participation;
        $this->participantIds = $participantIds;
    }

    public function execute(): void
    {
        $item = $this->conversationClient->conversationToItem($this->conversation->getId());

        $this->restrictModifyingParticipantsInDirectConversation($item);

        $this->batchDeleteParticipants();

        $this->decrementParticipantCount(count($this->participantIds));
    }

    private function batchDeleteParticipants(): void
    {
        $batchItems = [];
        $batchItemsCount = 0;

        foreach ($this->participantIds as $id) {
            if ($batchItemsCount == Configuration::getBatchLimit()) {
                $this->deleteItems($batchItems);
                $batchItems = [];
                $batchItemsCount = 0;
            }

            $participation = $this->participation->newInstance([
                'ConversationId' => $this->conversation->getId(),
                'Id' => $id,
            ]);

            $batchItems[] = [
                'DeleteRequest' => ['Key' => $participation->getPrimaryKey()],
            ];
            $batchItemsCount++;
        }

        if (! empty($batchItems)) {
            $this->deleteItems($batchItems);
        }
    }

    private function decrementParticipantCount(int $count): void
    {
        /** @var DynamoDbClient $client */
        $client = app(DynamoDbClient::class);
        $params = [
            'TableName' => Configuration::getTableName(),
            'Key' => $this->conversation->getPrimaryKey(),
            'ExpressionAttributeValues' => [
                ':inc' => ['N' => $count],
            ],
            'UpdateExpression' => 'SET ParticipantCount = ParticipantCount - :inc',
            'ReturnValues' => 'UPDATED_NEW',
        ];

        $client->updateItem($params);
    }
}
