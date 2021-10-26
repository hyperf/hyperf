#!/usr/bin/env sh
REPOS=$(git remote)
for REPO in $REPOS ; do
  if [ $REPO != 'upstream' ] && [ $REPO != 'origin' ]; then
    git remote remove $REPO
    echo "delete remote $REPO success."
  fi
done
