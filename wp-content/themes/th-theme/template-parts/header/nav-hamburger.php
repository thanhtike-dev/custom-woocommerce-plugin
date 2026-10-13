<?php
/**
 * Hamburger Navigation template.
 *
 * @package TH_THEME
 */

$header_menu_id = th_get_menu_id( 'th-hamburger-menu' );
$header_menus   = wp_get_nav_menu_items( $header_menu_id );

?>
<nav id="hamburger-menu" class="hamburger-menu js-hamburger-menu" aria-label="Main Navigation">
	<div class="hamburger-menu-inner">
		<div class="sec-wrap hamburger-menu-wrap">
			<div class="hamburger-menu-cols">
				<div class="hamburger-menu-left">
					<a class="hamburger-logo-link" href="<?php echo get_home_url(); ?>">
						<img class="hamburger-logo-img" src="<?php echo th_img_path( 'common/logo_main_pc.png' ); ?>"
							alt="<?php echo get_bloginfo( 'name' ); ?>" width="280" height="102">
					</a>
					<div class="hamburger-copyright fw-normal sm-pt40">
						<ul class="d-flex col-gap-30 row-gap-20">
							<li><a href="#" class="policy-link">個⼈情報保護⽅針</a></li>
							<li><a href="#" class="policy-link">プライバシーポリシー</a></li>
						</ul>
						<p class="copyright-txt en mt30 sm-mt60">Software Science, Inc. All Rights Reserved.</p>
					</div>
				</div>
				<div class="hamburger-menu-right fs24 sm-fs20">
					<?php
					if ( ! empty( $header_menus ) && is_array( $header_menus ) ) {
						// Get the current page URL
						global $wp;
						$current_url = trailingslashit( home_url( $wp->request ) );
						?>
					<ul class="hamburger-menu-list">
						<?php
						foreach ( $header_menus as $menu_item ) {
							if ( ! $menu_item->menu_item_parent ) {
								$child_menu_items = th_get_child_menu_items( $header_menus, $menu_item->ID );
								$has_children     = ! empty( $child_menu_items ) && is_array( $child_menu_items );
								$link_target      = ! empty( $menu_item->target ) && '_blank' === $menu_item->target ? '_blank' : '_self';
								$current_class    = ( esc_url( $menu_item->url ) === $current_url ) ? 'current' : '';
								if ( ! $has_children ) {
									?>
						<li class="hamburger-menu-item">
							<a class="hamburger-menu-link nav-link <?php echo esc_attr( $current_class ); ?>"
								href="<?php echo esc_url( $menu_item->url ); ?>"
								target="<?php echo esc_attr( $link_target ); ?>">
									<?php echo esc_html( $menu_item->title ); ?>
							</a>
						</li>
									<?php
								} else {
									?>
						<li class="hamburger-menu-item">
							<a class="hamburger-menu-link <?php echo esc_attr( $active_class ); ?>"
								href="<?php echo esc_url( $menu_item->url ); ?>"
								target="<?php echo esc_attr( $link_target ); ?>">
									<?php echo esc_html( $menu_item->title ); ?>
							</a>
							<ul class="hamburger-menu-sub-list">
									<?php
									foreach ( $child_menu_items as $child_menu_item ) {
										// Calculate link target for child menu items
										$child_link_target = ! empty( $child_menu_item->target ) && '_blank' === $child_menu_item->target ? '_blank' : '_self';
										?>
								<li class="hamburger-menu-sub-item">
									<a class="hamburger-menu-sub-link"
										href="<?php echo esc_url( $child_menu_item->url ); ?>"
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
						?>
					</ul>
						<?php
					}
					?>
				</div>
			</div>
		</div>
	</div>
</nav>
