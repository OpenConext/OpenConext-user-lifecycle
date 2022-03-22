# Database

The automated deprovisioning of users on the OpenConext platform relies on the OpenConext Stats. In the stats 
application, the timestamp of the last login of a platform user is stored.

If your development environment does not track the last login data, you can use an in memory SQLite alternative.

## Installing SQLite

On the guest, SQLite is available in the docker container. If you'd rather work on your host you might need to install
SQLite.

On a Red Hat-ish distro:
```bash
$ sudo yum install sqlite3
```

## Creating the SQLite dev database

Creating the dev database can be done with the following console command:

```bash
$ ./bin/console doctrine:schema:create --env=dev
``` 

Note that the `config_dev.yml` file is instructing the use of SQLite in the ORM configuration. The path used for the
 storage of the database file is in the projects var folder: `./var/user-lifecycle.sqlite`.
 
## Entries
Adding entries to the database is also done manually.

```bash
$ sqlite3 ./var/user-lifecycle.sqlite

sqlite> insert into last_login (`userid`, `lastseen`) values ('collab:person:user:example.com:jesse_james', '2018-01-01 12:34:00');
```
