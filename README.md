
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
$conversation = Chat::conversations()->getById($id);
```