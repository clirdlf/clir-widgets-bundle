#!/bin/bash

ls | while read -r FILE
do
  mv -v "$FILE" `echo $FILE | tr ' ' '_' | tr -d '[{}(),\!]' | tr -d "\'" | tr '[A-Z]' '[a-z]' | sed 's/_-_/_/g'`
done

for file in ./*.{jpg,png,jpeg,gif}
do
  if [[ -f $file ]]; then
    echo "Resizing $file..."
    `convert -define jpeg:size=270x270 $file -thumbnail 270x270^ -gravity center -extent 270x270 thumbs/$file`
  fi
done

echo "\n\nResized images are in thumbs directory"

