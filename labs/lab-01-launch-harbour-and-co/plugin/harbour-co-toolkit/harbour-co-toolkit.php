<?php
/**
 * Plugin Name:       Harbour & Co Toolkit
 * Plugin URI:        https://github.com/tertiarycourses/C6-Build-a-Professional-eCommerce-Store-with-Generative-AI
 * Description:       Course plugin for Build a Professional eCommerce Store with Generative AI (C6): Harbour & Co brand styles, storefront block patterns, and an optional block editor for WooCommerce products so the WordPress AI tools work on product pages.
 * Version:           1.0.0
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * Author:            Tertiary Infotech Academy Pte Ltd
 * License:           GPL-2.0-or-later
 * Text Domain:       harbour-co-toolkit
 */

defined( 'ABSPATH' ) || exit;

define( 'HARBOUR_CO_VERSION', '1.0.0' );

/* ------------------------------------------------------------------
 * 1. Brand styles (Lato, Harbour Navy / Sea Glass / Sand / Coral)
 * ------------------------------------------------------------------ */
function harbour_co_styles() {
	wp_enqueue_style( 'harbour-co-lato', 'https://fonts.googleapis.com/css2?family=Lato:wght@400;700;900&display=swap', array(), null );
	wp_enqueue_style( 'harbour-co', plugins_url( 'assets/harbour-co.css', __FILE__ ), array(), HARBOUR_CO_VERSION );
}
add_action( 'wp_enqueue_scripts', 'harbour_co_styles', 20 );
add_action( 'enqueue_block_editor_assets', 'harbour_co_styles' );

/* ------------------------------------------------------------------
 * 1b. Show Singapore dollars as "S$" (WooCommerce's default SGD symbol is "$")
 * ------------------------------------------------------------------ */
function harbour_co_sgd_symbol( $symbol, $currency ) {
	return 'SGD' === $currency ? 'S&#36;' : $symbol;
}
add_filter( 'woocommerce_currency_symbol', 'harbour_co_sgd_symbol', 10, 2 );

/* ------------------------------------------------------------------
 * 2. Block patterns: + (inserter) > Patterns > Harbour & Co
 * ------------------------------------------------------------------ */
function harbour_co_patterns() {
	if ( ! function_exists( 'register_block_pattern' ) ) {
		return;
	}
	register_block_pattern_category( 'harbour-co', array( 'label' => __( 'Harbour & Co', 'harbour-co-toolkit' ) ) );

	$patterns = array(
		'hero'          => array( 'Hero banner', harbour_co_pattern_hero() ),
		'collections'   => array( 'Three collections', harbour_co_pattern_collections() ),
		'new-arrivals'  => array( 'New arrivals grid', harbour_co_pattern_new_arrivals() ),
		'trust-bar'     => array( 'Trust bar (delivery, returns, payment, pickup)', harbour_co_pattern_trust() ),
		'policy-footer' => array( 'Policy links', harbour_co_pattern_policy_links() ),
	);
	foreach ( $patterns as $slug => $p ) {
		register_block_pattern(
			'harbour-co/' . $slug,
			array(
				'title'      => $p[0],
				'categories' => array( 'harbour-co' ),
				'content'    => $p[1],
			)
		);
	}
}
add_action( 'init', 'harbour_co_patterns' );

function harbour_co_pattern_hero() {
	return '<!-- wp:group {"className":"hc-hero","layout":{"type":"constrained"}} -->
<div class="wp-block-group hc-hero"><!-- wp:paragraph {"className":"hc-kicker"} -->
<p class="hc-kicker">New season · Resort Linen</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":1} -->
<h1 class="wp-block-heading">City wear for the tropics</h1>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Breathable linen and cotton, cut for the walk from the MRT to a cold meeting room.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="/shop/">Shop the collection</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->';
}

function harbour_co_pattern_collections() {
	$cols = array(
		array( 'The Everyday Edit', 'Cotton tees, polos and caps you reach for every morning.' ),
		array( 'Resort Linen', 'European linen shirts and wide-leg trousers for 32°C days.' ),
		array( 'Layers', 'A denim jacket and merino scarf for 20°C offices and long flights.' ),
	);
	$out = '<!-- wp:columns {"className":"hc-collections"} -->
<div class="wp-block-columns hc-collections">';
	foreach ( $cols as $c ) {
		$out .= '<!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">' . esc_html( $c[0] ) . '</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>' . esc_html( $c[1] ) . '</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->';
	}
	return $out . '</div>
<!-- /wp:columns -->';
}

function harbour_co_pattern_new_arrivals() {
	return '<!-- wp:heading {"textAlign":"center","className":"hc-section-title"} -->
<h2 class="wp-block-heading has-text-align-center hc-section-title">New arrivals</h2>
<!-- /wp:heading -->

<!-- wp:shortcode -->
[products limit="8" columns="4" orderby="date" order="DESC"]
<!-- /wp:shortcode -->';
}

function harbour_co_pattern_trust() {
	$items = array(
		array( 'Free delivery', 'on orders from S$100' ),
		array( '14-day returns', 'unworn, with tags' ),
		array( 'PayNow &amp; card', 'or pay at pickup' ),
		array( 'Studio pickup', '71 Ayer Rajah Crescent' ),
	);
	$out = '<!-- wp:columns {"className":"hc-trust"} -->
<div class="wp-block-columns hc-trust">';
	foreach ( $items as $i ) {
		$out .= '<!-- wp:column -->
<div class="wp-block-column"><!-- wp:paragraph -->
<p><strong>' . $i[0] . '</strong><br>' . $i[1] . '</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->';
	}
	return $out . '</div>
<!-- /wp:columns -->';
}

function harbour_co_pattern_policy_links() {
	return '<!-- wp:paragraph {"className":"hc-policy-links"} -->
<p class="hc-policy-links"><a href="/shipping-and-delivery/">Shipping &amp; Delivery</a> · <a href="/returns-and-refunds/">Returns &amp; Refunds</a> · <a href="/payment-methods/">Payment Methods</a> · <a href="/privacy-policy/">Privacy Policy</a></p>
<!-- /wp:paragraph -->';
}

/* ------------------------------------------------------------------
 * 3. Optional: block editor for products (Settings > Harbour & Co)
 *    The WordPress AI plugin draws its toolbar in the block editor;
 *    WooCommerce products still default to the classic editor.
 * ------------------------------------------------------------------ */
function harbour_co_block_editor_for_products( $can_edit, $post_type ) {
	if ( 'product' === $post_type && 'yes' === get_option( 'harbour_co_block_editor_products', 'no' ) ) {
		return true;
	}
	return $can_edit;
}
add_filter( 'use_block_editor_for_post_type', 'harbour_co_block_editor_for_products', 10, 2 );

function harbour_co_taxonomies_in_rest( $args ) {
	if ( 'yes' === get_option( 'harbour_co_block_editor_products', 'no' ) ) {
		$args['show_in_rest'] = true;
	}
	return $args;
}
add_filter( 'woocommerce_taxonomy_args_product_cat', 'harbour_co_taxonomies_in_rest' );
add_filter( 'woocommerce_taxonomy_args_product_tag', 'harbour_co_taxonomies_in_rest' );

function harbour_co_settings_page() {
	add_options_page( 'Harbour & Co', 'Harbour & Co', 'manage_options', 'harbour-co', 'harbour_co_render_settings' );
}
add_action( 'admin_menu', 'harbour_co_settings_page' );

function harbour_co_register_setting() {
	register_setting(
		'harbour_co',
		'harbour_co_block_editor_products',
		array(
			'type'              => 'string',
			'default'           => 'no',
			'sanitize_callback' => function ( $v ) {
				return 'yes' === $v ? 'yes' : 'no';
			},
		)
	);
}
add_action( 'admin_init', 'harbour_co_register_setting' );

function harbour_co_render_settings() {
	?>
	<div class="wrap">
		<h1>Harbour &amp; Co Toolkit</h1>
		<p>Brand styles and the <strong>Harbour &amp; Co</strong> block patterns are always on. Find the patterns in any page: <em>+ &gt; Patterns &gt; Harbour &amp; Co</em>.</p>
		<form method="post" action="options.php">
			<?php settings_fields( 'harbour_co' ); ?>
			<label>
				<input type="checkbox" name="harbour_co_block_editor_products" value="yes" <?php checked( get_option( 'harbour_co_block_editor_products', 'no' ), 'yes' ); ?> />
				Use the block editor for WooCommerce products (lets the WordPress AI plugin suggest titles, excerpts and alt text on products)
			</label>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}
