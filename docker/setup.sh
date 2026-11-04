#!/usr/bin/env bash
# Harbour & Co reference store — installs exactly what learners install in Labs 1–7 and
# seeds the finished store from the lab kits (labs/ is mounted read-only).
#   docker compose up -d && bash setup.sh
#   open http://localhost:8088   ·   admin: /wp-admin  (training1@tertiaryinfotech.com / harbour-local)
set -euo pipefail
cd "$(dirname "$0")"
URL="${WP_URL:-http://localhost:8088}"
wp() { docker compose run --rm -T cli "$@"; }

docker compose up -d
until curl -s -o /dev/null "$URL/wp-login.php"; do sleep 2; done

if ! wp core is-installed 2>/dev/null; then
  wp core install --url="$URL" --title="Harbour & Co" \
    --admin_user=training1@tertiaryinfotech.com --admin_password='harbour-local' \
    --admin_email=training1@tertiaryinfotech.com --skip-email
fi
wp config set WP_MEMORY_LIMIT 256M >/dev/null
wp rewrite structure '/%postname%/'

# Lab 1 — WooCommerce, Storefront, Harbour & Co Toolkit (the same zip learners upload)
wp plugin install woocommerce --activate
wp theme install storefront --activate
wp plugin install /labs/lab-01-launch-harbour-and-co/plugin/harbour-co-toolkit.zip --activate --force
# Lab 6 + Lab 7
wp plugin install all-in-one-seo-pack mailpoet --activate
wp plugin delete akismet hello >/dev/null 2>&1 || true

wp eval-file /seed/seed.php
wp eval-file /seed/seo.php      # Lab 6 reference: AIOSEO meta + alt text
# push the seeded orders into WooCommerce Analytics (same as Analytics > Settings > Import historical data)
wp eval 'Automattic\WooCommerce\Admin\ReportsSync::regenerate_report_data( null, false );'
wp action-scheduler run --batch-size=100 >/dev/null || true
wp transient delete --all >/dev/null
wp plugin list --fields=name,status,version
wp theme list --status=active --fields=name,version
echo "Open $URL"
