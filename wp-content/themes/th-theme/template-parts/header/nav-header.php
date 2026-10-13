<?php
/**
 * Header Navigation template.
 *
 * @package TH_THEME
 */

$header_menu_id = th_get_menu_id( 'th-header-menu' );
$header_menus   = wp_get_nav_menu_items( $header_menu_id );
if ( is_user_logged_in() ) :
	?>
<nav class="header-nav" aria-label="Primary Navigation">
	<?php
	if ( ! empty( $header_menus ) && is_array( $header_menus ) ) {
		?>
	<ul class="header-nav-list">
		<?php
		foreach ( $header_menus as $menu_item ) {
			if ( ! $menu_item->menu_item_parent ) {
				$child_menu_items = th_get_child_menu_items( $header_menus, $menu_item->ID );
				$has_children     = ! empty( $child_menu_items ) && is_array( $child_menu_items );
				$link_target      = ! empty( $menu_item->target ) && '_blank' === $menu_item->target ? '_blank' : '_self';
				$menu_classes     = ! empty( $menu_item->classes ) ? $menu_item->classes : array();
				$menu_classes     = implode( ' ', $menu_classes );
				if ( ! $has_children ) {
					?>
		<li class="header-nav-item">
			<a class="header-nav-link nav-link <?php echo esc_attr( $menu_classes ); ?>"
				href="<?php echo esc_url( $menu_item->url ); ?>" target="<?php echo esc_attr( $link_target ); ?>">
					<?php echo esc_html( $menu_item->title ); ?>
			</a>
		</li>
					<?php
				} else {
					?>
		<li class="header-nav-item js-nav-sub-trigger">
			<div class="header-nav-link js-nav-sub-parent">
					<?php echo esc_html( $menu_item->title ); ?>
				<span class="plus-icon"></span>
			</div>
			<ul class="header-nav-sub-list jp">
				<li class="header-nav-sub-item">
					<a class="header-nav-sub-link nav-link" href="<?php echo esc_url( $menu_item->url ); ?>"
						target="<?php echo esc_attr( $link_target ); ?>">
						<?php echo esc_html( $menu_item->title ) . 'トップ'; ?>
					</a>
				</li>
					<?php
					foreach ( $child_menu_items as $child_menu_item ) {
						// Calculate link target for child menu items.
						$child_link_target     = ! empty( $child_menu_item->target ) && '_blank' === $child_menu_item->target ? '_blank' : '_self';
						$grandchild_menu_items = th_get_child_menu_items( $header_menus, $child_menu_item->ID );
						$has_grandchildren     = ! empty( $grandchild_menu_items );
						?>
				<li class="header-nav-sub-item">
					<a class="header-nav-sub-link nav-link" href="<?php echo esc_url( $child_menu_item->url ); ?>"
						target="<?php echo esc_attr( $child_link_target ); ?>">
						<?php echo esc_html( $child_menu_item->title ); ?>
					</a>
						<?php if ( $has_grandchildren ) : ?>
					<ul class="header-nav-grandchild-list">
							<?php
							foreach ( $grandchild_menu_items as $grandchild_menu_item ) :
									$grandchild_link_target = ( ! empty( $grandchild_menu_item->target ) && '_blank' === $grandchild_menu_item->target ) ? '_blank' : '_self';
								?>
						<li class="header-nav-grandchild-item">
							<a class="header-nav-grandchild-link nav-link"
								href="<?php echo esc_url( $grandchild_menu_item->url ); ?>"
								target="<?php echo esc_attr( $grandchild_link_target ); ?>">
								<?php echo esc_html( $grandchild_menu_item->title ); ?>
							</a>
						</li>
						<?php endforeach; ?>
					</ul>
					<?php endif; ?>
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
		?>
	</ul>
		<?php
	}
	?>
</nav>
	<?php
endif;
