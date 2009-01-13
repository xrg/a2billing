#!/bin/sh
#
# Startup script for the DNS caching server

### BEGIN INIT INFO
# Provides: a2billing-daemons
# Required-Start: $network
# Required-Stop: $network
# Default-Start: 3 4 5
# Short-Description: daemons for the a2billing platform
# Description: The A2Billing platform depends on some processes,
#    that perform maintenance tasks (eg. send alarms).
#    Note that phone services through a2billing could work even if
#    these daemons are stopped.
### END INIT INFO

#
# chkconfig: 2345 99 01
# description: This script starts the a2billing daemons
#

# Source function library.
. /etc/rc.d/init.d/functions

send_emailsd=/usr/share/a2billing/scripts/send-emails.php
[ -f $send_emailsd ] || exit 0

DAEMON_NAME=a2billing-emailsd

[ -f /etc/sysconfig/a2billing-daemons ] && . /etc/sysconfig/a2billing-daemons

case "$1" in
  start)
        gprintf "Starting %s: " $DAEMON_NAME
        PIDFILE=/var/run/$DAEMON_NAME.pid
        touch $PIDFILE && chown apache $PIDFILE
        daemon --pidfile=$PIDFILE --user apache +6 \
        	$send_emailsd --daemon -q \& echo \$! \> $PIDFILE
        echo
        ;;
  stop)
        gprintf "Shutting down %s: " $DAEMON_NAME
        PIDFILE=/var/run/$DAEMON_NAME.pid
        killproc -p $PIDFILE $send_emailsd
        echo
        ;;
  status)
  	PIDFILE=/var/run/$DAEMON_NAME.pid
    	status -p $PIDFILE $DAEMON_NAME
        ;;
  restart|reload)
        $0 stop
        $0 start
        ;;
  *)
        gprintf "Usage: %s {start|stop|restart|reload|condrestart|status}\n" "$0"
        exit 1
esac


