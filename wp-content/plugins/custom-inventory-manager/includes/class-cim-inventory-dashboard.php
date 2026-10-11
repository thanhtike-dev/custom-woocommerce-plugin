<?php
/**
 * Inventory dashboard for WooCommerce.
 *
 * Handles product inventory filtering, stock management, and pagination.
 *
 * @package CustomInventoryManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manages the inventory dashboard.
 *
 * @package CustomInventoryManager
 */
class CIM_Inventory_Dashboard {

	/**
	 * Number of products displayed per page.
	 *
	 * @var int
	 */
	public const PRODUCTS_PER_PAGE = 10;

	/**
	 * Register the dashboard shortcode.
	 *
	 * @return void
	 */
	public static function init() {
		add_shortcode(
			'custom_inventory_dashboard',
			array( self::class, 'render' )
		);

		add_action(
			'wp_ajax_cim_update_inventory',
			array( self::class, 'update_inventory' )
		);
	}

	/**
	 * Render the inventory dashboard.
	 *
	 * @return string
	 */
	public static function render() {
		// Require WooCommerce inventory management permissions.
		// phpcs:ignore WordPress.WP.Capabilities.Unknown -- WooCommerce defines manage_woocommerce.
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return '<p>You do not have permission to access inventory management.</p>';
		}

		if ( ! function_exists( 'wc_get_products' ) ) {
			return '<p>WooCommerce is not available.</p>';
		}

		/*
		 * These GET parameters only filter displayed results.
		 * They do not create, update, or delete inventory data.
		 */
		// phpcs:disable WordPress.Security.NonceVerification.Recommended

		$page = max(
			1,
			absint( wp_unslash( $_GET['inventory_page'] ?? 1 ) )
		);

		$search = sanitize_text_field(
			wp_unslash( $_GET['inventory_search'] ?? '' )
		);

		$category_id = absint(
			wp_unslash( $_GET['inventory_category'] ?? 0 )
		);

		$brand_id = absint(
			wp_unslash( $_GET['inventory_brand'] ?? 0 )
		);

		$stock_filter = sanitize_key(
			wp_unslash( $_GET['inventory_stock'] ?? 'all' )
		);

		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		$allowed_stock_filters = array(
			'all',
			'instock',
			'outofstock',
			'low_stock',
		);

		if ( ! in_array( $stock_filter, $allowed_stock_filters, true ) ) {
			$stock_filter = 'all';
		}

		$query_args = array(
			'status'   => array( 'publish', 'private', 'draft' ),
			'type'     => array( 'simple' ),
			'limit'    => self::PRODUCTS_PER_PAGE,
			'page'     => $page,
			'orderby'  => 'name',
			'order'    => 'ASC',
			'paginate' => true,
		);

		if ( '' !== $search ) {
			$query_args['s'] = $search;
		}

		$tax_query = array();

		if ( $category_id ) {
			$category = get_term( $category_id, 'product_cat' );

			if ( $category && ! is_wp_error( $category ) ) {
				$tax_query[] = array(
					'taxonomy' => 'product_cat',
					'field'    => 'term_id',
					'terms'    => array( $category_id ),
				);
			}
		}

		if ( $brand_id ) {
			$brand = get_term( $brand_id, 'product_brand' );

			if ( $brand && ! is_wp_error( $brand ) ) {
				$tax_query[] = array(
					'taxonomy' => 'product_brand',
					'field'    => 'term_id',
					'terms'    => array( $brand_id ),
				);
			}
		}

		if ( ! empty( $tax_query ) ) {
			if ( count( $tax_query ) > 1 ) {
				$tax_query['relation'] = 'AND';
			}

			$query_args['tax_query'] = $tax_query;
		}

		if (
			in_array(
				$stock_filter,
				array(
					'instock',
					'outofstock',
				),
				true
			)
		) {
			$query_args['stock_status'] = $stock_filter;
		}

		/*
		 * Low-stock filtering requires custom processing because
		 * WooCommerce does not provide a universal low-stock query.
		 */
		if ( 'low_stock' === $stock_filter ) {
			$low_stock_query_args = $query_args;

			unset( $low_stock_query_args['paginate'] );

			$low_stock_query_args['limit'] = -1;

			$all_products = wc_get_products( $low_stock_query_args );

			$all_products = array_values(
				array_filter(
					$all_products,
					static function ( $product ) {
						if (
							! $product->managing_stock() ||
							! $product->is_in_stock()
						) {
							return false;
						}

						$quantity  = $product->get_stock_quantity();
						$threshold = $product->get_low_stock_amount();

						return null !== $quantity
							&& $quantity <= $threshold;
					}
				)
			);

			$total     = count( $all_products );
			$max_pages = max(
				1,
				(int) ceil( $total / self::PRODUCTS_PER_PAGE )
			);

			$page = min( $page, $max_pages );

			$products = array_slice(
				$all_products,
				( $page - 1 ) * self::PRODUCTS_PER_PAGE,
				self::PRODUCTS_PER_PAGE
			);
		} else {
			$result = wc_get_products( $query_args );

			$products  = $result->products;
			$total     = $result->total;
			$max_pages = $result->max_num_pages;
		}

		$total_products = $total;

		/*
		 * Calculate inventory summary counts independently
		 * of the currently selected filters.
		 */
		$summary_products = wc_get_products(
			array(
				'status' => array( 'publish', 'private', 'draft' ),
				'type'   => array( 'simple' ),
				'limit'  => -1,
			)
		);

		$store_total_products = count( $summary_products );
		$low_stock            = 0;
		$out_of_stock         = 0;

		foreach ( $summary_products as $product ) {
			if ( ! $product->managing_stock() ) {
				continue;
			}

			$quantity = $product->get_stock_quantity();

			if ( ! $product->is_in_stock() ) {
				++$out_of_stock;
			} elseif (
				null !== $quantity &&
				$quantity <= $product->get_low_stock_amount()
			) {
				++$low_stock;
			}
		}

		ob_start();
		?>

<div class="cim-dashboard">

	<header class="cim-header">
		<div>
			<h1>Inventory Manager</h1>
			<p>Manage your products and stock in one place.</p>
		</div>
	</header>

	<section class="cim-stats">

		<div class="cim-stat-card">
			<span>Total Products</span>
			<strong><?php echo esc_html( $store_total_products ); ?></strong>
		</div>

		<div class="cim-stat-card">
			<span>Low Stock</span>
			<strong><?php echo esc_html( $low_stock ); ?></strong>
		</div>

		<div class="cim-stat-card">
			<span>Out of Stock</span>
			<strong><?php echo esc_html( $out_of_stock ); ?></strong>
		</div>

	</section>

	<section class="cim-products">

		<form method="get" class="cim-filters">

			<input type="search" name="inventory_search" placeholder="Search by product name..."
				value="<?php echo esc_attr( $search ); ?>">

			<select name="inventory_category">
				<option value="0">All Categories</option>

				<?php
						$categories = get_terms(
							array(
								'taxonomy'   => 'product_cat',
								'hide_empty' => false,
							)
						);

				if ( ! is_wp_error( $categories ) ) :
					foreach ( $categories as $category ) :
						?>
				<option value="<?php echo esc_attr( $category->term_id ); ?>"
						<?php selected( $category_id, $category->term_id ); ?>>
						<?php echo esc_html( $category->name ); ?>
				</option>
						<?php
					endforeach;
						endif;
				?>
			</select>

			<select name="inventory_stock">
				<?php
						$stock_options = array(
							'all'        => 'All Stock',
							'instock'    => 'In Stock',
							'low_stock'  => 'Low Stock',
							'outofstock' => 'Out of Stock',
						);

						foreach ( $stock_options as $value => $label ) :
							?>
				<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $stock_filter, $value ); ?>>
							<?php echo esc_html( $label ); ?>
				</option>
							<?php
						endforeach;
						?>
			</select>

			<select name="inventory_brand">
				<option value="0">All Brands</option>

				<?php
						$brands = get_terms(
							array(
								'taxonomy'   => 'product_brand',
								'hide_empty' => false,
							)
						);

				if ( ! is_wp_error( $brands ) ) :
					foreach ( $brands as $brand ) :
						?>
				<option value="<?php echo esc_attr( $brand->term_id ); ?>"
						<?php selected( $brand_id, $brand->term_id ); ?>>
						<?php echo esc_html( $brand->name ); ?>
				</option>
						<?php
					endforeach;
						endif;
				?>
			</select>

			<button type="submit">Search</button>

			<a href="<?php echo esc_url( get_permalink() ); ?>">
				Clear filters
			</a>

		</form>

		<div class="cim-section-header">
			<h2>Products</h2>
			<span>
				<?php echo esc_html( $total_products ); ?> products
			</span>
		</div>

		<div class="cim-table-wrapper">

			<table class="cim-table">

				<thead>
					<tr>
						<th>Product</th>
						<th>Brands</th>
						<th>Category</th>
						<th>Stock</th>
						<th>Status</th>
						<th>Actions</th>
					</tr>
				</thead>

				<tbody>

					<?php if ( empty( $products ) ) : ?>

					<tr>
						<td colspan="6">
							No products found. Add your first product in WooCommerce.
						</td>
					</tr>

					<?php else : ?>

						<?php foreach ( $products as $product ) : ?>
							<?php
									$product_id = $product->get_id();

									$categories = wc_get_product_category_list(
										$product_id,
										', ',
										'',
										''
									);

									$quantity = $product->get_stock_quantity();

							if ( ! $product->managing_stock() ) {
								$stock_label = 'Not tracked';
							} elseif ( ! $product->is_in_stock() ) {
								$stock_label = 'Out of stock';
							} elseif (
										null !== $quantity &&
										$quantity <= $product->get_low_stock_amount()
									) {
								$stock_label = 'Low stock';
							} else {
								$stock_label = 'In stock';
							}
							?>

					<tr>
						<td>
							<a href="<?php echo esc_url( $product->get_permalink() ); ?>">
								<?php echo esc_html( $product->get_name() ); ?>
							</a>
						</td>

						<td>
							<?php
								$product_brands = get_the_terms(
									$product_id,
									'product_brand'
								);

							if (
									$product_brands &&
									! is_wp_error( $product_brands )
								) {
								echo esc_html(
									implode(
										', ',
										wp_list_pluck(
											$product_brands,
											'name'
										)
									)
								);
							} else {
								echo '&mdash;';
							}
							?>
						</td>

						<td>
							<?php
											echo wp_kses_post(
												$categories
													? $categories
													: 'Uncategorized'
											);
							?>
						</td>

						<td>
							<?php
											echo esc_html(
												$product->managing_stock()
													? $quantity
													: '—'
											);
							?>
						</td>

						<td>
							<span class="cim-status">
								<?php echo esc_html( $stock_label ); ?>
							</span>
						</td>
						<td>
							<button type="button" class="cim-edit-button"
								data-product-id="<?php echo esc_attr( $product_id ); ?>"
								data-product-name="<?php echo esc_attr( $product->get_name() ); ?>"
								data-manage-stock="<?php echo $product->managing_stock() ? 'yes' : 'no'; ?>"
								data-stock-quantity="<?php echo esc_attr( null === $quantity ? '' : $quantity ); ?>">
								Edit
							</button>
						</td>
					</tr>

					<?php endforeach; ?>

					<?php endif; ?>

				</tbody>

			</table>

			<?php if ( $max_pages > 1 ) : ?>

			<nav class="cim-pagination" aria-label="Inventory pages">

				<?php
							$pagination_args = array(
								'inventory_search'   => $search,
								'inventory_category' => $category_id,
								'inventory_brand'    => $brand_id,
								'inventory_stock'    => $stock_filter,
							);

							// Previous page.
							if ( $page > 1 ) :
								$prev_url = add_query_arg(
									array_merge(
										$pagination_args,
										array(
											'inventory_page' => $page - 1,
										)
									),
									get_permalink()
								);
								?>
				<a href="<?php echo esc_url( $prev_url ); ?>" class="cim-pagination__prev">
					&laquo; Prev
				</a>
				<?php endif; ?>

				<?php
					// Display the first five pages and the last page.
					$visible_pages = array_unique(
						array_merge(
							range( 1, min( 5, $max_pages ) ),
							array( $max_pages )
						)
					);

					$previous_page = 0;

				foreach ( $visible_pages as $i ) :
					if ( $i <= $previous_page ) {
						continue;
					}

					if ( $i > $previous_page + 1 ) :
						?>
				<span class="cim-pagination__ellipsis" aria-hidden="true">
					&hellip;
				</span>
						<?php
					endif;

					$url = add_query_arg(
						array_merge(
							$pagination_args,
							array(
								'inventory_page' => $i,
							)
						),
						get_permalink()
					);
					?>

				<a href="<?php echo esc_url( $url ); ?>" <?php echo $page === $i ? 'aria-current="page"' : ''; ?>>
					<?php echo esc_html( $i ); ?>
				</a>

					<?php
					$previous_page = $i;
							endforeach;
				?>

				<?php
							// Next page.
				if ( $page < $max_pages ) :
					$next_url = add_query_arg(
						array_merge(
							$pagination_args,
							array(
								'inventory_page' => $page + 1,
							)
						),
						get_permalink()
					);
					?>
				<a href="<?php echo esc_url( $next_url ); ?>" class="cim-pagination__next">
					Next &raquo;
				</a>
				<?php endif; ?>

			</nav>

			<?php endif; ?>

		</div>

	</section>

	<!-- Modal to edit stock -->
	<div id="cim-inventory-modal" class="cim-modal" aria-hidden="true">
		<div class="cim-modal__overlay" data-cim-close></div>

		<section class="cim-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="cim-modal-title">
			<header class="cim-modal__header">
				<h2 id="cim-modal-title">Edit Inventory</h2>

				<button type="button" class="cim-modal__close" data-cim-close aria-label="Close modal">&times;</button>
			</header>

			<form id="cim-inventory-form">
				<input type="hidden" name="action" value="cim_update_inventory">
				<input type="hidden" name="nonce" value="">

				<input type="hidden" name="product_id" value="">

				<div class="cim-modal__body">
					<p class="cim-modal__product-name"></p>

					<label class="cim-checkbox">
						<input type="checkbox" name="manage_stock" value="yes">
						Manage stock for this product
					</label>

					<div class="cim-stock-fields">
						<label for="cim-stock-quantity">
							Stock quantity
						</label>

						<input id="cim-stock-quantity" type="number" name="stock_quantity" min="0" step="1"
							inputmode="numeric">
					</div>

					<p class="cim-modal__message" role="status" aria-live="polite"></p>
				</div>

				<footer class="cim-modal__footer">
					<button type="button" class="cim-button cim-button--secondary" data-cim-close>
						Cancel
					</button>

					<button type="submit" class="cim-button cim-button--primary">
						Save changes
					</button>
				</footer>
			</form>
		</section>
	</div>

</div>

		<?php

		return ob_get_clean();
	}


	/**
	 * Update inventory settings for a simple product.
	 *
	 * @return void
	 */
	public static function update_inventory() {
		check_ajax_referer( 'cim_update_inventory', 'nonce' );

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error(
				array( 'message' => 'You do not have permission to update inventory.' ),
				403
			);
		}

		$product_id = isset( $_POST['product_id'] )
			? absint( wp_unslash( $_POST['product_id'] ) )
			: 0;

		if (
			! $product_id ||
			! current_user_can( 'edit_post', $product_id )
		) {
			wp_send_json_error(
				array( 'message' => 'You cannot edit this product.' ),
				403
			);
		}

		$product = wc_get_product( $product_id );

		if ( ! $product || ! $product->is_type( 'simple' ) ) {
			wp_send_json_error(
				array( 'message' => 'The product is invalid or unsupported.' ),
				400
			);
		}

		$manage_stock = isset( $_POST['manage_stock'] )
			? sanitize_key( wp_unslash( $_POST['manage_stock'] ) )
			: '';

		/*
		 * Only validate and change quantity when stock management is enabled.
		 * Disabling stock management leaves the stored quantity untouched.
		 */
		if ( 'yes' === $manage_stock ) {
			if ( ! isset( $_POST['stock_quantity'] ) ) {
				wp_send_json_error(
					array( 'message' => 'Stock quantity is required.' ),
					400
				);
			}

			$raw_quantity = trim(
				(string) wp_unslash( $_POST['stock_quantity'] )
			);

			$quantity = filter_var(
				$raw_quantity,
				FILTER_VALIDATE_INT
			);

			if ( false === $quantity || $quantity < 0 ) {
				wp_send_json_error(
					array(
						'message' => 'Enter a valid, non-negative whole-number quantity.',
					),
					400
				);
			}
		}

		try {
			$product->set_manage_stock( 'yes' === $manage_stock );

			if ( 'yes' === $manage_stock ) {
				$product->set_stock_quantity( $quantity );
			}

			$product->save();

			/*
			 * Reload the product so the response reflects WooCommerce's
			 * saved stock status and quantity.
			 */
			$product = wc_get_product( $product_id );

			if ( ! $product ) {
				throw new Exception( 'Could not reload the saved product.' );
			}
		} catch ( Throwable $exception ) {
			wp_send_json_error(
				array(
					'message' => 'The inventory update failed. Please try again.',
				),
				500
			);
		}

		$saved_quantity      = $product->get_stock_quantity();
		$low_stock_threshold = $product->get_low_stock_amount();

		// Fall back to the store-wide threshold when no product-level
		// threshold is configured.
		if ( '' === $low_stock_threshold || null === $low_stock_threshold ) {
			$low_stock_threshold = get_option(
				'woocommerce_notify_low_stock_amount',
				5
			);
		}

		if ( ! $product->managing_stock() ) {
			$stock_label = 'Not tracked';
		} elseif ( ! $product->is_in_stock() ) {
			$stock_label = 'Out of stock';
		} elseif (
			null !== $saved_quantity &&
			$saved_quantity <= (int) $low_stock_threshold
		) {
			$stock_label = 'Low stock';
		} else {
			$stock_label = 'In stock';
		}

		wp_send_json_success(
			array(
				'product_id'     => $product_id,
				'manage_stock'   => $product->managing_stock(),
				'stock_quantity' => $saved_quantity,
				'stock_label'    => $stock_label,
				'message'        => 'Inventory updated successfully.',
			)
		);
	}
}