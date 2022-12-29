# Laravel DynamoDB Chat Library

## Table of Contents

<details><summary>Click to expand</summary>

- [Introduction](#introduction)
- [Installation](#installation)
- [Usage](#usage)
  - [Conversations](#conversations--aka-rooms-groups-etc-)
    - [Create a Conversation](#create-a-conversation)
    - [Create a direct conversation](#create-a-direct-conversation)
    - [Get / resolve a direct Conversation](#get--resolve-a-direct-conversation)
    - [Add participants to a Conversation](#add-participants-to-a-conversation)
    - [Remove participants from a Conversation](#remove-participants-from-a-conversation)
    - [Update Conversation details](#update-conversation-details)
  - [Messages](#messages)
    - [Send Message](#send-message)
    - [Send Message with additional details](#send-message-with-additional-details)
    - [Delete Message](#delete-message)
    - [Mark Message as read](#mark-message-as-read)
- [DynamoDB access patterns](#dynamodb-access-patterns)
</details>

## Introduction

This is a simple chat library for DynamoDB. It is designed to be used with the AWS SDK for PHP. The package follows a single database design for DynamoDB.
You can create a Chat application for your multiple entities.

## Installation

## Usage

### Conversations (aka Rooms, Groups etc)

#### Create a Conversation

```php
$conversation = Chat::conversation()
    ->setSubject('Group 1')
    ->setAttributes([
        'Description' => 'My description',
    ])
    ->create();
```

#### Create a direct conversation

```php
$conversation = Chat::conversation()
    ->setSubject('Conversation')
    ->setParticipants(['johnID', 'janeID'])
    ->setIsDirect(true)
    ->create();
```

>Note: You will not be able to add additional participants to a direct conversation. Additionally, you can't remove a participant from a direct conversation.

#### Get / resolve a direct Conversation

You may want to get a direct conversation between two users. This is useful if you want to send a message to a user, but you don't know if they have a conversation with you already.

```php
$conversation = Chat::conversation()
    ->getDirectConversation($participant1Id, $participant2Id);
```

#### Add participants to a Conversation

You can add participants to a conversation at any time. However, they will not be able to see messages sent before they were added.

```php
Chat::addParticipants($conversationId, [
    'jamesID',
    'janeID',
    'johnID'
]);
```

#### Remove participants from a Conversation

You can remove participants from a conversation at any time. However, they will still be able to see messages sent before they were removed. Otherwise, they will not be able to see any new messages or send messages.

```php
Chat::deleteParticipants(
    $conversationId, 
    ['user1', 'user2']
);
```

#### Update Conversation details

```php
$updated = Chat::conversation($conversationId)
    ->setAttributes([
        'Subject' => $newSubject,
        'Description' => $description,
        // ... unchanged data
    ])
    ->update();
```

### Messages

#### Send Message

```php
Chat::messaging($conversationId)
    ->message($senderId, 'Hello')
    ->send();
```

#### Send Message with additional details

You can send a message with additional details. This is useful if you want to send a message with a link to a resource, or a file.

```php
$data = [
    'images' => [
        [
            'file_name' => 'post_image.jpg',
            'file_url' => 'http://example.com/post_img.jpg',
        ],
        [
            'file_name' => 'post_image2.jpg',
            'file_url' => 'http://example.com/post_img2.jpg',
        ],
    ]
];

$message = Chat::messaging($conversationId)
    ->message($senderId, 'Hello', $data)
    ->send();
```

#### Delete Message

Deleting a message will remove it from the conversation for the specified user. The message will still be visible to other participants.

```php
Chat::messaging($conversationId, $messageId)
    ->delete($recipientOwnerId);
```

#### Mark Message as read

Marking a message as read will remove the unread indicator for the specified user. The message will still be visible to other participants.

```php  
Chat::messaging($conversationId, $messageId)
    ->markAsRead($recipientOrOwnerId);
```

### DynamoDB access patterns

| Entity       |        PK         |                SK |     |
|--------------|:-----------------:|------------------:|-----|
| Conversation | CONVERSATION#{ID} | CONVERSATION#{ID} |     |
| Participant  | CONVERSATION#{ID} |  PARTICIPANT#{ID} |     |
| Message      | CONVERSATION#{ID} |          MSG#{ID} |     |