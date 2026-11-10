#!/usr/bin/env bash
#
# Installs WordPress, its PHPUnit test library and WooCommerce for the plugin's tests.
#
# usage: bin/install-wp-tests.sh <db-name> <db-user> <db-pass> [db-host] [wp-version] [wc-version] [skip-database-creation]
#
# db-host can be "host", "host:port" or "host:/path/to/mysql.sock".
# wp-version and wc-version can be "latest" or a version number, e.g. 6.5 or 8.9.3.
# WP_TESTS_DIR and WP_CORE_DIR default to $TMPDIR/wordpress-tests-lib and $TMPDIR/wordpress.

set -e

if [ $# -lt 3 ]; then
	echo "usage: $0 <db-name> <db-user> <db-pass> [db-host] [wp-version] [wc-version] [skip-database-creation]"
	exit 1
fi

DB_NAME=$1
DB_USER=$2
DB_PASS=$3
DB_HOST=${4-localhost}
WP_VERSION=${5-latest}
WC_VERSION=${6-latest}
SKIP_DB_CREATE=${7-false}

TMPDIR=${TMPDIR-/tmp}
TMPDIR=$(echo "$TMPDIR" | sed -e "s/\/$//")
WP_TESTS_DIR=${WP_TESTS_DIR-$TMPDIR/wordpress-tests-lib}
WP_CORE_DIR=${WP_CORE_DIR-$TMPDIR/wordpress}
WP_CORE_DIR=$(echo "$WP_CORE_DIR" | sed -e "s/\/$//")

download() {
	if command -v curl > /dev/null; then
		curl -sfL "$1" > "$2"
	else
		wget -nv -O "$2" "$1"
	fi
}

# The tag of the WordPress develop repository with the matching test library.
if [[ $WP_VERSION =~ ^[0-9]+\.[0-9]+\-(beta|RC)[0-9]+$ ]]; then
	WP_TESTS_TAG="branches/${WP_VERSION%\-*}"
elif [[ $WP_VERSION =~ ^[0-9]+\.[0-9]+$ ]]; then
	WP_TESTS_TAG="branches/$WP_VERSION"
elif [[ $WP_VERSION =~ [0-9]+\.[0-9]+\.[0-9]+ ]]; then
	if [[ $WP_VERSION =~ [0-9]+\.[0-9]+\.[0] ]]; then
		# x.x.0 is tagged as x.x
		WP_TESTS_TAG="tags/${WP_VERSION%??}"
	else
		WP_TESTS_TAG="tags/$WP_VERSION"
	fi
elif [[ $WP_VERSION == 'nightly' || $WP_VERSION == 'trunk' ]]; then
	WP_TESTS_TAG="trunk"
else
	download https://api.wordpress.org/core/version-check/1.7/ "$TMPDIR/wp-latest.json"
	LATEST_VERSION=$(grep -o '"version":"[^"]*' "$TMPDIR/wp-latest.json" | head -1 | sed 's/"version":"//')
	if [[ -z "$LATEST_VERSION" ]]; then
		echo "The latest WordPress version could not be found."
		exit 1
	fi
	WP_TESTS_TAG="tags/$LATEST_VERSION"
fi

install_wp() {
	if [ -f "$WP_CORE_DIR/wp-settings.php" ]; then
		return
	fi

	mkdir -p "$WP_CORE_DIR"

	if [[ $WP_VERSION == 'nightly' || $WP_VERSION == 'trunk' ]]; then
		download https://wordpress.org/nightly-builds/wordpress-latest.zip "$TMPDIR/wordpress-nightly.zip"
		unzip -oq "$TMPDIR/wordpress-nightly.zip" -d "$TMPDIR/wordpress-nightly"
		cp -R "$TMPDIR/wordpress-nightly/wordpress/." "$WP_CORE_DIR"
	else
		if [ "$WP_VERSION" == 'latest' ]; then
			ARCHIVE_NAME='latest'
		else
			ARCHIVE_NAME="wordpress-$WP_VERSION"
		fi
		download "https://wordpress.org/${ARCHIVE_NAME}.tar.gz" "$TMPDIR/wordpress.tar.gz"
		tar --strip-components=1 -zxmf "$TMPDIR/wordpress.tar.gz" -C "$WP_CORE_DIR"
	fi
}

install_woocommerce() {
	local plugins_dir="$WP_CORE_DIR/wp-content/plugins"

	if [ -f "$plugins_dir/woocommerce/woocommerce.php" ]; then
		return
	fi

	if [ "$WC_VERSION" == 'latest' ]; then
		local archive='woocommerce.zip'
	else
		local archive="woocommerce.$WC_VERSION.zip"
	fi

	download "https://downloads.wordpress.org/plugin/$archive" "$TMPDIR/woocommerce.zip"
	mkdir -p "$plugins_dir"
	unzip -oq "$TMPDIR/woocommerce.zip" -d "$plugins_dir"
}

install_test_suite() {
	# Portable in-place sed for GNU and macOS.
	if [[ $(uname -s) == 'Darwin' ]]; then
		local ioption='-i.bak'
	else
		local ioption='-i'
	fi

	if [ ! -d "$WP_TESTS_DIR/includes" ]; then
		mkdir -p "$WP_TESTS_DIR"
		svn export --quiet --ignore-externals "https://develop.svn.wordpress.org/${WP_TESTS_TAG}/tests/phpunit/includes/" "$WP_TESTS_DIR/includes"
		svn export --quiet --ignore-externals "https://develop.svn.wordpress.org/${WP_TESTS_TAG}/tests/phpunit/data/" "$WP_TESTS_DIR/data"
	fi

	if [ ! -f "$WP_TESTS_DIR/wp-tests-config.php" ]; then
		download "https://develop.svn.wordpress.org/${WP_TESTS_TAG}/wp-tests-config-sample.php" "$WP_TESTS_DIR/wp-tests-config.php"
		sed $ioption "s:dirname( __FILE__ ) . '/src/':'$WP_CORE_DIR/':" "$WP_TESTS_DIR/wp-tests-config.php"
		sed $ioption "s:__DIR__ . '/src/':'$WP_CORE_DIR/':" "$WP_TESTS_DIR/wp-tests-config.php"
		sed $ioption "s/youremptytestdbnamehere/$DB_NAME/" "$WP_TESTS_DIR/wp-tests-config.php"
		sed $ioption "s/yourusernamehere/$DB_USER/" "$WP_TESTS_DIR/wp-tests-config.php"
		sed $ioption "s/yourpasswordhere/$DB_PASS/" "$WP_TESTS_DIR/wp-tests-config.php"
		sed $ioption "s|localhost|${DB_HOST}|" "$WP_TESTS_DIR/wp-tests-config.php"
	fi
}

install_db() {
	if [ "${SKIP_DB_CREATE}" = "true" ]; then
		return 0
	fi

	# Parse DB_HOST for a port or socket.
	local PARTS=(${DB_HOST//\:/ })
	local DB_HOSTNAME=${PARTS[0]}
	local DB_SOCK_OR_PORT=${PARTS[1]}
	local EXTRA=""

	if [ -n "$DB_HOSTNAME" ]; then
		if echo "$DB_SOCK_OR_PORT" | grep -qe '^[0-9]\{1,\}$'; then
			EXTRA=" --host=$DB_HOSTNAME --port=$DB_SOCK_OR_PORT --protocol=tcp"
		elif [ -n "$DB_SOCK_OR_PORT" ]; then
			EXTRA=" --socket=$DB_SOCK_OR_PORT"
		else
			EXTRA=" --host=$DB_HOSTNAME --protocol=tcp"
		fi
	fi

	# The test suite empties the database's tables on every run, so an existing test database is reused.
	mysql --user="$DB_USER" --password="$DB_PASS"$EXTRA --execute="CREATE DATABASE IF NOT EXISTS \`$DB_NAME\`;"
}

install_wp
install_woocommerce
install_test_suite
install_db

echo "WordPress ($WP_TESTS_TAG), WooCommerce ($WC_VERSION) and the test library are installed."
