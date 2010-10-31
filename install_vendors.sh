#!/bin/sh

CURRENT=`pwd`/lib/vendor

mkdir -p "lib/vendor" && cd lib/vendor

# Autoload
git clone git://github.com/CraigMason/Autoload.git