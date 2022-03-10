#!/bin/bash

numberExpression='^[0-9]+$'
databasePath=var/user-lifecycle.sqlite

if ! [[ $1 =~ $numberExpression ]] ; then
   echo "Usage: The first argument must be a number. Indicating the number of identities you'd like to seed to the last_login table" >&2
   exit 1
fi

if ! [[ $2 =~ $numberExpression ]] ; then
   echo "Usage: The second argument must be a number. Indicating the number of days since last login for all users" >&2
   exit 1
fi

if ! [ -x "$(command -v sqlite3)" ]; then
    echo "SQLite is not installed"
    exit 1
fi
if ! [ -x "$(command -v rig)" ]; then
    echo "Rig is not installed. Rig is used to create a random username."
    exit 1
fi

if ! [ -f "${databasePath}" ]; then
    echo "The SQLite database was not found at '${databasePath}'"
    exit 1
fi

echo "Adding ${1} users to the last_login test table.."
for ((i = 1 ; i <= $1 ; i++)) do
    randomUserName=`rig | head -n 1 |  tr ' ' _ | tr A-Z a-z`
    timeLastLogin=`date --rfc-3339=date -d "-${2} days"`

    query="insert into last_login (userid, lastseen) values ('urn:collab:person:user:example.com:${randomUserName}', '${timeLastLogin}');"
    sqlite3 ${databasePath} "${query}"
done
