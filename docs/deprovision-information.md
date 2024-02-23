# API for applications supporting deprovision

## Background

User Lifecycle iterates different clients and calls a the deprovision endpoint on those clients.

## Required API endpoints

The following calls are possible, all under a configurable base URL for your application. The passed user identifier is the collabPersonId, e.g. `urn:collab:person:example.org:jdoe`:
* GET `/deprovision/<collabPersonId>`: app returns all information it has stored for this user in the below JSON format, no actions done on the user.
* DELETE `/deprovision/<collabPersonId>`: app will deprovision everything it knows about this user, and return the JSON format below indicating success or failure and the last known data of the user right before removing it.
* DELETE `/deprovision/<collabPersonId>/dry-run`: as previous, but will then only go through the motions, not actually delete the user, and will return the JSON format as below.

## The contract
The following fields MUST be in the response

|Name | Description                                                                                            | Possible values|
|---- |--------------------------------------------------------------------------------------------------------|-------------------- |
| `status` | Was the retrieval action successful?                                                                   | `OK`, `FAILED` |
| `name` | The name of the application that returns the data.                                                     | - |
| `data` | The data the application stored of the user.                                                           | `array` |
| `message` | Optional: message(s) generated during the creation of the user data. Can be used for error reporting.  | - |


All data in the `data` field should be collected in an array, each entry of the
array should be a JSON object that has a name and a value.

| Name    | Description | Possible values |
|---------|-------------|--------------------|
| `name`  | The description of the user data that is specified in the `value` | any string value |
| `value` | The value of the user data | any string value |

### Example JSON happy flow

```json
{
  "status": "OK",
  "name": "EngineBlock",
  "data": [
    {
      "name": "name_id",
      "value": "urn:collab:person:idinstitution:john_doe"
    },
    {
      "name": "email",
      "value": "john_doe@institution.com"
    }
  ]
}
```

### Example JSON message

```json
{
    "status": "FAILED",
    "name": "EngineBlock",
    "data": [],
    "message": [
        "User identified by: urn:collab:person:idinstitution:john_doe was not found. Unable to provide deprovision data."
    ]
}
```
### JSON Schema

```json
{
  "$schema": "http://json-schema.org/draft-04/schema#",
  "title": "user-deprovision-data",
  "type": "object",
  "properties": {
    "status": {
      "type": "string",
      "enum": [
        "OK",
        "FAILED"
      ]
    },
    "name": {
      "type": "string"
    },
    "data": {
      "type": "array",
      "items": [
        {
          "type": "object",
          "properties": {
            "name": {
              "type": "string"
            },
            "value": {
              "type": "string"
            }
          },
          "required": [
            "name",
            "value"
          ]
        },
        {
          "type": "object",
          "properties": {
            "name": {
              "type": "string"
            },
            "value": {
              "type": "string"
            }
          },
          "required": [
            "name",
            "value"
          ]
        }
      ]
    },
    "message": {
      "type": "array"
    }
  },
  "required": [
    "status",
    "name",
    "data"
  ]
}
```
