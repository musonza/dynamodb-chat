<?php

namespace Musonza\LaravelDynamodbChat\Actions;

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
    }
}