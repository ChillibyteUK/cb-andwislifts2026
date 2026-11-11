<?php
/**
 * Block template for CB Cross Sell.
 *
 * The two-step andwis Group cross-sell card. The markup and behaviour live in
 * inc/cb-cross-sell.php so templates can output the same thing; this only
 * gathers the block's fields.
 *
 * @package cb-andwislifts2026
 */

defined( 'ABSPATH' ) || exit;

cb_cross_sell_render(
	array(
		'id'           => $block['anchor'] ?? $block['id'] ?? '',
		'class'        => $block['className'] ?? '',
		'style'        => get_field( 'style' ),
		'heading'      => get_field( 'heading' ),
		'body'         => get_field( 'body' ),
		'note'         => get_field( 'note' ),
		'group_link'   => get_field( 'group_link' ),
		'card_heading' => get_field( 'card_heading' ),
		'button_label' => get_field( 'button_label' ),
	)
);
