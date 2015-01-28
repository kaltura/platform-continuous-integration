#!/bin/sh
while read CLIENT;do php /opt/kaltura/app/generator/generate.php $CLIENT;done < /opt/kaltura/app/configurations/generator.all.ini
REPO_RC_FILE=`dirname $0`/clientlib_to_git_repo.rc
if [ -r $REPO_RC_FILE ];then
	. $REPO_RC_FILE
else
	echo "Couldn't find `dirname $0`/clientlib_to_git_repo.rc && exiting:("
	exit 1
fi
TEMP_CHECKOUT=/tmp/clients_checkouts
mkdir -p $TEMP_CHECKOUT
rm -rf $TEMP_CHECKOUT/*
cd $TEMP_CHECKOUT
while read CLIENT;do 
	if `echo $CLIENT|grep -q "^#"` ;then
		continue
	fi
	REPO=`echo $CLIENT |awk -F "=" '{print $1}'`
	DIR=`echo $CLIENT |awk -F "=" '{print $2}'`
	LIB_PREFIX_DIR="/opt/kaltura/web/content/clientlibs/$DIR"
	git clone git@github.com:kaltura/$REPO.git
	cp -r $LIB_PREFIX_DIR/* $TEMP_CHECKOUT/$REPO/$DIR
	cd $TEMP_CHECKOUT/$REPO && git add . && git commit -a -m "New clientlib ver" && git push
done < $REPO_RC_FILE

