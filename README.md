<a href="https://openconext.org/">
    <img src="https://openconext.org/wp-content/uploads/2016/11/openconext_logo-med.png" alt="OpenConext"
         align="right" />
</a>

[![Build status](https://img.shields.io/travis/OpenConext/user-lifecycle.svg)](https://travis-ci.org/OpenConext/user-lifecycle)
[![License](https://img.shields.io/github/license/OpenConext/user-lifecycle.svg)](https://github.com/OpenConext/user-lifecycle/blob/master/LICENSE)

# OpenConext User Lifecycle
Deprovision users within the OpenConext platform. Teh User Lifecycle application is where the last login information of OpenConext suite users is stored. From this application you can trigger the deprovisioning of users that are no longer considered active users.

## Configuring deprovision clients
A deprovision client is an OpenConext suite app that implements the deprovisioning API. And can therefor be used by OpenConext User Lifecycle to deprovision users from the platform. To configure a client, please update the `app/config/parameters.yml` file. For each client provide an entry in the `open_conext_user_lifecycle_clients` configuration section. An example can be found below.

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

For more information about setting up the clients, see the `parameters.yml.dist` file.

## Deprovisioning a user
TODO: describe DELETE feature and its --dry-run option.

## Gather information about a user
TODO: describe the GET feature.

## For developers
See the `/docs` folder for more details information about the application.