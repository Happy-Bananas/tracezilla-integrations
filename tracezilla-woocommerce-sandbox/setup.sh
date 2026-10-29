#!/bin/sh
set -eu

until wp core is-installed --path=/var/www/html 2>/dev/null; do
    if [ -f /var/www/html/wp-config.php ]; then
        wp core install \
            --path=/var/www/html \
            --url=http://localhost:8080 \
            --title="Tracezilla WooCommerce Sandbox" \
            --admin_user=admin \
            --admin_password=admin \
            --admin_email=developer@example.com \
            --skip-email && break
    fi
    sleep 2
done

# The WordPress image only copies core files into an empty volume, so upgrade
# existing installations to the pinned version explicitly.
if [ "$(wp core version --path=/var/www/html)" != "$WORDPRESS_VERSION" ]; then
    wp core update --version="$WORDPRESS_VERSION" --force --path=/var/www/html
    wp core update-db --path=/var/www/html
fi

if [ "$(wp plugin get woocommerce --field=version --path=/var/www/html 2>/dev/null)" != "$WOOCOMMERCE_VERSION" ]; then
    wp plugin install woocommerce --version="$WOOCOMMERCE_VERSION" --force --path=/var/www/html
fi
wp plugin activate woocommerce --path=/var/www/html
wp wc update --path=/var/www/html
# Pretty permalinks, as on real stores, so the REST API is at /wp-json/.
wp rewrite structure '/%postname%/' --hard --path=/var/www/html
wp option update woocommerce_onboarding_profile '{"skipped":true}' --format=json --path=/var/www/html
wp eval-file /seed-products.php --path=/var/www/html

# Well-known local credentials, also applied to existing installations.
wp user update admin --user_pass=admin --skip-email --path=/var/www/html
wp eval-file /seed-api-key.php --path=/var/www/html

echo "WooCommerce sandbox is ready at http://localhost:8080"
echo "WordPress login: admin / admin"
