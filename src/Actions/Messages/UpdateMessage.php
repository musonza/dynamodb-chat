<?php

namespace Musonza\LaravelDynamodbChat\Actions\Messages;

use Bego\Condition;
use Musonza\LaravelDynamodbChat\Actions\Action;
use Musonza\LaravelDynamodbChat\Entities\Conversation;
use Musonza\LaravelDynamodbChat\Entities\Participation;
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
        $item = $this->getTable()
            ->fetch($this->conversation->getPK(), "MSG#{$this->messageId}");

        if ($item->isEmpty()) {
            new NotFoundHttpException('Message not found');
        }

        foreach ($this->attributes as $attribute => $value) {
            if (!in_array($attribute, $this->allowedAttributes)) {
                continue;
            }
            $item->set($attribute, $value);
        }

        return $this->getTable()->update($item);
    }
}