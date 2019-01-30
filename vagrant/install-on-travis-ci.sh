#!/bin/bash

# This script runs in a travis-ci.org virtual machine
# https://docs.travis-ci.com/user/trusty-ci-environment/
# Ubuntu 14 (trusty)
# user 'travis'
# $TRAVIS_BUILD_DIR is /home/travis/build/openstreetmap/Nominatim/, for others see
#   https://docs.travis-ci.com/user/environment-variables/#Default-Environment-Variables
# Postgres 9.6 installed and started. role 'travis' already superuser
# Python 3.6
# Travis has a 4 MB, 10000 line output limit, so where possible we run script --quiet


pip3 install --quiet behave nose pytidylib psycopg2-binary

# Travis uses phpenv to support multiple PHP versions. We need to make sure
# these packages get installed to the phpenv-set PHP (below /home/travis/.phpenv/),
# not the system PHP (/usr/bin/php)
sudo PHP_PEAR_PHP_BIN=`which php` pear -q install pear/PEAR-1.10.0
sudo PHP_PEAR_PHP_BIN=`which php` pear -q install DB
sudo PHP_PEAR_PHP_BIN=`which php` pear -q install PHP_CodeSniffer
sudo PHP_PEAR_PHP_BIN=`which php` pear list
# re-populate the shims/ directory, e.g. adds phpcs
phpenv rehash
ls -la /home/travis/.phpenv/shims/

# $PHPENV_VERSION and $TRAVIS_PHP_VERSION are unset.
export PHPENV_VERSION=$(cat /home/travis/.phpenv/version)

# add lib/php/pear to the PHP include path
tee /tmp/travis.php.ini << EOF
include_path = .:/home/travis/.phpenv/versions/$PHPENV_VERSION/share/pear:/home/travis/.phpenv/versions/$PHPENV_VERSION/lib/php/pear
EOF
phpenv config-add /tmp/travis.php.ini


sudo -u postgres createuser -S www-data

# Make sure that system servers can read from the home directory:
chmod a+x $HOME
chmod a+x $TRAVIS_BUILD_DIR


sudo tee /etc/apache2/conf-available/nominatim.conf << EOFAPACHECONF > /dev/null
    <Directory "$TRAVIS_BUILD_DIR/build/website">
      Options FollowSymLinks MultiViews
      AddType text/html   .php
      DirectoryIndex search.php
      Require all granted
    </Directory>

    Alias /nominatim $TRAVIS_BUILD_DIR/build/website
EOFAPACHECONF


sudo a2enconf nominatim
sudo service apache2 restart

wget -O $TRAVIS_BUILD_DIR/data/country_osm_grid.sql.gz https://www.nominatim.org/data/country_grid.sql.gz

mkdir build
cd build
cmake $TRAVIS_BUILD_DIR
make


tee settings/local.php << EOF
<?php
 @define('CONST_Website_BaseURL', '/nominatim/');
 @define('CONST_Database_DSN', 'pgsql://@/test_api_nominatim');
 @define('CONST_Wikipedia_Data_Path', CONST_BasePath.'/test/testdb');
EOF

