<?php
/**
 * Plugin Name: Sandbox REST API over HTTP
 * Description: Lets the local sandbox accept WooCommerce REST API keys over plain HTTP. Sandbox only.
 *
 * WooCommerce accepts a consumer key and secret only over HTTPS. Over HTTP it
 * requires OAuth 1.0a signatures that are checked against the site URL
 * (http://localhost:8080), which never matches requests from a Docker
 * container (http://host.docker.internal:8080). The disposable sandbox has no
 * certificate, so REST API requests are treated as secure here.
 *
 * Never copy this to a real store. Real stores must use HTTPS.
 */

$requestUri = $_SERVER['REQUEST_URI'] ?? '';
if (str_starts_with($requestUri, '/wp-json/') || isset($_GET['rest_route'])) {
    $_SERVER['HTTPS'] = 'on';
}
