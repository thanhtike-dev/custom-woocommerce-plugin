<?php
/**
 * Footer Navigation template.
 *
 * @package TH_THEME
 */

$header_menu_id = th_get_menu_id( 'th-footer-menu' );
$header_menus   = wp_get_nav_menu_items( $header_menu_id );

if ( ! is_array( $header_menus ) || empty( $header_menus ) ) {
	$header_menus = array(); // Ensure $header_menus is an array for further operations
}

// Split the menu items into two arrays
$menu_count       = count( $header_menus );
$half_index       = ceil( $menu_count / 2 );
$left_menu_items  = array_slice( $header_menus, 0, $half_index );
$right_menu_items = array_slice( $header_menus, $half_index );

/**
 * Render menu items as a list with optional child items.
 *
 * @param array $menu_items Menu items to render.
 */
function render_menu_items( $menu_items ) {
	if ( ! is_array( $menu_items ) || empty( $menu_items ) ) {
		return;
	}

	foreach ( $menu_items as $menu_item ) {
		if ( ! $menu_item->menu_item_parent ) {
			$child_menu_items = th_get_child_menu_items( $menu_items, $menu_item->ID );
			$has_children     = ! empty( $child_menu_items ) && is_array( $child_menu_items );
			$link_target      = ! empty( $menu_item->target ) && '_blank' === $menu_item->target ? '_blank' : '_self';
			if ( ! $has_children ) {
				?>
<li class="footer-nav-item">
	<a class="footer-nav-link nav-link" href="<?php echo esc_url( $menu_item->url ); ?>"
		target="<?php echo esc_attr( $link_target ); ?>">
				<?php echo esc_html( $menu_item->title ); ?>
	</a>
</li>
				<?php
			} else {
				?>
<li class="footer-nav-item js-nav-sub-trigger">
	<div class="footer-nav-link js-nav-sub-parent">
				<?php echo esc_html( $menu_item->title ); ?>
	</div>
	<ul class="footer-nav-sub-list">
				<?php
				foreach ( $child_menu_items as $child_menu_item ) {
					// Calculate link target for child menu items
					$child_link_target = ! empty( $child_menu_item->target ) && '_blank' === $child_menu_item->target ? '_blank' : '_self';
					?>
		<li class="footer-nav-sub-item">
			<a class="footer-nav-sub-link" href="<?php echo esc_url( $child_menu_item->url ); ?>"
				target="<?php echo esc_attr( $child_link_target ); ?>">
					<?php echo esc_html( $child_menu_item->title ); ?>
			</a>
		</li>
					<?php
				}
				?>
	</ul>
</li>
				<?php
			}
			?>
			<?php
		}
	}
}
?>

<div class="footer-wrap d-flex justify-between">
	<a class="logo-link" href="<?php echo esc_url( home_url( '/' ) ); ?>">
		<img class="footer-logo" src="<?php echo th_img_path( 'common/logo_main_pc.png' ); ?>"
			alt="<?php echo get_bloginfo( 'name' ); ?>" width="190" height="43">
	</a>
	<nav class="footer-nav-box" aria-label="Footer Navigation">
		<ul class="footer-nav-list">
			<?php render_menu_items( $left_menu_items ); ?>
		</ul>
		<ul class="footer-nav-list">
			<?php render_menu_items( $right_menu_items ); ?>
		</ul>
	</nav>
</div>
