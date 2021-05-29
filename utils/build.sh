#!/bin/sh


dnf -y update && dnf -y install dnf-plugins-core && dnf config-manager --set-enabled powertools && dnf -y install doxygen ant php-pear

cd /app/utils \
&& pear channel-update pear \
&& pear upgrade -Z --force --alldeps \
&& pear install --onlyreqdeps PEAR_PackageFileManager2-beta \
&& ant dist -Ddoxygen.path=/usr/bin/doxygen -Dphp.path=/usr/bin/php
