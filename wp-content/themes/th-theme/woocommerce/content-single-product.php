<?php
/**
 * Custom WooCommerce single product layout.
 *
 * @package YourTheme
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! $product instanceof WC_Product ) {
	$product = wc_get_product( get_the_ID() );
}

if ( ! $product instanceof WC_Product ) {
	return;
}

/**
 * Hook: woocommerce_before_single_product.
 */
do_action( 'woocommerce_before_single_product' );

if ( post_password_required() ) {
	echo get_the_password_form(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	return;
}
?>

<div id="product-<?php the_ID(); ?>" <?php wc_product_class( 'cim-single-product', $product ); ?>>
	<div class="sec-wrap">
		<?php
		if ( function_exists( 'woocommerce_breadcrumb' ) ) {
			woocommerce_breadcrumb();
		}
		?>
		<div class="cim-product-layout">

			<section class="cim-product-gallery" aria-label="<?php esc_attr_e( 'Product images', 'woocommerce' ); ?>">
				<?php
				/**
				 * Outputs the sale badge and product image gallery.
				 */

				do_action( 'woocommerce_before_single_product_summary' );
				?>
			</section>

			<section class="cim-product-summary">
				<div class="summary entry-summary">
					<?php
					/**
					 * Outputs the title, rating, price, description,
					 * add-to-cart form, metadata and sharing options.
					 */
					remove_action(
						'woocommerce_single_product_summary',
						'woocommerce_template_single_add_to_cart',
						30
					);

					do_action( 'woocommerce_single_product_summary' );
					?>

					<?php
					$stock_status     = $product->get_stock_status();
					$managing_stock   = $product->managing_stock();
					$stock_quantity   = $product->get_stock_quantity();
					$low_stock_amount = $product->get_low_stock_amount();

					if ( 'outofstock' === $stock_status ) {
						$label = 'Out of stock';
						$class = 'out-of-stock';
					} elseif ( ! $managing_stock ) {
						$label = 'Not tracked';
						$class = 'not-tracked';
					} elseif ( null !== $stock_quantity && $stock_quantity <= 0 ) {
						$label = 'Out of stock';
						$class = 'out-of-stock';
					} elseif (
					null !== $stock_quantity
					&& $low_stock_amount > 0
					&& $stock_quantity <= $low_stock_amount
					) {
						$label = 'Low stock';
						$class = 'low-stock';
					} else {
						$label = 'In stock';
						$class = 'in-stock';
					}
					?>

					<p class="cim-product-stock cim-stock-<?php echo esc_attr( $class ); ?>">
						<?php echo esc_html( $label ); ?>

						<?php if ( $managing_stock && null !== $stock_quantity && 'outofstock' !== $stock_status ) : ?>
						(<?php echo esc_html( $stock_quantity ); ?> available)
						<?php endif; ?>
					</p>


					<?php

					global $product;

					if ( $product && $product->is_type( 'variable' ) ) {
						// Variable products can have variation-specific attributes.
						$attributes = $product->get_attributes();
					} elseif ( $product ) {
						$attributes = $product->get_attributes();
					}

					if ( ! empty( $attributes ) ) :
						?>

					<div class="product-specifications">
						<h2>Product Specifications</h2>

						<?php
						$attribute_order = array(
							'pa_led',
							'pa_sets',
							'pa_voltage',
							'pa_length',
						);

						$attributes = $product->get_attributes();
						?>

						<table class="product-specifications__table">
							<tbody>
								<?php foreach ( $attribute_order as $attribute_name ) : ?>
									<?php
									if ( ! isset( $attributes[ $attribute_name ] ) ) {
										continue;
									}

									$attribute = $attributes[ $attribute_name ];

									if ( ! $attribute->get_visible() ) {
										continue;
									}

									$name = $attribute->get_name();

									if ( $attribute->is_taxonomy() ) {
										$values = wc_get_product_terms(
											$product->get_id(),
											$name,
											array( 'fields' => 'names' )
										);

										$value = implode( ', ', $values );
									} else {
										$value = implode(
											', ',
											$attribute->get_options()
										);
									}

									if ( '' === $value ) {
										continue;
									}
									?>

								<tr>
									<th>
										<?php echo esc_html( wc_attribute_label( $name ) ); ?>
									</th>
									<td><?php echo esc_html( $value ); ?></td>
								</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>

					<?php endif; ?>



				</div>
			</section>

		</div>

		<section class="cim-product-extras">
			<?php
									remove_action(
										'woocommerce_after_single_product_summary',
										'woocommerce_output_product_data_tabs',
										10
									);
									remove_action(
										'woocommerce_after_single_product_summary',
										'woocommerce_output_related_products',
										20
									);
									/**
									 * Outputs product tabs, upsells and related products.
									 */
									do_action( 'woocommerce_after_single_product_summary' );
									?>
		</section>
	</div>
</div>
<?php
/**
 * Hook: woocommerce_after_single_product.
 */
do_action( 'woocommerce_after_single_product' );
?>