#!/bin/sh

yum -y update && yum -y install ant doxygen php-pear

cd /app/utils \
&& pear channel-update pear \
&& pear upgrade --force --alldeps \
&& pear install --onlyreqdeps PEAR_PackageFileManager2-beta \
&& ant dist -Ddoxygen.path=/usr/bin/doxygen -Dphp.path=/usr/bin/php
