#!/bin/bash

if [ $# -lt 1 ]; then
  echo "Usage: $0 <basename>";
  echo "Base example: 'UEssexCbeta3'";
  exit;
fi

lnk="DB.skel.sql";
pass="photon06";

proj=$1

ver=`mysql -p$pass $proj -B -s -e "SELECT dbver FROM settings WHERE id=1"`
echo "Version detected: $ver"

fname="./RL_$ver.skel.sql";

# Dump
mysqldump -p$pass --no-data --skip-add-drop-table  --skip-add-locks --skip-disable-keys --skip-set-charset  $proj | sed 's/AUTO_INCREMENT=[0-9]*\b//' > $fname

# Categories
cat ./cats.sql >> $fname

# echo -e "\n\nINSERT INTO settings (\`id\`,\`dbver\`) VALUES (1,'$ver');\n\n" >> $fname;

echo "Output file: $fname"

if [ -f $lnk ]; then
  rm $lnk
fi

echo "Linking to $lnk"
ln -s $fname $lnk

exit 0
