#!/usr/bin/env sh
set -e
# execute `composer global require comcast/php-legal-licenses` before run this shell

NOW=$(date +%s)
BASEPATH=$(cd `dirname $0`; cd ../src/; pwd)

repos=$(ls "$BASEPATH")
echo $NOW

cd `dirname $0`; cd ../src/

git checkout master && git checkout -b licenses-generate-"$NOW"

for REPO in $repos
do
    echo "Generating $REPO";
    cd "./$REPO"

    if [ -f "composer.json" ]; then
      composer update -q
      php-legal-licenses generate
    fi
    if [ -f "composer.lock" ]; then
      rm -rf ./composer.lock
    fi
    if [ -d "vendor" ]; then
      rm -rf ./vendor
    fi
    if [ -f "licenses.md" ]; then
      git add ./licenses.md
    fi

    cd ../

done

git commit -m "Update licenses.md"
git push

TIME=$(echo "$(date +%s) - $NOW" | bc)

printf "Execution time: %f seconds" "$TIME"