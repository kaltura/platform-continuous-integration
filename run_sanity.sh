#!/bin/sh
CLUS_LIST="/opt/vhosts/csi/cluster_members"

DB="/opt/vhosts/csi/db/csi.db"
rm /tmp/reportme.`date +%d_%m_%Y`.sql
for i in `cat $CLUS_LIST`;do
	if echo $i|grep '#' -q ;then
		continue
	fi
	ssh root@$i -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no "yum clean all && yum update '*kaltura*' -y"
	if [ -z "$VERSION" ];then
		VERSION=`ssh root@$i -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no "rpm -qa kaltura-base --queryformat %{version}" `
	fi
	if [ -z "$DB_UPDATE" ];then

		ssh root@$i -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no "/opt/kaltura/bin/kaltura-db-update.sh"
		DB_UPDATE=1

	fi
	ssh root@$i -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no "/opt/kaltura/bin/kaltura-base-config.sh /root/kalt.ans && /opt/kaltura/bin/kaltura-batch-config.sh"
	ssh root@$i -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no /opt/kaltura/bin/kaltura-sanity.sh 
	scp -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no root@$i:"/tmp/$i-reportme.`date +%d_%m_%Y`.sql" /tmp/
	cat "/tmp/$i-reportme.`date +%d_%m_%Y`.sql" >> /tmp/reportme.`date +%d_%m_%Y`.sql
done

cp $DB $DB.older
echo "delete from csi_log where kaltura_version='$VERSION';"|sqlite3 $DB
echo "delete from success_rates where kaltura_version='$VERSION';"|sqlite3 $DB
sqlite3 $DB < /tmp/reportme.`date +%d_%m_%Y`.sql
SUCCESS=0
FAILED=0
SUCCESS=`echo "select count(rc) from csi_log where rc=0 and kaltura_version='$VERSION';"|sqlite3 $DB`
FAILED=`echo "select count(rc) from csi_log where rc!=0 and kaltura_version='$VERSION';"|sqlite3 $DB`
echo "insert into success_rates values(NULL,$FAILED,$SUCCESS,'$VERSION');"|sqlite3 $DB
scp -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no /opt/vhosts/csi/db/csi.db root@38.74.192.73:/opt/vhosts/reports/ci/db 
