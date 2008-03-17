#!/bin/bash

A2B_USER=a2billing
A2B_DB=a2billing
A2B_GROUP=a2b_group

createdb -O $A2B_USER -E UTF8 -U postgres $A2B_DB || exit $?

psql -U postgres -d $A2B_DB --set ON_ERROR_STOP= -c 'CREATE LANGUAGE plpgsql;' || exit $?

DO_TBLONLY=
if [ "$1" == '--schema-only' ] ; then
	DO_TBLONLY=y
fi

for DBSCRIPT in schema-200/* ; do
	DBNUM=$(basename $DBSCRIPT | sed 's/^\([0-9]\+\)-.*$/\1/')
	if [ "$DO_TBLONLY" == "y" ] && [ "$DBNUM" -ge 50 ] ; then
		continue
	fi
	echo "Processing $DBSCRIPT.."
	psql -U $A2B_USER --set ON_ERROR_STOP= --set A2B_GROUP=$A2B_GROUP \
		-d $A2B_DB -f "$DBSCRIPT" || exit $?
done

echo "Database build successfully!"
#eof
