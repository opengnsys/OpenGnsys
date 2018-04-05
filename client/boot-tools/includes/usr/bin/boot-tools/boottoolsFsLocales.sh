#!/bin/bash
cat << EOT | debconf-set-selections --
console-data console-data/keymap/full select es
console-data console-data/keymap/policy select Select keymap from full list
console-setup console-setup/charmap47 select UTF-8
console-setup console-setup/codeset47 select . Combined - Latin; Slavic Cyrillic; Greek
console-setup console-setup/fontface47 select VGA
console-setup console-setup/fontsize-fb47 select 8x16
console-setup console-setup/fontsize-text47 select 8x16
EOT
dpkg-reconfigure console-data console-setup locales

history -c
