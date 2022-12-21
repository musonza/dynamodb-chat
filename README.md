
## Creating a conversation of type private / public

```php

$participants = [$messageable1, $messageable2,..., $messageableN];

// Create a private conversation
$conversation = Chat::createConversation($participants)->makePrivate();

// Create a public conversation
$conversation = Chat::createConversation($participants)->makePrivate(false);

// Create a direct message

// Make direct conversation after creation
$conversation = Chat::createConversation($participants)->makeDirect();

// Specify intent for direct conversation before creation
$conversation = Chat::makeDirect()->createConversation($participants);
```

>Note: You will not be able to add additional participants to a direct conversation. Additionally you can't remove a participant from a direct conversation.

## Get a conversation by id

```php
$conversation = Chat::conversations()->getById($id);
```