#!/bin/bash

# Hint procedure for dumping the reusable data out of a a2b installation
# You can add  any pg_dump arguments at the cmdline like:
# dump-reusable.sh -U a2billing a2billing 

for table in cc_texts cc_paytypes ; do
	if [ -f $table.sql ] ; then
		echo "File $table.sql already exists, exiting."
		exit 1
	fi
	pg_dump -a -t $table -f $table.sql "$@"
done

