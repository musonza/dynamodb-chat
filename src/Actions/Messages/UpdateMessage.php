<?php

namespace Musonza\LaravelDynamodbChat\Actions\Messages;

use Bego\Condition;
use Musonza\LaravelDynamodbChat\Actions\Action;
use Musonza\LaravelDynamodbChat\Entities\Conversation;
use Musonza\LaravelDynamodbChat\Entities\Participation;
use Musonza\LaravelDynamodbChat\Exceptions\ResourceNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UpdateMessage extends Action
{
    protected Conversation $conversation;
    protected Participation $participation;
    protected string $messageId;
    protected array $attributes;
    protected array $allowedAttributes = [
        'Read',
    ];

    public function __construct(Conversation $conversation, Participation $participation, string $messageId, array $attributes)
    {
        $this->conversation = $conversation;
        $this->participation = $participation;
        $this->messageId = $messageId;
        $this->attributes = $attributes;
    }

    public function execute(): bool
    {
        // TODO resolve IDs cleanly
        $gsi1sk = "PARTICIPANT#{$this->participation->getParticipantIdentifier()}#MSG{$this->messageId}";

        $item = $this->getTable()
            ->query('GSI1')
            ->key($this->conversation->getPK())
            ->condition(Condition::attribute('GSI1SK')->beginsWith($gsi1sk))
            ->fetch()
            ->first();

        if (is_null($item)) {
            throw new ResourceNotFoundException('Message not found');
        }

        foreach ($this->attributes as $attribute => $value) {
            if (!in_array($attribute, $this->allowedAttributes)) {
                continue;
            }
            $item->set($attribute, $value);
        }

        $updated = $this->getTable()->update($item);

        // TODO this logic can be moved to an event listener
        // update ReadCount on parent message
        if ($updated && isset($this->attributes['Read'])) {
            $parentMessage = $this->getTable()
                ->query()
                ->key($this->conversation->getPK())
                ->condition(Condition::attribute('SK')->eq("MSG#{$item->attribute('ParentId')}"))
                ->fetch()
                ->first();

            // Possibly a parent message was deleted
            if (is_null($parentMessage)) {
                return $updated;
            }

            $parentMessage->set('ReadCount', $parentMessage->attribute('ReadCount') + 1);
            $this->getTable()->update($parentMessage);
        }

        return (bool) $updated;
    }
}