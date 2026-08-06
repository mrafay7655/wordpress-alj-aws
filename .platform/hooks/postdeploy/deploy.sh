#!/bin/bash
set -e

echo "Preparing EFS..."

mkdir -p /mnt/efs/wp-content/uploads

# First deployment only: seed EFS uploads from whatever is in the bundle
if [ -d /var/app/current/wp-content/uploads ] && [ ! -L /var/app/current/wp-content/uploads ]; then
    if [ -z "$(ls -A /mnt/efs/wp-content/uploads 2>/dev/null)" ]; then
        echo "First deployment - seeding EFS uploads from bundle"
        cp -a /var/app/current/wp-content/uploads/. /mnt/efs/wp-content/uploads/
    fi
    rm -rf /var/app/current/wp-content/uploads
fi

echo "Symlinking wp-content/uploads to EFS (code stays local)"

ln -sfn /mnt/efs/wp-content/uploads /var/app/current/wp-content/uploads

chown -R webapp:webapp /mnt/efs/wp-content/uploads
chown -h webapp:webapp /var/app/current/wp-content/uploads