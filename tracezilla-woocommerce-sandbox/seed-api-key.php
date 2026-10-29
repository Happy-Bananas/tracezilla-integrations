<?php

if (! defined('ABSPATH')) {
    fwrite(STDERR, "Run this file through WP-CLI.\n");
    exit(1);
}

if (! function_exists('wc_api_hash')) {
    throw new RuntimeException('WooCommerce must be active before the REST API key can be created.');
}

// A fixed, easy-to-remember REST API key, so the integration works with the
// sandbox without creating keys by hand. Recreated on every start, so it also
// survives a reset. Never use these values on a real store.
const SANDBOX_CONSUMER_KEY = 'ck_sandbox';
const SANDBOX_CONSUMER_SECRET = 'cs_sandbox';

global $wpdb;

$admin = get_user_by('login', 'admin');
if (! $admin instanceof WP_User) {
    throw new RuntimeException('The sandbox admin user does not exist.');
}

$table = $wpdb->prefix.'woocommerce_api_keys';
$wpdb->delete($table, ['consumer_key' => wc_api_hash(SANDBOX_CONSUMER_KEY)]);
$inserted = $wpdb->insert($table, [
    'user_id' => $admin->ID,
    'description' => 'Sandbox (preinstalled)',
    'permissions' => 'read_write',
    'consumer_key' => wc_api_hash(SANDBOX_CONSUMER_KEY),
    'consumer_secret' => SANDBOX_CONSUMER_SECRET,
    'truncated_key' => substr(SANDBOX_CONSUMER_KEY, -7),
]);

if ($inserted !== 1) {
    throw new RuntimeException('Unable to create the sandbox REST API key: '.$wpdb->last_error);
}

echo 'REST API key: '.SANDBOX_CONSUMER_KEY.' / '.SANDBOX_CONSUMER_SECRET." (read/write)\n";
