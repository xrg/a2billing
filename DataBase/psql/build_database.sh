#!/bin/bash

A2B_USER=a2billing
A2B_DB=a2billing
A2B_GROUP=a2b_group

createdb -O $A2B_USER -U postgres $A2B_DB || exit $?

psql -U postgres -d $A2B_DB --set ON_ERROR_STOP= -c 'CREATE LANGUAGE plpgsql;' || exit $?

for DBSCRIPT in schema-200/* ; do
	psql -U $A2B_USER --set ON_ERROR_STOP= --set A2B_GROUP=$A2B_GROUP \
		-d $A2B_DB -f "$DBSCRIPT" || exit $?
done

echo "Database build successfully!"
#eof
