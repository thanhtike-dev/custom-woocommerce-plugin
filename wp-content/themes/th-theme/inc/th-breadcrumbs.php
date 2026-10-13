<?php
function th_breadcrumbs() {
	if ( is_front_page() ) {
		return;
	}

	$current_post_type = get_post_type();

	if ( ! $current_post_type ) {
		return;
	}

	echo '<nav class="breadcrumbs" aria-label="Breadcrumbs Navigation">';
	echo '<ul class="bread-list jp fw-medium">';
	echo '<li><a href="' . esc_url( home_url() ) . '">top</a></li>';

	if ( is_tax() || is_category() || is_single() || is_year() ) {
		if ( $current_post_type === 'post' ) {
			echo '<li><a href="' . esc_url( home_url( '/topics' ) ) . '">topics</a></li>';
		} elseif ( $current_post_type ) {
			echo '<li><a href="' . esc_url( home_url( '/' . $current_post_type ) ) . '">' . esc_html( $current_post_type ) . '</a></li>';
		}

		if ( is_category() || is_tax() ) {
			$term = get_queried_object();
			if ( isset( $term->name ) ) {
				echo '<li>' . esc_html( $term->name ) . '</li>';
			}
		} elseif ( is_year() ) {
			echo '<li>' . esc_html( get_the_date( 'Y' ) ) . '</li>';
		} elseif ( is_single() ) {
			echo '<li>' . esc_html( get_the_title() ) . '</li>';
		}
	} elseif ( is_archive() || is_home() || is_page() ) {
		if ( is_page() ) {
			// If child page, get parents
			$post = get_queried_object();
			$anc  = get_post_ancestors( $post->ID );

			// Get parents in the right order
			$anc = array_reverse( $anc );

			// Parent page loop
			if ( ! isset( $parents ) ) {
				$parents = null;
			}

			foreach ( $anc as $ancestor ) {
				$parents .= '<li><a href="' . get_permalink( $ancestor ) . '">' . get_the_title( $ancestor ) . '</a></li>';
			}

			// Display parent pages
			echo $parents;

			// Current page
			echo '<li>' . get_the_title() . '</li>';
		} elseif ( $current_post_type === 'post' ) {
				echo '<li>topics</li>';
		} elseif ( $current_post_type ) {
			echo '<li>' . esc_html( $current_post_type ) . '</li>';
		}
	}
	echo '</ul>';
	echo '</nav>';
}
