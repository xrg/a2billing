#!/bin/bash

# This script migrates a v1.3xrg database to v200
A2B_USER=a2billing
A2B_DB=a2billing
A2B_GROUP=a2b_group

BACKUP_DIR=.

# First, make a backup!
DSTAMP=$(date +'%Y%m%d')
BKUP_FILE="$BACKUP_DIR/a2billing-dbdump-$DSTAMP.gz"

if [ "$1" == "--skip-backup" ] ; then
	DO_SKIP_BKUP=y
fi

if [ -f "$BKUP_FILE" ] && [ "$DO_SKIP_BKUP" == 'y' ] ; then
	echo "Skipping backup"
elif [ -f "$BKUP_FILE" ] || ( ! pg_dump -U $A2B_USER -d $A2B_DB | \
		gzip -c > $BKUP_FILE ) ; then
	echo "Backup couldn't be made, it is not wise to continue!"
	exit 1
else
	echo "Backup made. If any of the following fail, please restore $BKUP_FILE ."
fi

echo "Stage 1."
for DBSCRIPT in migrate-1/* ; do
	echo  'Invoking' "$DBSCRIPT"

	psql -U $A2B_USER --set ON_ERROR_STOP= --set A2B_GROUP=$A2B_GROUP \
		-q -d $A2B_DB -f "$DBSCRIPT" || exit $?
done

echo "Stage 1 finished: moved away v1.3 db."
echo "Stage 2."


#eof

