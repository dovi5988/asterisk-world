#!/bin/bash

# This script is meant to constantly watch the fulll log. If a peer
# becomes unreachable it will run a trace and then email it to us.

MAILTO=email@example.org

tail -Fn0 /var/log/asterisk/full|\
while read LINE
do

if [[ $LINE =~ (.+Peer \'(.+)\' is now UNREACHABLE.+) ]] ; then
        Q=mktemp
	echo "Peer ${BASH_REMATCH[2]} just became unreachable. Below is a trace." > $Q
	echo "" >> $Q
	/usr/sbin/mtr -o "L SRD NBAW JMXI" --report --report-cycles 5 --no-dns `/usr/sbin/asterisk -rx'sip show peers' | grep  ${BASH_REMATCH[2]} | awk '{print $2}'` >> $Q
	mail  -s "Connectivity issuse to peer ${BASH_REMATCH[2]}" $MAILTO < $Q
	rm -rf $Q

fi

done

