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

// Ten food categories of twenty products each give exactly 200 top-level
// products. SKUs are TZ-<CATEGORY>-<NNN> and must match the tracezilla seed in
// tracezilla-woocommerce-php/seeds/woocommerce-sandbox.yaml.
$catalog = [
    'FRUIT' => [
        'Fairtrade Bananas 1 kg', 'Golden Pineapple', 'Ripe Mango', 'Red Apples 1 kg', 'Conference Pears 1 kg',
        'Organic Lemons 500 g', 'Seedless Grapes 500 g', 'Strawberries 400 g', 'Blueberries 250 g', 'Raspberries 125 g',
        'Navel Oranges 1 kg', 'Kiwi Fruit 6 pcs', 'Clementines 1 kg', 'Avocados 2 pcs', 'Watermelon',
        'Cherries 300 g', 'Plums 500 g', 'Peaches 4 pcs', 'Limes 4 pcs', 'Passion Fruit 3 pcs',
    ],
    'VEG' => [
        'Carrots 1 kg', 'Organic Potatoes 2 kg', 'Yellow Onions 1 kg', 'Garlic 3 pcs', 'Cherry Tomatoes 250 g',
        'Cucumber', 'Red Bell Pepper', 'Broccoli 400 g', 'Cauliflower', 'Iceberg Lettuce',
        'Baby Spinach 150 g', 'Leeks 500 g', 'Sweet Potatoes 1 kg', 'Courgette', 'Aubergine',
        'Mushrooms 250 g', 'Green Beans 300 g', 'Beetroot 1 kg', 'Kale 200 g', 'Celeriac',
    ],
    'DAIRY' => [
        'Aged Gouda 500 g', 'Organic Whole Milk 1 L', 'Skimmed Milk 1 L', 'Greek Yoghurt 1 kg', 'Salted Butter 250 g',
        'Crème Fraîche 200 ml', 'Whipping Cream 250 ml', 'Mature Cheddar 400 g', 'Danish Blue Cheese 150 g', 'Feta 200 g',
        'Mozzarella 125 g', 'Cottage Cheese 250 g', 'Vanilla Skyr 450 g', 'Brie 200 g', 'Parmesan 150 g',
        'Buttermilk 1 L', 'Chocolate Milk 500 ml', 'Free-range Eggs 10 pcs', 'Cream Cheese 200 g', 'Oat Drink 1 L',
    ],
    'BAKERY' => [
        'Sourdough Rye Bread 750 g', 'White Sandwich Loaf 500 g', 'Wholegrain Rolls 6 pcs', 'Croissants 4 pcs', 'Cinnamon Swirls 4 pcs',
        'Baguette', 'Seeded Crispbread 250 g', 'Spelt Bread 600 g', 'Danish Pastry 2 pcs', 'Pita Bread 6 pcs',
        'Tortilla Wraps 8 pcs', 'Bagels 4 pcs', 'Brioche Buns 4 pcs', 'Ciabatta', 'Rye Crackers 200 g',
        'Blueberry Muffins 2 pcs', 'Carrot Cake 400 g', 'Butter Cookies 300 g', 'Oat Biscuits 250 g', 'Gluten-free Bread 400 g',
    ],
    'MEAT' => [
        'Minced Beef 500 g', 'Chicken Breast 400 g', 'Pork Tenderloin 500 g', 'Smoked Bacon 150 g', 'Sirloin Steak 250 g',
        'Chicken Thighs 1 kg', 'Lamb Chops 400 g', 'Pork Sausages 400 g', 'Sliced Ham 100 g', 'Salami 80 g',
        'Turkey Breast Slices 100 g', 'Liver Pâté 200 g', 'Meatballs 500 g', 'Roast Beef Slices 100 g', 'Chorizo 200 g',
        'Duck Breast 350 g', 'Beef Burger Patties 4 pcs', 'Pulled Pork 400 g', 'Whole Chicken 1.4 kg', 'Veal Escalope 300 g',
    ],
    'FISH' => [
        'Smoked Salmon Slices 200 g', 'Salmon Fillet 400 g', 'Cod Fillet 400 g', 'Cooked Prawns 250 g', 'Pickled Herring 300 g',
        'Tuna in Olive Oil 160 g', 'Mackerel in Tomato 125 g', 'Fish Cakes 300 g', 'Fish Fingers 450 g', 'Crab Sticks 250 g',
        'Smoked Trout 150 g', 'Plaice Fillet 300 g', 'Mussels 1 kg', 'Anchovies 50 g', 'Sardines 120 g',
        'Hot-smoked Salmon 150 g', 'Haddock Fillet 400 g', 'Lumpfish Roe 100 g', 'Shrimp Salad 200 g', 'Whole Sea Bass 500 g',
    ],
    'COFFEE' => [
        'House Blend Coffee Beans', 'Dark Roast Coffee Beans 1 kg', 'Espresso Beans 500 g', 'Ground Filter Coffee 500 g', 'Decaf Ground Coffee 250 g',
        'Cold Brew Coffee Beans 500 g', 'Colombian Single Origin 250 g', 'Ethiopian Yirgacheffe 250 g', 'Instant Coffee 100 g', 'Coffee Pods 10 pcs',
        'Cappuccino Sachets 8 pcs', 'Organic Medium Roast 500 g', 'Brazil Santos Beans 1 kg', 'Guatemala Antigua 250 g', 'Kenya AA Beans 250 g',
        'Mocha Blend Ground 400 g', 'Swedish Light Roast 450 g', 'Iced Coffee 250 ml', 'Vanilla Coffee Syrup 250 ml', 'Cocoa Powder 250 g',
    ],
    'TEA' => [
        'English Breakfast Tea 50 bags', 'Earl Grey 100 g', 'Sencha Green Tea 100 g', 'Chamomile Tea 20 bags', 'Peppermint Tea 20 bags',
        'Rooibos 100 g', 'Jasmine Green Tea 50 g', 'Chai Spice Tea 20 bags', 'Lemon Ginger Tea 20 bags', 'Darjeeling 100 g',
        'Matcha Powder 30 g', 'Oolong 50 g', 'Hibiscus Berry Tea 20 bags', 'Lapsang Souchong 50 g', 'White Tea 30 g',
        'Assam Loose Leaf 125 g', 'Fruit Tea Mix 40 bags', 'Peach Iced Tea 1 L', 'Licorice Tea 20 bags', 'Nettle Tea 20 bags',
    ],
    'PANTRY' => [
        'Tomato Basil Sauce 500 g', 'Extra Virgin Olive Oil 500 ml', 'Spaghetti 500 g', 'Basmati Rice 1 kg', 'Rolled Oats 1 kg',
        'Honey Almond Granola 500 g', 'Wildflower Honey 350 g', 'Strawberry Jam 400 g', 'Peanut Butter 350 g', 'Chickpeas 400 g',
        'Red Lentils 500 g', 'Wheat Flour 2 kg', 'Cane Sugar 1 kg', 'Sea Salt 500 g', 'Black Pepper 50 g',
        'Balsamic Vinegar 250 ml', 'Dijon Mustard 200 g', 'Coconut Milk 400 ml', 'Chopped Tomatoes 400 g', 'Vegetable Stock 500 ml',
    ],
    'SNACK' => [
        'Sea Salt Crisps 150 g', 'Salted Peanuts 200 g', 'Dark Chocolate 70% 100 g', 'Milk Chocolate 100 g', 'Trail Mix 250 g',
        'Popcorn 90 g', 'Licorice 150 g', 'Wine Gums 200 g', 'Rice Cakes 130 g', 'Protein Bar 60 g',
        'Dried Apricots 200 g', 'Raisins 250 g', 'Roasted Almonds 150 g', 'Cashews 150 g', 'Pretzels 175 g',
        'Tortilla Chips 200 g', 'Oat Bars 6 pcs', 'Marzipan Bar 50 g', 'Fruit Leather 5 pcs', 'Vegetable Crisps 100 g',
    ],
];

// Products that represent specific integration test cases.
$overrides = [
    'TZ-FRUIT-001' => ['regular_price' => '25.00', 'stock_quantity' => 100],
    'TZ-FRUIT-002' => ['regular_price' => '40.00', 'sale_price' => '32.00', 'stock_quantity' => 50],
    'TZ-FRUIT-003' => ['regular_price' => '30.00', 'stock_quantity' => 0],
];
$variableSku = 'TZ-COFFEE-001';

$seeded = [];
$number = 0;
foreach ($catalog as $category => $names) {
    foreach ($names as $index => $name) {
        $number++;
        $sku = sprintf('TZ-%s-%03d', $category, $index + 1);
        if ($sku === $variableSku) {
            continue;
        }
        $seeded[$sku] = seed_simple_product(array_merge([
            'sku' => $sku,
            'name' => $name,
            'regular_price' => number_format(10 + ($number % 90), 2, '.', ''),
            'manage_stock' => true,
            'stock_quantity' => 20 + ($number % 181),
        ], $overrides[$sku] ?? []));
    }
}

$coffee = seed_variable_parent($variableSku, $catalog['COFFEE'][0], ['250 g', '1 kg']);
$seeded[$variableSku] = $coffee->get_id();
seed_variation($coffee, 'TZ-COFFEE-001-250G', '250 g', '45.00', 20);
seed_variation($coffee, 'TZ-COFFEE-001-1KG', '1 kg', '150.00', 15);

WC_Product_Variable::sync($coffee, true);
wc_delete_product_transients($coffee->get_id());

// Remove TZ- products that are no longer part of the catalog, e.g. after a
// fixture rename, so re-running the seeder restores exactly this catalog.
$removed = 0;
foreach (wc_get_products(['limit' => -1, 'type' => ['simple', 'variable'], 'status' => 'any']) as $product) {
    if (str_starts_with($product->get_sku(), 'TZ-') && ! isset($seeded[$product->get_sku()])) {
        $product->delete(true);
        $removed++;
    }
}

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
        ['sku' => 'TZ-FRUIT-001', 'quantity' => 2],
        ['sku' => 'TZ-COFFEE-001-250G', 'quantity' => 1],
    ]),
    seed_order('TZ-ORDER-002', $customers['ben@example.test'], 'completed', 1, [
        ['sku' => 'TZ-FRUIT-002', 'quantity' => 3],
        ['sku' => 'TZ-DAIRY-001', 'quantity' => 1],
    ]),
    seed_order('TZ-ORDER-003', $customers['carla@example.test'], 'on-hold', 2, [
        ['sku' => 'TZ-COFFEE-001-1KG', 'quantity' => 2],
        ['sku' => 'TZ-DAIRY-002', 'quantity' => 4],
    ]),
];

echo sprintf(
    "Seeded WooCommerce sandbox: %d top-level products, %d variations, %d customers, and %d orders. Removed %d obsolete products.\n",
    count($seeded),
    count(wc_get_product($coffee->get_id())->get_children()),
    count($customers),
    count($orders),
    $removed,
);
