#!/usr/bin/env bash

# Helper script to download data from data.gouv.fr to local machine
# usage: ./download.sh /path/to/data/tmp/enrich/death-fr/raw

if [ $# -eq 0 ]; then
    echo "This script must be called with an argument (the directory where the data will be stored)"
    exit
fi

OUT_DIR=$1
INPUT_FILE="urls.txt"

set -euo pipefail

current_year=""

while IFS= read -r line; do
    # Skip empty lines
    [[ -z "$line" ]] && continue

    if [[ "$line" =~ ^[0-9]{4}$ ]]; then
        # Line is a year
        current_year="$line"

    elif [[ "$line" =~ ^https:// ]]; then
        if [[ -z "$current_year" ]]; then
            echo "Error: URL without preceding year"
            exit 1
        fi

        url="$line"

        # Extract UUID from URL (last path component)
        uuid="${url##*/}"

        echo "Downloading $url ..."
        curl -L --output-dir $OUT_DIR -o "$uuid" "$url"

        newname="deces-${current_year}.txt"

        mv "$OUT_DIR/$uuid" "$OUT_DIR/$newname"

        echo "Compressing $newname ..."
        bzip2 -f "$OUT_DIR/$newname"

        current_year=""
    fi

done < "$INPUT_FILE"

echo "Done."
