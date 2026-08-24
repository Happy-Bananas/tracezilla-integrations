<?php

if (! defined('ABSPATH')) {
    fwrite(STDERR, "Run this file through WP-CLI.\n");
    exit(1);
}

if (! class_exists('WooCommerce')) {
    throw new RuntimeException('WooCommerce must be active before products can be seeded.');
}

// Fixture creation can trigger customer and order notifications. The sandbox
// has no mail transport, and development data must never result in real mail.
add_filter('pre_wp_mail', static fn (): bool => false);

/**
 * Create or update a simple product identified by its stable SKU.
 *
 * @param array{
 *     sku: string,
 *     name: string,
 *     regular_price: string,
 *     sale_price?: string,
 *     manage_stock: bool,
 *     stock_quantity?: int
 * } $fixture
 */
function seed_simple_product(array $fixture): int
{
    $productId = wc_get_product_id_by_sku($fixture['sku']);
    $product = $productId === 0 ? new WC_Product_Simple() : wc_get_product($productId);

    if (! $product instanceof WC_Product_Simple) {
        throw new RuntimeException("SKU {$fixture['sku']} already belongs to a non-simple product.");
    }

    $product->set_name($fixture['name']);
    $product->set_sku($fixture['sku']);
    $product->set_status('publish');
    $product->set_catalog_visibility('visible');
    $product->set_regular_price($fixture['regular_price']);
    $product->set_sale_price($fixture['sale_price'] ?? '');
    $product->set_manage_stock($fixture['manage_stock']);

    if ($fixture['manage_stock']) {
        $quantity = $fixture['stock_quantity'] ?? 0;
        $product->set_stock_quantity($quantity);
        $product->set_stock_status($quantity > 0 ? 'instock' : 'outofstock');
    } else {
        $product->set_stock_quantity(null);
        $product->set_stock_status('instock');
    }

    return $product->save();
}

/** @return WC_Product_Variable */
function seed_variable_parent(string $sku, string $name, array $sizes): WC_Product_Variable
{
    $productId = wc_get_product_id_by_sku($sku);
    $product = $productId === 0 ? new WC_Product_Variable() : wc_get_product($productId);

    if (! $product instanceof WC_Product_Variable) {
        throw new RuntimeException("SKU {$sku} already belongs to a non-variable product.");
    }

    $attribute = new WC_Product_Attribute();
    $attribute->set_id(0);
    $attribute->set_name('Size');
    $attribute->set_options($sizes);
    $attribute->set_position(0);
    $attribute->set_visible(true);
    $attribute->set_variation(true);

    $product->set_name($name);
    $product->set_sku($sku);
    $product->set_status('publish');
    $product->set_catalog_visibility('visible');
    $product->set_attributes([$attribute]);
    $product->save();

    return $product;
}

function seed_variation(
    WC_Product_Variable $parent,
    string $sku,
    string $size,
    string $price,
    int $stockQuantity,
): int {
    $productId = wc_get_product_id_by_sku($sku);
    $variation = $productId === 0 ? new WC_Product_Variation() : wc_get_product($productId);

    if (! $variation instanceof WC_Product_Variation) {
        throw new RuntimeException("SKU {$sku} already belongs to a non-variation product.");
    }

    $variation->set_parent_id($parent->get_id());
    $variation->set_name("{$parent->get_name()} - {$size}");
    $variation->set_sku($sku);
    $variation->set_status('publish');
    $variation->set_regular_price($price);
    $variation->set_manage_stock(true);
    $variation->set_stock_quantity($stockQuantity);
    $variation->set_stock_status($stockQuantity > 0 ? 'instock' : 'outofstock');
    $variation->set_attributes(['size' => $size]);

    return $variation->save();
}

/**
 * Create or update a customer identified by email address.
 *
 * @param array{email: string, first_name: string, last_name: string, city: string, country: string} $fixture
 */
function seed_customer(array $fixture): WC_Customer
{
    $user = get_user_by('email', $fixture['email']);
    $customer = $user instanceof WP_User ? new WC_Customer($user->ID) : new WC_Customer();

    if ($customer->get_id() === 0) {
        $customer->set_email($fixture['email']);
        $customer->set_username(strstr($fixture['email'], '@', true));
        $customer->set_password('tracezilla');
    }

    $customer->set_first_name($fixture['first_name']);
    $customer->set_last_name($fixture['last_name']);
    $customer->set_display_name($fixture['first_name'].' '.$fixture['last_name']);
    $customer->set_billing_first_name($fixture['first_name']);
    $customer->set_billing_last_name($fixture['last_name']);
    $customer->set_billing_email($fixture['email']);
    $customer->set_billing_address_1('Integration Street 1');
    $customer->set_billing_postcode('1000');
    $customer->set_billing_city($fixture['city']);
    $customer->set_billing_country($fixture['country']);
    $customer->set_shipping_first_name($fixture['first_name']);
    $customer->set_shipping_last_name($fixture['last_name']);
    $customer->set_shipping_address_1('Integration Street 1');
    $customer->set_shipping_postcode('1000');
    $customer->set_shipping_city($fixture['city']);
    $customer->set_shipping_country($fixture['country']);
    $customer->save();

    return $customer;
}

/**
 * Create or restore an order identified by a fixture key.
 *
 * @param list<array{sku: string, quantity: int}> $lines
 */
function seed_order(
    string $fixtureKey,
    WC_Customer $customer,
    string $status,
    int $daysAgo,
    array $lines,
): WC_Order {
    $matches = wc_get_orders([
        'limit' => 1,
        'return' => 'objects',
        'meta_key' => '_tracezilla_fixture_key',
        'meta_value' => $fixtureKey,
    ]);
    $order = $matches[0] ?? wc_create_order(['customer_id' => $customer->get_id()]);

    if (! $order instanceof WC_Order) {
        throw new RuntimeException("Unable to create fixture order {$fixtureKey}.");
    }

    foreach ($order->get_items(['line_item', 'shipping', 'fee', 'coupon']) as $itemId => $item) {
        $order->remove_item($itemId);
    }

    $order->set_customer_id($customer->get_id());
    $order->set_created_via('tracezilla_fixture');
    $order->set_currency('DKK');
    $order->set_billing_first_name($customer->get_billing_first_name());
    $order->set_billing_last_name($customer->get_billing_last_name());
    $order->set_billing_email($customer->get_billing_email());
    $order->set_billing_address_1($customer->get_billing_address_1());
    $order->set_billing_postcode($customer->get_billing_postcode());
    $order->set_billing_city($customer->get_billing_city());
    $order->set_billing_country($customer->get_billing_country());
    $order->set_shipping_first_name($customer->get_shipping_first_name());
    $order->set_shipping_last_name($customer->get_shipping_last_name());
    $order->set_shipping_address_1($customer->get_shipping_address_1());
    $order->set_shipping_postcode($customer->get_shipping_postcode());
    $order->set_shipping_city($customer->get_shipping_city());
    $order->set_shipping_country($customer->get_shipping_country());
    $order->set_date_created(time() - ($daysAgo * DAY_IN_SECONDS));
    $order->update_meta_data('_tracezilla_fixture_key', $fixtureKey);

    foreach ($lines as $line) {
        $productId = wc_get_product_id_by_sku($line['sku']);
        $product = $productId === 0 ? false : wc_get_product($productId);
        if (! $product instanceof WC_Product) {
            throw new RuntimeException("Fixture order {$fixtureKey} references unknown SKU {$line['sku']}.");
        }
        $order->add_product($product, $line['quantity']);
    }

    $order->calculate_totals();
    $order->set_status($status);
    $order->save();

    return $order;
}

$simpleProducts = [
    [
        'sku' => 'TZ-SIMPLE-001',
        'name' => 'Tracezilla Test Banana',
        'regular_price' => '25.00',
        'manage_stock' => true,
        'stock_quantity' => 100,
    ],
    [
        'sku' => 'TZ-SALE-001',
        'name' => 'Tracezilla Test Pineapple',
        'regular_price' => '40.00',
        'sale_price' => '32.00',
        'manage_stock' => true,
        'stock_quantity' => 50,
    ],
    [
        'sku' => 'TZ-OUT-OF-STOCK',
        'name' => 'Tracezilla Test Mango',
        'regular_price' => '30.00',
        'manage_stock' => true,
        'stock_quantity' => 0,
    ],
];

$seeded = [];
foreach ($simpleProducts as $fixture) {
    $seeded[] = $fixture['sku'].' (#'.seed_simple_product($fixture).')';
}

// Together with the three products above and the variable parent below, these
// fixtures produce exactly 200 top-level products. Variations are not counted.
for ($number = 1; $number <= 196; $number++) {
    $sku = sprintf('TZ-CATALOG-%03d', $number);
    $seeded[] = $sku.' (#'.seed_simple_product([
        'sku' => $sku,
        'name' => sprintf('Tracezilla Catalog Product %03d', $number),
        'regular_price' => number_format(10 + ($number % 90), 2, '.', ''),
        'manage_stock' => true,
        'stock_quantity' => 20 + ($number % 181),
    ]).')';
}

$shirt = seed_variable_parent('TZ-TSHIRT', 'Tracezilla Test T-shirt', ['Small', 'Medium']);
$seeded[] = 'TZ-TSHIRT (#'.$shirt->get_id().')';
$seeded[] = 'TZ-TSHIRT-S (#'.seed_variation($shirt, 'TZ-TSHIRT-S', 'Small', '20.00', 20).')';
$seeded[] = 'TZ-TSHIRT-M (#'.seed_variation($shirt, 'TZ-TSHIRT-M', 'Medium', '20.00', 15).')';

WC_Product_Variable::sync($shirt, true);
wc_delete_product_transients($shirt->get_id());

$customerFixtures = [
    ['email' => 'anna@example.test', 'first_name' => 'Anna', 'last_name' => 'Andersen', 'city' => 'Copenhagen', 'country' => 'DK'],
    ['email' => 'ben@example.test', 'first_name' => 'Ben', 'last_name' => 'Berg', 'city' => 'Aarhus', 'country' => 'DK'],
    ['email' => 'carla@example.test', 'first_name' => 'Carla', 'last_name' => 'Christensen', 'city' => 'Odense', 'country' => 'DK'],
];

$customers = [];
foreach ($customerFixtures as $fixture) {
    $customer = seed_customer($fixture);
    $customers[$fixture['email']] = $customer;
}

$orders = [
    seed_order('TZ-ORDER-001', $customers['anna@example.test'], 'processing', 0, [
        ['sku' => 'TZ-SIMPLE-001', 'quantity' => 2],
        ['sku' => 'TZ-TSHIRT-S', 'quantity' => 1],
    ]),
    seed_order('TZ-ORDER-002', $customers['ben@example.test'], 'completed', 1, [
        ['sku' => 'TZ-SALE-001', 'quantity' => 3],
        ['sku' => 'TZ-CATALOG-001', 'quantity' => 1],
    ]),
    seed_order('TZ-ORDER-003', $customers['carla@example.test'], 'on-hold', 2, [
        ['sku' => 'TZ-TSHIRT-M', 'quantity' => 2],
        ['sku' => 'TZ-CATALOG-002', 'quantity' => 4],
    ]),
];

echo sprintf(
    "Seeded WooCommerce sandbox: %d top-level products, %d variations, %d customers, and %d orders.\n",
    200,
    2,
    count($customers),
    count($orders),
);
