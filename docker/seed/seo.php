<?php
// Lab 5 Part A reference: AIOSEO title, description and focus keyphrase on every product, alt text on every image.
$data = json_decode( file_get_contents( '/seed/catalogue.json' ), true, 512, JSON_INVALID_UTF8_SUBSTITUTE );
if ( ! function_exists( 'aioseo' ) ) { WP_CLI::error( 'All in One SEO is not active' ); }
foreach ( $data['catalogue'] as $p ) {
	$id = wc_get_product_id_by_sku( $p['sku'] ); if ( ! $id ) { continue; }
	$post = \AIOSEO\Plugin\Common\Models\Post::getPost( $id );
	$post->post_id     = $id;
	$post->title       = $p['meta_title'];
	$post->description = $p['meta_desc'];
	$post->keyphrases  = wp_json_encode( array( 'focus' => array( 'keyphrase' => $p['keyword'], 'score' => 0, 'analysis' => array() ), 'additional' => array() ) );
	$post->save();
	$img = get_post_thumbnail_id( $id ); if ( $img ) { update_post_meta( $img, '_wp_attachment_image_alt', $p['alt'] ); }
}
WP_CLI::success( 'AIOSEO meta and alt text set on ' . count( $data['catalogue'] ) . ' products' );
