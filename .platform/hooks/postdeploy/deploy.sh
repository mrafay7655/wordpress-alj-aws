#!/bin/bash

  echo "Link storage/app/public with efs.."
  sudo ln -snf /mnt/efs/wp-content /var/app/current/wp-content
  echo "Fix ownership /var/app/current/wp-content"
  sudo chown -R webapp:webapp /var/app/current/wp-content
# sudo chmod -R 777 /var/app/current/wp-content/uploads
sudo chown -R webapp:webapp /mnt/efs/wp-content
