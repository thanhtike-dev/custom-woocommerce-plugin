<?php

if (! defined('ABSPATH')) {
    exit;
}

class CIM_Inventory_Dashboard
{
    /**
     * Register the dashboard shortcode.
     */
    public static function init()
    {
        add_shortcode(
            'custom_inventory_dashboard',
            [self::class, 'render']
        );
    }

    /**
     * Render the inventory dashboard.
     */
    public static function render()
    {
        // Require a logged-in administrator.
        if (! current_user_can('manage_woocommerce')) {
            return '<p>You do not have permission to access inventory management.</p>';
        }

        if (! function_exists('wc_get_products')) {
            return '<p>WooCommerce is not available.</p>';
        }

        // Retrieve simple products.
        $products = wc_get_products([
            'status' => ['publish', 'private', 'draft'],
            'type' => ['simple'],
            'limit' => 100,
            'orderby' => 'name',
            'order' => 'ASC',
        ]);

        $total_products = count($products);
        $low_stock = 0;
        $out_of_stock = 0;

        foreach ($products as $product) {
            if (! $product->managing_stock()) {
                continue;
            }

            if (! $product->is_in_stock()) {
                $out_of_stock++;
            } elseif (
                $product->get_stock_quantity() <=
                $product->get_low_stock_amount()
            ) {
                $low_stock++;
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
      <strong><?php echo esc_html($total_products); ?></strong>
    </div>

    <div class="cim-stat-card">
      <span>Low Stock</span>
      <strong><?php echo esc_html($low_stock); ?></strong>
    </div>

    <div class="cim-stat-card">
      <span>Out of Stock</span>
      <strong><?php echo esc_html($out_of_stock); ?></strong>
    </div>

  </section>

  uc <section class="cim-products">

    <div class="cim-section-header">
      <h2>Products</h2>
      <span><?php echo esc_html($total_products); ?> products</span>
    </div>

    <div class="cim-table-wrapper">
      <table class="cim-table">
        <thead>
          <tr>
            <th>Product</th>
            <th>SKU</th>
            <th>Category</th>
            <th>Stock</th>
            <th>Status</th>
          </tr>
        </thead>

        <tbody>
          <?php if (empty($products)) : ?>

          <tr>
            <td colspan="5">
              No products found. Add your first product in WooCommerce.
            </td>
          </tr>

          <?php else : ?>

          <?php foreach ($products as $product) : ?>
          <?php
                                    $product_id = $product->get_id();

                                    $categories = wc_get_product_category_list(
                                        $product_id,
                                        ', ',
                                        '',
                                        ''
                                    );

                                    $quantity = $product->get_stock_quantity();

                                    if (! $product->managing_stock()) {
                                        $stock_label = 'Not tracked';
                                    } elseif (! $product->is_in_stock()) {
                                        $stock_label = 'Out of stock';
                                    } elseif (
                                        $quantity <= $product->get_low_stock_amount()
                                    ) {
                                        $stock_label = 'Low stock';
                                    } else {
                                        $stock_label = 'In stock';
                                    }
                                    ?>
          <tr>
            <td>
              <strong>
                <?php echo esc_html($product->get_name()); ?>
              </strong>
            </td>
            <td>
              <?php echo esc_html($product->get_sku() ?: '—'); ?>
            </td>

            <td>
              <?php echo wp_kses_post($categories ?: 'Uncategorized'); ?>
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
                <?php echo esc_html($stock_label); ?>
              </span>
            </td>
          </tr>

          <?php endforeach; ?>

          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </section>

</div>

<?php

        return ob_get_clean();
    }
}