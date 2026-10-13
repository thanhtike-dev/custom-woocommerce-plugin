<?php

/**
 * Get theme directory path with optional subpath
 *
 * @param string $path Optional subpath
 * @return string Full directory path
 */
function th_template_dir( string $path = '' ): string {
	return get_template_directory() . ( $path ? '/' . ltrim( $path, '/' ) : '' );
}

/**
 * Get theme directory URI with optional subpath
 *
 * @param string $path Optional subpath
 * @return string Full directory URI
 */
function th_template_dir_uri( string $path = '' ): string {
	return get_template_directory_uri() . ( $path ? '/' . ltrim( $path, '/' ) : '' );
}

/**
 * Get full path to image in assets directory
 *
 * @param string $image_path Image path relative to assets/img
 * @return string Full image URL
 */
function th_img_path( string $image_path ): string {
	if ( empty( $image_path ) ) {
		return '';
	}
	return esc_url( th_template_dir_uri( 'assets/img/' . ltrim( $image_path, '/' ) ) );
}

/**
 * Check if current page is a direct subpage of specified parent
 *
 * @param string $parent_slug Parent page slug
 * @return bool True if current page is a direct child
 */
function th_is_subpage( string $parent_slug ): bool {
	if ( ! is_page() ) {
		return false;
	}

	global $post;
	$parent = $post->post_parent
		? get_post( $post->post_parent )
		: ( $post->post_name === $parent_slug ? null : false );

	return $parent && ( $parent->post_name === $parent_slug );
}

/**
 * Check if current page is a taxonomy archive for specified post type
 *
 * @param string $post_type Post type to check
 * @return bool True if current page is a taxonomy archive
 */
function th_is_tax_of( string $post_type ): bool {
	return is_tax( get_object_taxonomies( $post_type ) );
}

/**
 * Get the trimmed version of post title with truncate end.
 *
 * @param int $trim_character_count Number of characters to trim the title to.
 * @return void
 */
function th_the_title( $trim_character_count = 0 ) {
	$post_ID = get_the_ID();

	if ( empty( $post_ID ) ) {
		return;
	}

	$title = get_the_title( $post_ID );

	// Display the full title if no trimming is needed
	if ( ! empty( $title ) && $trim_character_count === 0 ) {
		the_title();
		return;
	}

	// Trim the title if necessary
	$trimmed_title = wp_html_excerpt( $title, $trim_character_count, '...' );
	echo esc_html( $trimmed_title );
}

/**
 * Get the menu id by menu location.
 *
 * @param string $location
 *
 * @return integer
 */
function th_get_menu_id( $location ) {
	// Get all locations
	$locations = get_nav_menu_locations();

	// Get object id by location
	$menu_id = ! empty( $locations[ $location ] ) ? $locations[ $location ] : '';

	return ! empty( $menu_id ) ? $menu_id : '';
}

/**
 * Get all child menus that has given parent menu id.
 *
 * @param array $menu_items
 * @param int   $parent_id
 * @return array
 */
function th_get_child_menu_items( array $menu_items, int $parent_id ): array {
	if ( empty( $menu_items ) ) {
		return array();
	}

	return array_values(
		array_filter(
			$menu_items,
			fn( $item ) => (int) $item->menu_item_parent === $parent_id
		)
	);
}
