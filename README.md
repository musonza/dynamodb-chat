## Create a Conversation

```php
$conversation = $this->chat->conversation()
    ->setSubject('Group 1')
    ->setAttributes([
        'Description' => 'My description',
    ])
    ->create();
```

## Creating a direct conversation

```php
Chat::conversation()
    ->setSubject('Conversation 1')
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
Chat::deleteParticipants($conversation->getId(), ['user1', 'user2']);
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

## Create a direct conversation

```php
$conversation = $this->chat->conversation()
    ->setSubject('Conversation')
    ->setParticipants(['johnID', 'janeID'])
    ->setIsDirect(true)
    ->create();
```