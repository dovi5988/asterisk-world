#! /bin/bash

# This bash script is meant to be called as a cron job once a minute.
# It checks Asterisk for any UNREACHABLE peer and then run a trace to them.
# Add this to crontab: /usr/sbin/asterisk -rx 'sip show peers' | grep UNREACHABLE | /root/log_unreachable.sh >/dev/null 2>&1

while read INPUT
do
        IFS=' ' read -ra INPUT <<< "$INPUT"
        DIR=/var/log/traces/${INPUT[0]}/$(date +%Y-%m-%d)/$(date +"%H")/
        DIR_FILE=$DIR$(date +"%M")
        mkdir -p $DIR
        /usr/sbin/mtr -o "L SRD NBAW JMXI" --report --report-cycles 5 --no-dns ${INPUT[1]} > $DIR_FILE
done
