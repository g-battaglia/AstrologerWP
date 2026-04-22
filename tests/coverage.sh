#!/usr/bin/env bash
#
# coverage.sh — Generate PHP (PHPUnit) + JS (Jest) coverage reports.
#
# Outputs:
#   coverage/clover-unit.xml       — PHP unit coverage (clover)
#   coverage/html-unit/            — PHP unit coverage (html)
#   coverage/jest/                 — Jest coverage reports
#
# If neither pcov nor xdebug is available, PHP coverage is skipped with a
# warning but the Jest coverage run still proceeds.
#
# Usage:
#   ./tests/coverage.sh
#
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "${ROOT_DIR}"

mkdir -p coverage

echo "=============================================="
echo " PHP coverage (PHPUnit)"
echo "=============================================="

PHP_COVERAGE_AVAILABLE=0
if php -m | grep -qiE '^(pcov|xdebug)$'; then
	PHP_COVERAGE_AVAILABLE=1
fi

if [ "${PHP_COVERAGE_AVAILABLE}" -eq 1 ]; then
	vendor/bin/phpunit \
		-c phpunit-unit.xml.dist \
		--coverage-clover coverage/clover-unit.xml \
		--coverage-html coverage/html-unit \
		|| echo "WARN: phpunit exited non-zero; continuing."
else
	echo "WARN: Neither pcov nor xdebug is loaded — PHP coverage skipped."
	echo "      Install either extension (e.g. 'pecl install pcov') to enable."
fi

echo
echo "=============================================="
echo " JS coverage (Jest)"
echo "=============================================="

npx jest --coverage --coverageDirectory=coverage/jest \
	|| echo "WARN: jest exited non-zero; continuing."

echo
echo "=============================================="
echo " Coverage summary"
echo "=============================================="

if [ -f coverage/clover-unit.xml ]; then
	echo "PHP clover: coverage/clover-unit.xml"
	echo "PHP html:   coverage/html-unit/index.html"
fi

if [ -d coverage/jest ]; then
	echo "Jest:       coverage/jest/lcov-report/index.html"
fi

echo "Done."
