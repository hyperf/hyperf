#!/usr/bin/env bash

set -e

CURRENT_PATH="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
ROOT_PATH=$(dirname ${CURRENT_PATH})
REPO_PATH="${ROOT_PATH}/src"
REPOS=$@

function pending()
{
    local REPO=$1

    echo "Copying .github to ${REPO}"
    cp -rf "${ROOT_PATH}/bin/stubs/.github" "${ROOT_PATH}/src/${REPO}"

    return
}

if [[ $# -eq 0 ]]; then
    REPOS=$(ls $REPO_PATH)
fi

for REPO in $REPOS ; do
    echo "Pending ${REPO} ..."

    pending "$REPO"

    echo ""
done
