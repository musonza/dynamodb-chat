<?php

namespace Musonza\LaravelDynamodbChat\Console\Debug;

use Illuminate\Console\Command;
use Musonza\LaravelDynamodbChat\Entities\Conversation;

class ConversationCommand extends Command
{
    protected $signature = 'dynamo:chat:conversation';

    public function handle()
    {
        // Create conversation
        // Get conversation by ID
        // Delete conversation by ID
    }

    public function createConversation()
    {
        $subject = 'Group 1';
        /** @var Conversation $conversation */
        $conversation = app(Conversation::class);
        $conversation->setSubject($subject);

        dump($conversation->toItem());
    }
}