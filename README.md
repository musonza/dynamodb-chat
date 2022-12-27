## Create a Conversation

```php
$conversation = Chat::conversation()
    ->setSubject('Group 1')
    ->setAttributes([
        'Description' => 'My description',
    ])
    ->create();
```

## Create a direct conversation

```php
$conversation = Chat::conversation()
    ->setSubject('Conversation')
    ->setParticipants(['johnID', 'janeID'])
    ->setIsDirect(true)
    ->create();
```

>Note: You will not be able to add additional participants to a direct conversation. Additionally, you can't remove a participant from a direct conversation.

## Get a conversation by id

```php
$conversation = Chat::getConversationById($id);
```

## Add participants to a Conversation

```php
Chat::addParticipants($conversationId, [
    'jamesID',
    'janeID',
    'johnID'
]);
```

## Remove participants from a Conversation

```php
Chat::deleteParticipants(
    $conversation->getId(), 
    ['user1', 'user2']
);
```

## Update Conversation details

```php
$updated = $this->chat->conversation($conversationId)
    ->setAttributes([
        'Subject' => $newSubject,
        'Description' => $description,
        // ... unchanged data
    ])
    ->update();
```

## Send Message

```php
Chat::messaging($conversationId)
    ->message($senderId, 'Hello')
    ->send();
```

## Send Message with additional details

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