<?php
/**
 * Build the Harbour & Co reference store — the state a learner reaches after Labs 1–5 —
 * from the lab kits themselves (labs/ is mounted read-only at /labs).
 *
 *   docker compose run --rm cli eval-file /seed/seed.php
 *
 * It exercises the same pieces the labs use: the WooCommerce CSV importer on both lab CSVs,
 * the Harbour & Co Toolkit block patterns, the homepage / policy templates, payments,
 * the Singapore shipping zone, WELCOME10 / RESORT15, a refunded + restocked order and an
 * attributed campaign order. Safe to re-run: existing items are reused, not duplicated.
 */
defined( 'ABSPATH' ) || exit;

$L    = '/labs';
$lab  = function ( $n ) use ( $L ) { $g = glob( "$L/lab-0$n-*" ); return $g[0]; };
$data = json_decode( file_get_contents( '/seed/catalogue.json' ), true, 512, JSON_INVALID_UTF8_SUBSTITUTE );
if ( ! $data ) { WP_CLI::error( 'Cannot read /seed/catalogue.json' ); }
$log  = function ( $m ) { WP_CLI::log( '  · ' . $m ); };

// run as the administrator, as a learner does (the CSV importer only creates categories for users who may manage terms)
$admin = get_users( array( 'role' => 'administrator', 'number' => 1 ) );
wp_set_current_user( $admin[0]->ID );
foreach ( array( 'billing_country', 'shipping_country' ) as $k ) { update_user_meta( $admin[0]->ID, $k, 'SG' ); }

require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';

function hc_image( $path, $alt = '' ) {
	$name = basename( $path );
	$found = get_posts( array( 'post_type' => 'attachment', 'name' => sanitize_title( pathinfo( $name, PATHINFO_FILENAME ) ), 'posts_per_page' => 1 ) );
	if ( $found ) { return $found[0]->ID; }
	$tmp = wp_tempnam( $name ); copy( $path, $tmp );
	$id  = media_handle_sideload( array( 'name' => $name, 'tmp_name' => $tmp ), 0 );
	if ( is_wp_error( $id ) ) { WP_CLI::warning( $id->get_error_message() ); return 0; }
	if ( $alt ) { update_post_meta( $id, '_wp_attachment_image_alt', $alt ); }
	return $id;
}
function hc_page( $title, $content, $slug = null, $template = '' ) {
	$p = get_page_by_path( $slug ?: sanitize_title( $title ) );
	$args = array( 'post_title' => $title, 'post_name' => $slug ?: sanitize_title( $title ), 'post_content' => $content, 'post_status' => 'publish', 'post_type' => 'page' );
	if ( $p ) { $args['ID'] = $p->ID; }
	$id = wp_insert_post( wp_slash( $args ) );
	if ( $template ) { update_post_meta( $id, '_wp_page_template', $template ); }
	return $id;
}

WP_CLI::log( 'Lab 1 — store settings, theme, toolkit, homepage, roles' );
$opts = array(
	'blogname' => 'Harbour & Co', 'blogdescription' => 'City wear for the tropics', 'timezone_string' => 'Asia/Singapore',
	'woocommerce_store_address' => '71 Ayer Rajah Crescent', 'woocommerce_store_address_2' => '#02-18',
	'woocommerce_store_city' => 'Singapore', 'woocommerce_store_postcode' => '139951', 'woocommerce_default_country' => 'SG',
	'woocommerce_allowed_countries' => 'specific', 'woocommerce_specific_allowed_countries' => array( 'SG' ),
	'woocommerce_ship_to_countries' => '', 'woocommerce_currency' => 'SGD', 'woocommerce_currency_pos' => 'left',
	'woocommerce_price_num_decimals' => 2, 'woocommerce_price_decimal_sep' => '.', 'woocommerce_price_thousand_sep' => ',',
	'woocommerce_default_customer_address' => 'base', 'woocommerce_calc_taxes' => 'yes', 'woocommerce_prices_include_tax' => 'yes',
	'woocommerce_tax_display_shop' => 'incl', 'woocommerce_tax_display_cart' => 'incl', 'woocommerce_enable_coupons' => 'yes',
	'woocommerce_onboarding_profile' => array( 'completed' => true, 'industry' => array( array( 'slug' => 'clothing_and_accessories' ) ) ),
	'woocommerce_task_list_hidden' => 'yes', 'woocommerce_coming_soon' => 'no', 'woocommerce_store_pages_only' => 'no',
);
foreach ( $opts as $k => $v ) { update_option( $k, $v ); }
global $wpdb;
if ( ! $wpdb->get_var( "SELECT tax_rate_id FROM {$wpdb->prefix}woocommerce_tax_rates WHERE tax_rate_country='SG' AND tax_rate_name='GST'" ) ) {
	WC_Tax::_insert_tax_rate( array( 'tax_rate_country' => 'SG', 'tax_rate' => '9.0000', 'tax_rate_name' => 'GST', 'tax_rate_priority' => 1, 'tax_rate_shipping' => 1, 'tax_rate_order' => 0, 'tax_rate_class' => '' ) );
}
$log( 'S$, 9% GST inclusive, Singapore only' );

$logo = hc_image( $lab( 1 ) . '/assets/images/harbour-co-logo-light.png', 'Harbour & Co logo' );
set_theme_mod( 'custom_logo', $logo );
$hero_img = hc_image( $lab( 1 ) . '/assets/images/hero-resort-linen.jpg', 'Resort Linen — breathable city wear for the tropics' );

// Homepage: the AI reference HTML in a Custom HTML block + the toolkit's New arrivals pattern
$home_html = file_get_contents( $lab( 1 ) . '/templates/harbour-home.html' );
$home_html = preg_replace( '/^<!--.*?-->\s*/s', '', $home_html );
$registry  = WP_Block_Patterns_Registry::get_instance();
$arrivals  = $registry->is_registered( 'harbour-co/new-arrivals' ) ? $registry->get_registered( 'harbour-co/new-arrivals' )['content'] : '';
$trust     = $registry->is_registered( 'harbour-co/policy-footer' ) ? $registry->get_registered( 'harbour-co/policy-footer' )['content'] : '';
if ( ! $arrivals ) { WP_CLI::error( 'Harbour & Co Toolkit patterns are not registered — is the plugin active?' ); }
$home = hc_page( 'Home', "<!-- wp:html -->\n" . $home_html . "\n<!-- /wp:html -->\n\n" . $arrivals, 'home', 'template-fullwidth.php' );
hc_page( 'About Us', "<!-- wp:html -->\n" . preg_replace( '/^<!--.*?-->\s*/s', '', file_get_contents( $lab( 1 ) . '/templates/harbour-about.html' ) ) . "\n<!-- /wp:html -->", 'about-us' );
update_option( 'show_on_front', 'page' ); update_option( 'page_on_front', $home );
$sample = get_page_by_path( 'sample-page' ); if ( $sample ) { wp_delete_post( $sample->ID, true ); }
$hello = get_page_by_path( 'hello-world', OBJECT, 'post' ); if ( $hello ) { wp_delete_post( $hello->ID, true ); }
$menu = wp_get_nav_menu_object( 'Main menu' );
if ( ! $menu ) {
	$mid = wp_create_nav_menu( 'Main menu' );
	foreach ( array( $home, wc_get_page_id( 'shop' ), get_page_by_path( 'about-us' ) ? get_page_by_path( 'about-us' )->ID : 0 ) as $i => $pg ) {
		if ( $pg ) { wp_update_nav_menu_item( $mid, 0, array( 'menu-item-object-id' => $pg, 'menu-item-object' => 'page', 'menu-item-type' => 'post_type', 'menu-item-status' => 'publish', 'menu-item-position' => $i + 1 ) ); }
	}
	$loc = get_theme_mod( 'nav_menu_locations', array() ); $loc['primary'] = $mid; set_theme_mod( 'nav_menu_locations', $loc );
}
if ( ! get_user_by( 'login', 'daniel.koh' ) ) {
	wp_insert_user( array( 'user_login' => 'daniel.koh', 'user_email' => 'daniel.koh@harbourandco.example', 'first_name' => 'Daniel', 'last_name' => 'Koh', 'role' => 'shop_manager', 'user_pass' => wp_generate_password() ) );
}
$log( 'Home (Custom HTML + New arrivals pattern), About Us, Daniel Koh = Shop manager' );

WP_CLI::log( 'Lab 2 Part A — Classic Cotton T-Shirt and Cotton Cap' );
$img_dir = $lab( 3 ) . '/assets/images';
function hc_term_path( $path ) {
	$parent = 0; $id = 0;
	foreach ( array_map( 'trim', explode( '>', $path ) ) as $name ) {
		$t = term_exists( $name, 'product_cat', $parent );
		if ( ! $t ) { $t = wp_insert_term( $name, 'product_cat', array( 'parent' => $parent ) ); }
		$id = (int) ( is_array( $t ) ? $t['term_id'] : $t ); $parent = $id;
	}
	return $id;
}
function hc_simple( $p, $img_dir ) {
	$id = wc_get_product_id_by_sku( $p['sku'] );
	$prod = $id ? wc_get_product( $id ) : new WC_Product_Simple();
	$prod->set_name( $p['name'] ); $prod->set_sku( $p['sku'] );
	$prod->set_regular_price( $p['regular'] ); $prod->set_sale_price( $p['sale'] );
	$prod->set_short_description( $p['short'] );
	$prod->set_description( '<p>' . implode( '</p><p>', $p['long'] ) . '</p><h3>Fabric &amp; fit</h3><p>' . esc_html( $p['fabric'] . ' · ' . $p['fit'] ) . '</p><h3>Care</h3><p>' . esc_html( $p['care'] ) . '</p>' );
	$prod->set_manage_stock( true ); $prod->set_stock_quantity( (int) $p['stock'] ); $prod->set_weight( $p['weight'] );
	$prod->set_category_ids( array( hc_term_path( $p['cat'] ) ) );
	$prod->set_tag_ids( array_map( function ( $t ) { $r = term_exists( $t, 'product_tag' ) ?: wp_insert_term( $t, 'product_tag' ); return (int) $r['term_id']; }, array_map( 'trim', explode( ',', $p['tags'] ) ) ) );
	$prod->set_image_id( hc_image( "$img_dir/{$p['sku']}.jpg", $p['alt'] ) );
	return $prod->save();
}
$by = array(); foreach ( $data['catalogue'] as $p ) { $by[ $p['sku'] ] = $p; }
$tee = hc_simple( $by['TSHIRT-CLASSIC-001'], $img_dir );
$cap = hc_simple( $by['CAP-COTTON-001'], $img_dir );
$log( "T-shirt #$tee, cap #$cap" );

WP_CLI::log( 'Lab 2 Part B — CSV importer (core + AI new-season CSV), variable polo, linked products' );
include_once WC_ABSPATH . 'includes/import/class-wc-product-csv-importer.php';
$map = array( 'Type' => 'type', 'SKU' => 'sku', 'Name' => 'name', 'Published' => 'published', 'Short description' => 'short_description',
	'Regular price' => 'regular_price', 'Sale price' => 'sale_price', 'Categories' => 'category_ids', 'Tags' => 'tag_ids',
	'In stock?' => 'stock_status', 'Stock' => 'stock_quantity', 'Weight (kg)' => 'weight' );
foreach ( array( 'harbour-co-products.csv', 'harbour-co-new-season-reference.csv' ) as $csv ) {
	$file = $lab( 3 ) . "/data/$csv";
	$imp  = new WC_Product_CSV_Importer( $file, array( 'parse' => true, 'update_existing' => false, 'lines' => -1, 'mapping' => $map, 'prevent_timeouts' => false ) );
	$r = $imp->import();
	$log( sprintf( '%s: imported %d, updated %d, skipped %d (already there), failed %d', $csv, count( $r['imported'] ), count( $r['updated'] ), count( $r['skipped'] ), count( $r['failed'] ) ) );
	foreach ( $r['failed'] as $f ) { WP_CLI::warning( $f->get_error_message() ); }
	if ( $r['failed'] ) { WP_CLI::error( "CSV import failed for $csv" ); }
}
foreach ( $data['catalogue'] as $p ) {           // attach starter images + long copy to the imported rows
	if ( empty( $p['csv'] ) ) { continue; }
	$pr = wc_get_product( wc_get_product_id_by_sku( $p['sku'] ) );
	if ( ! $pr->get_image_id() ) { $pr->set_image_id( hc_image( "$img_dir/{$p['sku']}.jpg", $p['alt'] ) ); }
	$pr->set_description( '<p>' . implode( '</p><p>', $p['long'] ) . '</p>' ); $pr->set_manage_stock( true );
	$pr->set_category_ids( array( hc_term_path( $p['cat'] ) ) ); $pr->save();
}
// variable polo
$pid  = wc_get_product_id_by_sku( 'POLO-PREMIUM-001' );
$polo = $pid ? wc_get_product( $pid ) : new WC_Product_Variable();
$P = $by['POLO-PREMIUM-001'];
$polo->set_name( $P['name'] ); $polo->set_sku( $P['sku'] ); $polo->set_short_description( $P['short'] );
$polo->set_description( '<p>' . implode( '</p><p>', $P['long'] ) . '</p>' );
$polo->set_category_ids( array( hc_term_path( $P['cat'] ) ) );
$polo->set_image_id( hc_image( "$img_dir/POLO-PREMIUM-001.jpg", $P['alt'] ) );
$a1 = new WC_Product_Attribute(); $a1->set_name( 'Size' ); $a1->set_options( array( 'S', 'M', 'L' ) ); $a1->set_visible( true ); $a1->set_variation( true );
$a2 = new WC_Product_Attribute(); $a2->set_name( 'Colour' ); $a2->set_options( array( 'Black', 'White' ) ); $a2->set_visible( true ); $a2->set_variation( true );
$polo->set_attributes( array( $a1, $a2 ) ); $polo->set_default_attributes( array( 'size' => 'M', 'colour' => 'Black' ) );
$pid = $polo->save();
$white = hc_image( "$img_dir/POLO-PREMIUM-001-white.jpg", 'White Harbour & Co premium polo shirt, flat lay' );
foreach ( $data['polo'] as $v ) {
	list( $size, $colour, $vsku, $price ) = $v;
	$vid = wc_get_product_id_by_sku( $vsku );
	$var = $vid ? wc_get_product( $vid ) : new WC_Product_Variation();
	$var->set_parent_id( $pid ); $var->set_sku( $vsku ); $var->set_attributes( array( 'size' => $size, 'colour' => $colour ) );
	$var->set_regular_price( $price ); $var->set_manage_stock( true ); $var->set_stock_quantity( 25 );
	if ( 'White' === $colour ) { $var->set_image_id( $white ); }
	$var->save();
}
WC_Product_Variable::sync( $pid );
$t = wc_get_product( $tee ); $t->set_upsell_ids( array( $pid ) );
$t->set_cross_sell_ids( array( $cap, wc_get_product_id_by_sku( 'TOTE-CANVAS-001' ) ) ); $t->save();
$log( 'Polo #' . $pid . ' with 6 variations; T-shirt up-sell + cross-sells' );

WP_CLI::log( 'Lab 3 — policy pages, payments, shipping zone' );
$pages = array( 'privacy-policy.html' => 'Privacy Policy', 'returns-and-refunds.html' => 'Returns & Refunds',
	'shipping-and-delivery.html' => 'Shipping & Delivery', 'payment-methods.html' => 'Payment Methods', 'terms-and-conditions.html' => 'Terms and Conditions' );
$pid_of = array();
foreach ( $pages as $f => $title ) {
	$html = preg_replace( '/^<!--.*?-->\s*/s', '', file_get_contents( $lab( 4 ) . "/templates/$f" ) );
	$pid_of[ $title ] = hc_page( $title, "<!-- wp:html -->\n$html\n<!-- /wp:html -->", str_replace( '.html', '', $f ) );
}
update_option( 'wp_page_for_privacy_policy', $pid_of['Privacy Policy'] );
update_option( 'woocommerce_terms_page_id', $pid_of['Terms and Conditions'] );
update_option( 'woocommerce_bacs_settings', array( 'enabled' => 'yes', 'title' => 'PayNow or bank transfer',
	'description' => 'PayNow to UEN 201900000H or bank transfer. Use your order number as the reference.',
	'instructions' => 'PayNow to UEN 201900000H (Harbour & Co Pte Ltd). Use your order number as the reference.' ) );
update_option( 'woocommerce_bacs_accounts', array( array( 'account_name' => 'Harbour & Co Pte Ltd', 'account_number' => '000-000000-0', 'bank_name' => 'DBS', 'sort_code' => '', 'iban' => '', 'bic' => '' ) ) );
update_option( 'woocommerce_cod_settings', array( 'enabled' => 'yes', 'title' => 'Pay at pickup (cash or card)',
	'description' => 'Pay at our Ayer Rajah studio when you collect.', 'instructions' => 'Pay when you collect at 71 Ayer Rajah Crescent.',
	'enable_for_methods' => array( 'local_pickup' ), 'enable_for_virtual' => 'no' ) );
$zones = WC_Shipping_Zones::get_zones(); $zone = null;
foreach ( $zones as $z ) { if ( 'Singapore' === $z['zone_name'] ) { $zone = new WC_Shipping_Zone( $z['id'] ); } }
if ( ! $zone ) {
	$zone = new WC_Shipping_Zone(); $zone->set_zone_name( 'Singapore' ); $zone->add_location( 'SG', 'country' ); $zone->save();
	foreach ( array( 'flat_rate', 'free_shipping', 'local_pickup' ) as $m ) { $zone->add_shipping_method( $m ); }
}
foreach ( $zone->get_shipping_methods() as $iid => $m ) {
	$o = "woocommerce_{$m->id}_{$iid}_settings";
	if ( 'flat_rate' === $m->id ) { update_option( $o, array( 'title' => 'Flat rate', 'tax_status' => 'taxable', 'cost' => '4.59' ) ); } // entered ex-GST: 4.59 + 9% GST = S$5.00
	if ( 'free_shipping' === $m->id ) { update_option( $o, array( 'title' => 'Free shipping', 'requires' => 'min_amount', 'min_amount' => '100', 'ignore_discounts' => 'no' ) ); }
	if ( 'local_pickup' === $m->id ) { update_option( $o, array( 'title' => 'Studio pickup (71 Ayer Rajah Crescent)', 'tax_status' => 'none', 'cost' => '' ) ); }
}
WC_Cache_Helper::get_transient_version( 'shipping', true );
$log( 'Four policies + T&C, PayNow/bank transfer, pay at pickup, zone Singapore (flat S$5 · free ≥ S$100 · pickup)' );

WP_CLI::log( 'Lab 4 — WELCOME10, an order with a restocked refund and a customer note' );
function hc_coupon( $code, $args ) {
	$id = wc_get_coupon_id_by_code( $code ); $c = new WC_Coupon( $id ?: 0 ); $c->set_code( $code );
	foreach ( $args as $k => $v ) { $c->{"set_$k"}( $v ); }
	return $c->save();
}
hc_coupon( 'WELCOME10', array( 'discount_type' => 'percent', 'amount' => 10, 'minimum_amount' => 50, 'usage_limit_per_user' => 1 ) );
$resort_ids = array_map( 'wc_get_product_id_by_sku', $data['resort'] );
hc_coupon( 'RESORT15', array( 'discount_type' => 'percent', 'amount' => 15, 'product_ids' => $resort_ids, 'usage_limit_per_user' => 1,
	'date_expires' => strtotime( '+14 days' ) ) );

$existing = wc_get_orders( array( 'billing_email' => 'tan.weiling@example.com', 'limit' => 1 ) );
if ( ! $existing ) {
	$addr = array( 'first_name' => 'Wei Ling', 'last_name' => 'Tan', 'email' => 'tan.weiling@example.com', 'phone' => '+65 9000 0000',
		'address_1' => '8 Marina Boulevard', 'address_2' => '#12-04', 'city' => 'Singapore', 'postcode' => '018981', 'country' => 'SG' );
	$o = wc_create_order();
	$v = wc_get_product( wc_get_product_id_by_sku( 'POLO-M-BLK' ) );
	$o->add_product( $v, 1 ); $tote_item = $o->add_product( wc_get_product( wc_get_product_id_by_sku( 'TOTE-CANVAS-001' ) ), 1 );
	$o->set_address( $addr, 'billing' ); $o->set_address( $addr, 'shipping' );
	$ship = new WC_Order_Item_Shipping(); $ship->set_method_title( 'Flat rate' ); $ship->set_method_id( 'flat_rate' ); $ship->set_total( 4.59 );
	$o->add_item( $ship );
	$o->set_payment_method( 'bacs' ); $o->set_payment_method_title( 'PayNow or bank transfer' );
	$o->calculate_totals(); $o->apply_coupon( 'WELCOME10' ); $o->calculate_totals();   // same maths as checkout: −S$6.49, total S$63.32
	$o->update_status( 'on-hold', 'Awaiting PayNow.' ); wc_reduce_stock_levels( $o->get_id() );
	$o->update_status( 'processing', 'PayNow received.' ); $o->update_status( 'completed', 'Shipped.' );
	$item = $o->get_item( $tote_item );
	$refund = wc_create_refund( array( 'order_id' => $o->get_id(), 'amount' => wc_format_decimal( $item->get_total() + $item->get_total_tax(), 2 ), 'reason' => 'Strap stitching loose',
		'line_items' => array( $tote_item => array( 'qty' => 1, 'refund_total' => $item->get_total(), 'refund_tax' => $item->get_taxes()['total'] ) ), 'restock_items' => true ) );
	if ( is_wp_error( $refund ) ) { WP_CLI::warning( $refund->get_error_message() ); }
	$o = wc_get_order( $o->get_id() );
	$o->add_order_note( 'Hi Wei Ling, thank you for the photo, and sorry about the strap. We have refunded the Canvas Tote Bag — S$22.41, the price after your WELCOME10 discount — to your original payment method within 5–7 working days. No need to send the bag back. Harbour & Co Customer Care', 1, true );
	$log( 'Order #' . $o->get_id() . ' completed, tote refunded S$' . $refund->get_amount() . ' and restocked' );
}

WP_CLI::log( 'Lab 5 Part B — RESORT15 order arriving from Instagram' );
$existing = wc_get_orders( array( 'billing_email' => 'nur.aisyah@example.com', 'limit' => 1 ) );
if ( ! $existing ) {
	$addr = array( 'first_name' => 'Nur', 'last_name' => 'Aisyah', 'email' => 'nur.aisyah@example.com', 'address_1' => '1 Tampines Central 5', 'city' => 'Singapore', 'postcode' => '529508', 'country' => 'SG' );
	$o = wc_create_order();
	$o->add_product( wc_get_product( wc_get_product_id_by_sku( 'RESORT-SHIRT-001' ) ), 1 );
	$o->add_product( wc_get_product( wc_get_product_id_by_sku( 'TROUSER-LINEN-001' ) ), 1 );
	$o->set_address( $addr, 'billing' ); $o->set_address( $addr, 'shipping' );
	$ship = new WC_Order_Item_Shipping(); $ship->set_method_title( 'Free shipping' ); $ship->set_method_id( 'free_shipping' ); $ship->set_total( 0 ); $o->add_item( $ship );
	$o->set_payment_method( 'bacs' ); $o->set_payment_method_title( 'PayNow or bank transfer' );
	$o->calculate_totals(); $o->apply_coupon( 'RESORT15' ); $o->calculate_totals();
	foreach ( array( 'source_type' => 'utm', 'utm_source' => 'instagram', 'utm_medium' => 'social', 'utm_campaign' => 'resort-linen', 'device_type' => 'Mobile' ) as $k => $v ) {
		$o->update_meta_data( "_wc_order_attribution_$k", $v );
	}
	$o->save(); $o->update_status( 'processing', 'PayNow received.' );
	$log( 'Order #' . $o->get_id() . ' with RESORT15, source instagram' );
}

// footer: Customer care links (block widget in Storefront's Footer Column 2)
$widgets = get_option( 'widget_block', array() ); $widgets[90] = array( 'content' => $trust );
$widgets[91] = array( 'content' => '<!-- wp:woocommerce/product-search /-->' );
$widgets[92] = array( 'content' => "<!-- wp:heading {\"level\":3} -->\n<h3 class=\"wp-block-heading\">Shop by category</h3>\n<!-- /wp:heading -->" );
$widgets[93] = array( 'content' => '<!-- wp:woocommerce/product-categories {"hasCount":false} /-->' );
update_option( 'widget_block', $widgets );
$sidebars = get_option( 'sidebars_widgets', array() ); $sidebars['footer-2'] = array( 'block-90' );
$sidebars['sidebar-1'] = array( 'block-91', 'block-92', 'block-93' ); update_option( 'sidebars_widgets', $sidebars );

flush_rewrite_rules();
WP_CLI::success( 'Harbour & Co reference store ready at ' . home_url( '/' ) );
