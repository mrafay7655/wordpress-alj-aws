#!/bin/bash
set -e

echo "Preparing EFS..."

mkdir -p /mnt/efs

# First deployment only
if [ ! -d /mnt/efs/wp-content ]; then
    echo "First deployment - copying wp-content to EFS"
    cp -a /var/app/current/wp-content /mnt/efs/
fi

echo "Replacing wp-content with EFS symlink"

rm -rf /var/app/current/wp-content

ln -s /mnt/efs/wp-content /var/app/current/wp-content

chown -R webapp:webapp /mnt/efs/wp-content