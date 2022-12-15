<?php

namespace Musonza\LaravelDynamodbChat\Tests\Unit\Entities;

use Musonza\LaravelDynamodbChat\Chat;
use Musonza\LaravelDynamodbChat\Console\Debug\ConversationCommand;
use Musonza\LaravelDynamodbChat\Console\InstallCommand;
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

        /** @var InstallCommand $i */
        $i = app(InstallCommand::class);
//        $i->handle();
//        dd();

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

        $this->chat->addParticipants(
            $conversationRepo->getConversation()->getConversationId(),
            ['coley', 'amai']
        );

//        dd($participation->toItem());


//        $conversationDebugger = new ConversationCommand();
//        $conversationDebugger->createConversation();
    }
}
