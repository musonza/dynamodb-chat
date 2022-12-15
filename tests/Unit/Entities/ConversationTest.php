<?php

namespace Musonza\LaravelDynamodbChat\Tests\Unit\Entities;

use Musonza\LaravelDynamodbChat\Chat;
use Musonza\LaravelDynamodbChat\Console\Debug\ConversationCommand;
use Musonza\LaravelDynamodbChat\Entities\Participation;
use Musonza\LaravelDynamodbChat\Tests\TestCase;
use Musonza\LaravelDynamodbChat\Entities\Conversation;

class ConversationTest extends TestCase
{
    protected Conversation $conversation;
    protected Chat $chat;

    public function setUp(): void
    {
        parent::setUp();
        $this->chat = app(Chat::class);
        $this->conversation = app(Conversation::class);
    }

    public function testGetPrimaryKey()
    {
        $conversationRepo = $this->chat->createConversation()
            ->setSubject('Conversation title')
            ->save();


        $conversationRepo
            ->participation()
            ->addParticipants([
                'tinashe',
                'tashinga',
                'jojo'
            ]);

//        dd($participation->toItem());


//        $conversationDebugger = new ConversationCommand();
//        $conversationDebugger->createConversation();
    }
}
