#!/bin/bash


if [ $# -lt 1 ]; then
  echo "Usage: $0 <basename>";
  echo "Base example: 'UEssexCbeta3'";
  exit;
fi

base=$1

for i in ./uploaded/$base*.svg
do

  echo "php ./import.php filename=`basename $i` install=false";
  php ./import.php filename=`basename $i` install=false;
done

exit 0;


