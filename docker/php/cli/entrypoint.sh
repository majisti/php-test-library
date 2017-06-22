#!/bin/bash
set -e

user_id=`stat -c %u /var/www/html`

if (("$user_id" >= "1000")); then
    useradd --shell /bin/bash -u $user_id -o -c "" -m user
    export HOME=/home/user
fi


user_name=$(awk -F: "/:$user_id:/{print \$1}" /etc/passwd)
exec /usr/local/bin/gosu $user_name "$@"
