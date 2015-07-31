if [ $# -lt 2 ];then
	echo "Usage: $0 <machine> <mail@alert.com>"
	exit 1
fi
MACHINE=$1
EMAIL_TO=$2
LOGFILE=/tmp/$MACHINE.log
:> $LOGFILE ; 
ssh root@$MACHINE "/opt/kaltura/bin/kaltura-drop-db.sh $SUPER_USER_PASSWD && aptitude purge "~Nkaltura" -y && aptitude purge "~Napache2" -y && rm -rf /opt/kaltura" >> $LOGFILE 2>&1;
ssh root@$MACHINE "/root/kalt.ans && /root/install_kaltura_all_in_1.sh && /opt/kaltura/bin/kaltura-sanity.sh" >> $LOGFILE 2>&1;
if [ $? -ne 0 ]; then 
	mail -s "$MACHINE install failed" $EMAIL_TO < $LOGFILE
fi
