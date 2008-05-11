#!/bin/bash

A2B_USER=a2billing
A2B_DB=a2billing
A2B_GROUP=a2b_group
PG_ROOT=postgres

if ! (psql -qt -U $PG_ROOT -c "SELECT usename FROM pg_user WHERE usename = '$A2B_USER';" | \
	grep $A2B_USER > /dev/null) ; then
	if ! createuser -U $PG_ROOT -l $A2B_USER < /dev/null ; then
		echo "Failed to create user $A2B_USER"
		exit 1
	fi
else
	echo "User $A2B_USER already exists."
fi

if ! (psql -qt -U $PG_ROOT -c "SELECT groname FROM pg_group WHERE groname = '$A2B_GROUP';" | \
	grep $A2B_GROUP > /dev/null) ; then
	if ! psql -qt -U $PG_ROOT -c "CREATE GROUP $A2B_GROUP; " ; then
		echo "Failed to create group $A2B_GROUP"
		exit 1
	fi
else
	echo "Group $A2B_GROUP already exists."
fi

if (psql -qt -U $PG_ROOT -c "SELECT datname FROM pg_database WHERE datname = '$A2B_DB';" | \
	grep $A2B_DB > /dev/null ) ; then
	echo -n "Database $A2B_DB already exists." 
	if [ "$1" != "--force"  ] ; then
		echo " It is not wise to continue."
		exit 2
	else
		echo " Continuing anyway."
	fi
else
	createdb -O $A2B_USER -E UTF8 -U $PG_ROOT $A2B_DB || exit $?
fi

if ! (psql -qt -U $PG_ROOT -c "SELECT lanname FROM pg_language WHERE lanname = 'plpgsql';" | \
	grep 'plpgsql' > /dev/null) ; then
	psql -U $PG_ROOT -d $A2B_DB --set ON_ERROR_STOP= -c 'CREATE LANGUAGE plpgsql;' || \
		ERR_CODE=$? ; \
		echo "Cannot use plpgsql. Do you have the language module installed?" ;\
		exit $ERR_CODE
fi

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
