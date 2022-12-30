## Entities

| Entity       |        PK         |                SK |
|--------------|:-----------------:|------------------:|
| Conversation | CONVERSATION#{ID} | CONVERSATION#{ID} |
| Participant  | CONVERSATION#{ID} |  PARTICIPANT#{ID} |
| Message      | CONVERSATION#{ID} |          MSG#{ID} |

### GSI1

| Entity       |      GSI1PK       |            GSI1SK |
|--------------|:-----------------:|------------------:|
| Conversation |                   |                   |
| Participant  | PARTICIPANT#{ID}  | CONVERSATION#{ID} |
| Message      | CONVERSATION#{ID} |          MSG#{ID} |

### GSI2

| Entity       |         GSI2PK         |                    GSI2SK |
|--------------|:----------------------:|--------------------------:|
| Conversation |                        |                           |
| Participant  |                        |                           |
| Message      | PARTICIPANT#{SenderId} | PARTICIPANT#{RecipientId} |

## Access patterns

| Access Pattern       | Index | Parameters | Notes |
|----------------------|:-----:|-----------:|-------|
| Create Conversation  |       |            |       |
| Archive Conversation |       |            |       |
| Delete Conversation  |       |            |       |  
| Add Participants     |       |            |       |
| Delete Participants  |       |            |       |
| Send Message         |       |            |       |
| Delete Message       |       |            |       |
| Get messages         |       |            |       |
