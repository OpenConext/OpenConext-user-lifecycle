# User Data JSON structure

## Background
User Lifecycle iterates different clients and calls a `deprovision` or `user-information` (deprovision dry run) 
on those clients. In order to come back with a structured set of data for the logs we require the following
JSON structure for the user data that is returned to User Lifecycle.

### The contract
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
