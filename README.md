<a href="https://openconext.org/">
    <img src="https://openconext.org/wp-content/uploads/2016/11/openconext_logo-med.png" alt="OpenConext"
         align="right" width="300" />
</a>

[![Build status](https://img.shields.io/travis/OpenConext/user-lifecycle.svg)](https://travis-ci.org/OpenConext/user-lifecycle)
[![License](https://img.shields.io/github/license/OpenConext/user-lifecycle.svg)](https://github.com/OpenConext/user-lifecycle/blob/master/LICENSE)

# OpenConext User Lifecycle
Deprovision users within the OpenConext platform. The User Lifecycle application is where the last login information of OpenConext suite users is stored. From this application you can trigger the deprovisioning of users that are no longer considered active users.

## Configuring deprovision clients
A deprovision client is an OpenConext suite app that implements the deprovisioning API. And can therefor be used by OpenConext User Lifecycle to deprovision users from the platform. To configure a client, please update the `config/legacy/parameters.yml` file. For each client provide an entry in the `open_conext_user_lifecycle_clients` configuration section. An example can be found below.

```yaml
open_conext_user_lifecycle_clients:
    openconext_engineblock:
        url: 'https://engine.example.com/path/to/api/'
        username: 'my-user-name'
        password: 'secret'
        verify_ssl: false
    teams:
        url: 'https://teams.example.com/api'
        username: 'deprovision'
        password: 'secret'
``` 

For more information about setting up the clients, see the `/config/legacy/parameters.yml.dist` file.

## Deprovisioning users
Deprovisioning users can be done on a user basis, providing the user collab person id. Or automatically
after a period of inactiviy. This period can be configured in the `/config/legacy/parameters.yml`. Both options use
the `userlifecycle deprovision` console command.

### Single user
The `userlifecycle deprovision` takes an user argument and several other options.

The `user` argument should be the one and only argument of the command. 

**Options**

| Name   | Shortcut | Description |
|---|---|---|
| `--dry-run` | __none__ | Enables dry run mode, simulates a deprovision action, returning the output a regular run would, but without actually deprovisioning the user. |
| `--json` | __none__ | Only outputs JSON. Must be used in combination with the --no-interaction option.|
| `--pretty` | __none__ | Pretty-print JSON output.|
| `--no-interaction` | `-n` | Prevents the confirmation question. |

**Example usage**

```bash
$ userlifecycle deprovision urn:collab:person:surf.nl:janis_joplin
Continue with deprovisioning of "urn:collab:person:surf.nl:janis_joplin"? (y/n)
# Will start deprovisioning after a positive answer to the confirmation.
```

```bash
$ userlifecycle deprovision urn:collab:org:surf.nl:janis_joplin --dry-run
# Asks confirmation, will not deprovision actual user data
```

```bash
$ userlifecycle deprovision urn:collab:org:surf.nl:janis_joplin --no-interaction --json
# Starts deprovisioning right away, will only output the JSON returned from the services.
```

### Batch deprovisioning
When the user argument is omitted, the deprovision command will start deprovisioning the users that have exceeded the
inactivity period set in the `inactivity_period` parameter in `parameters.yml`. This parameter must be an integer value
representing the months of inactivity before a user must be deprovisioned.

> By default 37 months used as the inactivity period.

**Options**

The same options can be used as described in the `Single user` section above.

**Example usage**

```bash
$ userlifecycle deprovision
Continue with deprovisioning? (y/n)
# Will start deprovisioning after a positive answer to the confirmation.
```

```bash
$ userlifecycle deprovision --dry-run --no-interaction
Continue with deprovisioning? (y/n)
# Will start a dry run without asking for confirmation.
```

## Gather information about a user
To read user information you can use the `information` console command.

The `information` command takes one argument which is the collabPersonId.


**Options**

| Name | Shortcut | Description |
| --- | --- | --- |
| `--json` | __none__ | Only outputs JSON. |


**Example usage**
```bash
$ userlifecycle information urn:collab:example.org:user_id
```

## API
An API can be toggled, exposing the deprovision command (in read mode). Use the following feature toggle to enable/disable the API.

In config/legacy/parameters.yml
```bash
# By default the API is disabled
deprovision_api_settings_enabled: true
```

Only user information can be read from the endpoint. The API by default is configured with basic authentication, using a configurable username and password.

In config/legacy/parameters.yml
 ```bash
# To enable the API
deprovision_api_settings_enabled: true
deprovision_api_settings_username: userlifecycle
deprovision_api_settings_password: secret
 ```

Please note that the username and password should always be provided even when the API is disabled. 

The API can be called in the following manner for a given user's collabPersonId:

`GET /api/deprovision/urn:collab:person:example.org:jdoe`

and will return the deprovision information in JSON format.

There are some rules on how the user data should be structured. User Lifecycle will only accept properly formatted
user data. The contract can be found in the [docs/deprovision-information.md]().

## Logging

### Production logging
Logging is configured slightly different for the UserLifecycle project. On other OpenConext apps logging on production
is done in syslog using the fingers crossed strategy. Fingers crossed means that no detailed log trails are produced in
syslog unless a certain log level is reached. Say the application logs an `error`. Fingers crossed will then also log
any previous log messages along the error. Giving the log-auditor all the context it needs.

A great log solution, but this did not fit for UserLifecyle. Here we log data we always want to see in syslog. And using
the fingers crossed strategy here was not practical. So the regular `stream` log strategy is used, logging everything
surpassing the configured log level (`notice`).

## For developers
See the `/docs` folder for more details information about the application.
