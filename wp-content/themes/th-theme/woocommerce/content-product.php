<?php
/**
 * Product card template.
 *
 * @package CGMGL_WP
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! $product instanceof WC_Product || ! $product->is_visible() ) {
	return;
}

$stock_status   = $product->get_stock_status();
$managing_stock = $product->managing_stock();
$quantity       = $product->get_stock_quantity();
$low_stock      = $product->get_low_stock_amount();

if ( 'outofstock' === $stock_status ) {
	$status_label = 'Out of stock';
	$status_class = 'out-of-stock';
} elseif ( ! $managing_stock ) {
	$status_label = 'In stock';
	$status_class = 'in-stock';
} elseif ( null !== $quantity && $quantity <= 0 ) {
	$status_label = 'Out of stock';
	$status_class = 'out-of-stock';
} elseif (
	null !== $quantity
	&& $low_stock > 0
	&& $quantity <= $low_stock
) {
	$status_label = 'Low stock';
	$status_class = 'low-stock';
} else {
	$status_label = 'In stock';
	$status_class = 'in-stock';
}
?>

<li <?php wc_product_class( 'cim-product-card', $product ); ?>>

	<a class="cim-product-card__link" href="<?php echo esc_url( get_permalink() ); ?>">

		<div class="cim-product-card__image">
			<?php
			echo wp_kses_post( $product->get_image( 'woocommerce_thumbnail' ) );
			?>
		</div>

		<h2 class="cim-product-card__title">
			<?php echo esc_html( $product->get_name() ); ?>
		</h2>

		<div class="cim-product-card__price">
			<?php echo wp_kses_post( $product->get_price_html() ); ?>
		</div>

		<p class="cim-product-card__stock cim-stock-<?php echo esc_attr( $status_class ); ?>">
			<?php echo esc_html( $status_label ); ?>

			<?php if ( $managing_stock && null !== $quantity && 'outofstock' !== $stock_status ) : ?>
			<span>
				(<?php echo esc_html( $quantity ); ?> available)
			</span>
			<?php endif; ?>
		</p>
	</a>


</li>