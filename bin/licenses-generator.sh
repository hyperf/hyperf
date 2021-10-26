#!/usr/bin/env sh
set -e
# execute `composer global require comcast/php-legal-licenses` before run this shell

NOW=$(date +%s)
BASEPATH=$(cd `dirname $0`; cd ../src/; pwd)

repos=$(ls "$BASEPATH")
echo $NOW

function generate() {
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
}

cd ..

git checkout master && git checkout -b licenses-generate-"$NOW"

echo "Generating main repository";
generate

cd ./src

for REPO in $repos
do
    echo "Generating $REPO";
    cd "./$REPO"

    generate

    cd ../

done

git commit -m "Update licenses.md"

TIME=$(echo "$(date +%s) - $NOW" | bc)

printf "Execution time: %f seconds" "$TIME"