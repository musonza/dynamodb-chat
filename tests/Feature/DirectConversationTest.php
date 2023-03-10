<?php

namespace Musonza\LaravelDynamodbChat\Tests\Feature;

use Musonza\LaravelDynamodbChat\Entities\Conversation;
use Musonza\LaravelDynamodbChat\Exceptions\ConversationExistsException;
use Musonza\LaravelDynamodbChat\Exceptions\InvalidConversationParticipants;
use Musonza\LaravelDynamodbChat\Exceptions\ResourceNotFoundException;
use Musonza\LaravelDynamodbChat\Tests\TestCase;

class DirectConversationTest extends TestCase
{
    public function testDirectConversationNotDuplicated()
    {
        $this->expectException(ConversationExistsException::class);

        $this->chat->conversation()
            ->setAttributes([
                'Subject' => 'Conversation 1',
            ])
            ->setParticipants(['john', 'jane'])
            ->setIsDirect(true)
            ->create();

        $this->chat->conversation()
            ->setAttributes([
                'Subject' => 'Conversation 2',
            ])
            ->setParticipants(['jane', 'john'])
            ->setIsDirect(true)
            ->create();
    }

    public function testDirectConversationRequiresTwoParticipants()
    {
        $this->expectException(InvalidConversationParticipants::class);
        $this->expectExceptionMessage(InvalidConversationParticipants::REQUIRED_PARTICIPANT_COUNT);

        $this->chat->conversation()
            ->setAttributes([
                'Subject' => 'Conversation',
            ])
            ->setParticipants(['john', 'jane', 'doe'])
            ->setIsDirect(true)
            ->create();
    }

    public function testPreventsRemovingParticipantsFromDirectConversation()
    {
        $this->expectException(InvalidConversationParticipants::class);
        $this->expectExceptionMessage(InvalidConversationParticipants::PARTICIPANTS_IMMUTABLE);

        $conversation = $this->chat->conversation()
            ->setAttributes([
                'Subject' => 'Conversation',
            ])
            ->setParticipants(['john', 'jane'])
            ->setIsDirect(true)
            ->create();

        $this->chat->deleteParticipants($conversation->getId(), ['jane']);
    }

    public function testPreventsAddingParticipantsToDirectConversation()
    {
        $this->expectException(InvalidConversationParticipants::class);
        $this->expectExceptionMessage(InvalidConversationParticipants::PARTICIPANTS_IMMUTABLE);

        $conversation = $this->chat->conversation()
            ->setAttributes([
                'Subject' => 'Conversation',
            ])
            ->setParticipants(['john', 'jane'])
            ->setIsDirect(true)
            ->create();

        $this->chat->addParticipants($conversation->getId(), ['doe']);
    }

    public function testGetNonExistentDirectConversationDetails()
    {
        $this->expectException(ResourceNotFoundException::class);

        $this->chat->conversation()
            ->getDirectConversation('jane', 'john');
    }

    public function testGetExistingDirectConversationDetails()
    {
        $this->chat->conversation()
            ->setAttributes([
                'Subject' => 'Conversation 1',
            ])
            ->setParticipants(['john', 'jane'])
            ->setIsDirect(true)
            ->create();

        $conversation = $this->chat->conversation()
            ->getDirectConversation('jane', 'john');

        $this->assertInstanceOf(Conversation::class, $conversation);
        $this->assertEquals(1, $conversation->getResultSet()->count());
    }
}
