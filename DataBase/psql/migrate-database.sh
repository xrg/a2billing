#!/bin/bash

# This script migrates a v1.3xrg database to v200
A2B_USER=a2billing
A2B_DB=a2billing
A2B_GROUP=a2b_group

BACKUP_DIR=.

# First, make a backup!
DSTAMP=$(date +'%Y%m%d')
BKUP_FILE="$BACKUP_DIR/a2billing-dbdump-$DSTAMP.gz"

DO_SKIP_BKUP=
DO_SKIP_STAGE1=
DO_SKIP_STAGE2=
DO_SKIP_STAGE3=

while [ -n "$1" ] ; do
	case "$1" in
	'--skip-backup')
		DO_SKIP_BKUP=y
		;;
	'--skip-stage1')
		DO_SKIP_STAGE1=y
		;;
	'--skip-stage2')
		DO_SKIP_STAGE1=y
		DO_SKIP_STAGE2=y
		;;
	'--skip-stage3')
		DO_SKIP_STAGE1=y
		DO_SKIP_STAGE2=y
		DO_SKIP_STAGE3=y
		;;
	'--skip-stage4')
		DO_SKIP_STAGE4=y
		;;
	*)
		echo "Unknown option: $1"
		exit
		;;
	esac
	shift 1
done
echo -n "Performing Stages:"
[ "$DO_SKIP_STAGE1" == "y" ] || echo -n " 1"
[ "$DO_SKIP_STAGE2" == "y" ] || echo -n " 2"
[ "$DO_SKIP_STAGE3" == "y" ] || echo -n " 3"
[ "$DO_SKIP_STAGE4" == "y" ] || echo -n " 4"
echo "."

if [ -f "$BKUP_FILE" ] && [ "$DO_SKIP_BKUP" == 'y' ] ; then
	echo "Skipping backup"
elif [ -f "$BKUP_FILE" ] || ( ! pg_dump -U $A2B_USER -d $A2B_DB | \
		gzip -c > $BKUP_FILE ) ; then
	echo "Backup couldn't be made, it is not wise to continue!"
	exit 1
else
	echo "Backup made. If any of the following fail, please restore $BKUP_FILE ."
fi

if [ "$DO_SKIP_STAGE1" != "y" ] ; then
	echo "Stage 1."
	for DBSCRIPT in migrate-1/* ; do
		echo  'Invoking' "$DBSCRIPT"
	
		psql -U $A2B_USER --set ON_ERROR_STOP= --set A2B_GROUP=$A2B_GROUP \
			-q -d $A2B_DB -f "$DBSCRIPT" || exit $?
	done
	
	echo "Stage 1 finished: moved away v1.3 db."
fi

if [ "$DO_SKIP_STAGE2" != "y" ] ; then
	echo "Stage 2."
	#  Copied from build-database.sh
	for DBSCRIPT in schema-200/* ; do
		DBNUM=$(basename $DBSCRIPT | sed 's/^\([0-9]\+\)-.*$/\1/')
		if [ "$DBNUM" -ge 50 ] ; then
			continue
		fi
		echo "Processing $DBSCRIPT.."
		psql -U $A2B_USER --set ON_ERROR_STOP= --set A2B_GROUP=$A2B_GROUP \
			-d $A2B_DB -f "$DBSCRIPT" || exit $?
	done
	echo "Stage 2: Database build successfully!"
fi

if [ "$DO_SKIP_STAGE3" != "y" ] ; then
	echo "Stage 3."
	for DBSCRIPT in migrate-2/* ; do
		echo  'Invoking' "$DBSCRIPT"
	
		psql -U $A2B_USER --set ON_ERROR_STOP= --set A2B_GROUP=$A2B_GROUP \
			-q -d $A2B_DB -f "$DBSCRIPT" || exit $?
	done
	
	# Add missing texts
	psql -U $A2B_USER  --set A2B_GROUP=$A2B_GROUP \
		-q -d $A2B_DB -f schema-200/32-std-texts.sql || exit $?

	echo "Stage 3 finished: old data migrated to new tables"
fi

if [ "$DO_SKIP_STAGE4" != "y" ] ; then
	echo "Stage 4."
	psql -q -U $A2B_USER --set ON_ERROR_STOP= -d $A2B_DB \
		-c 'DROP SCHEMA a2b_old CASCADE;' || exit $?
	echo "Stage 4 finished: dropped old tables!"
fi

echo
echo "Your database should now be ready for v200!"
#eof

