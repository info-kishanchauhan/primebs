#!/bin/bash

# Source folder
SRC="/home/primebackstage/htdocs/www.primebackstage.in/public/uploads/audio"

# Convert all .wav files to .mp3
for file in "$SRC"/*.wav; do
    if [ -f "$file" ]; then
        filename=$(basename "$file" .wav)
        mp3file="$SRC/$filename.mp3"

        # Agar already convert nahi hua tabhi convert kare
        if [ ! -f "$mp3file" ]; then
            ffmpeg -i "$file" -vn -ar 44100 -ac 2 -b:a 192k "$mp3file"
            echo "Converted: $file -> $mp3file"
        fi
    fi
done
