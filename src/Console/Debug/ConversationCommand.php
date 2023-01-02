<?php

namespace Musonza\LaravelDynamodbChat\Console\Debug;

use Illuminate\Console\Command;
use Musonza\LaravelDynamodbChat\Entities\Conversation;

class ConversationCommand extends Command
{
    protected $signature = 'dynamo:chat:conversation';

    public function handle(): void
    {
        // Create conversation
        // Get conversation by ID
        // Delete conversation by ID
    }
}