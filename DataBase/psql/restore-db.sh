#!/bin/bash

A2B_USER=a2billing
A2B_DB=a2billing
A2B_GROUP=a2b_group

if [ ! -f "$1" ] ; then
	echo "File not found: $1"
	exit 1
fi

createdb -O $A2B_USER -U postgres A2B_DB || exit $?

cat "$1" | gunzip -c | psql -q --set ON_ERROR_STOP= -U postgres $A2B_DB || exit $?

echo "Database restored!"

#eof
