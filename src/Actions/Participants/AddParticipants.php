<?php

namespace Musonza\LaravelDynamodbChat\Actions\Participants;

use Musonza\LaravelDynamodbChat\Actions\Action;
use Musonza\LaravelDynamodbChat\Actions\ConversationClient;
use Musonza\LaravelDynamodbChat\Configuration;
use Musonza\LaravelDynamodbChat\Entities\Conversation;
use Musonza\LaravelDynamodbChat\Entities\Participation;

class AddParticipants extends Action
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

        $this->batchSaveParticipants();

        $this->incrementParticipantCount();
    }

    private function batchSaveParticipants(): void
    {
        $batchItems = array_map(fn ($id) => $this->participation->newInstance([
            'ConversationId' => $this->conversation->getId(),
            'Id' => $id,
        ])->toArray(), $this->participantIds);

        foreach (array_chunk($batchItems, Configuration::getBatchLimit()) as $batch) {
            $this->saveItems($batch);
        }
    }

    private function incrementParticipantCount(): void
    {
        $params = [
            'TableName' => Configuration::getTableName(),
            'Key' => $this->conversation->getPrimaryKey(),
            'ExpressionAttributeValues' => [
                ':inc' => ['N' => count($this->participantIds)],
            ],
            'UpdateExpression' => 'SET ParticipantCount = ParticipantCount + :inc',
            'ReturnValues' => 'UPDATED_NEW',
        ];

        $this->getDynamoDbClient()->updateItem($params);
    }
}
