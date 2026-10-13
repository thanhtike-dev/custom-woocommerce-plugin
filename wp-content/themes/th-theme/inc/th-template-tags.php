<?php
/**
 * Custom template tags for this theme
 *
 * Eventually, some of the functionality here could be replaced by core features.
 *
 * @package TH_THEME
 */

if ( ! function_exists( 'th_humanized_posted_on' ) ) :
	/**
	 * Prints HTML with meta information for the current human-readable post-date/time.
	 */
	function th_humanized_posted_on() {
		$time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';
		$datetime    = get_the_date( DATE_W3C );
		$time_string = sprintf(
			$time_string,
			esc_attr( $datetime ),
			esc_html( $datetime ),
		);

		$posted_on = '<a href="' . esc_url( get_permalink() ) . '" class="link-opacity-50-hover text-decoration-none">' . $time_string . '</a>';

		echo '<span class="posted-on">' . $posted_on . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
endif;

if ( ! function_exists( 'th_posted_on' ) ) :
	/**
	 * Prints HTML with meta information for the current post-date/time.
	 */
	function th_posted_on( $posted_on = 'Posted on' ) {
		$time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';
		if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
			$time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time><time class="updated" datetime="%3$s">%4$s</time>';
		}

		$time_string = sprintf(
			$time_string,
			esc_attr( get_the_date( DATE_W3C ) ),
			esc_html( get_the_date() ),
			esc_attr( get_the_modified_date( DATE_W3C ) ),
			esc_html( get_the_modified_date() )
		);

		$posted_on = sprintf(
			$posted_on . ' %s',
			'<a href="' . esc_url( get_permalink() ) . '" class="link-opacity-50-hover text-decoration-none">' . $time_string . '</a>'
		);

		echo '<span class="posted-on">' . $posted_on . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
endif;

if ( ! function_exists( 'th_posted_by' ) ) :
	/**
	 * Prints HTML with meta information for the current author.
	 */
	function th_posted_by() {
		$byline = sprintf(
			/* translators: %s: post author. */
			esc_html_x( 'by %s', 'post author', 'th-theme' ),
			'<span class="author vcard"><a class="url fn n" href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '">' . esc_html( get_the_author() ) . '</a></span>'
		);

		echo '<span class="byline"> ' . $byline . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
endif;

if ( ! function_exists( 'th_entry_footer' ) ) :
	/**
	 * Prints HTML with meta information for the categories, tags and comments.
	 */
	function th_entry_footer() {
		// Hide category and tag text for pages.
		if ( 'post' === get_post_type() ) {
			/* translators: used between list items, there is a space after the comma */
			$categories_list = get_the_category_list( esc_html__( ', ', 'th-theme' ) );
			if ( $categories_list ) {
				/* translators: 1: list of categories. */
				printf( '<span class="cat-links">' . esc_html__( 'Posted in %1$s', 'th-theme' ) . '</span>', $categories_list ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}

			/* translators: used between list items, there is a space after the comma */
			$tags_list = get_the_tag_list( '', esc_html_x( ', ', 'list item separator', 'th-theme' ) );
			if ( $tags_list ) {
				/* translators: 1: list of tags. */
				printf( '<span class="tags-links">' . esc_html__( 'Tagged %1$s', 'th-theme' ) . '</span>', $tags_list ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
		}

		if ( ! is_single() && ! post_password_required() && ( comments_open() || get_comments_number() ) ) {
			echo '<span class="comments-link">';
			comments_popup_link(
				sprintf(
					wp_kses(
						/* translators: %s: post title */
						__( 'Leave a Comment<span class="screen-reader-text"> on %s</span>', 'th-theme' ),
						array(
							'span' => array(
								'class' => array(),
							),
						)
					),
					wp_kses_post( get_the_title() )
				)
			);
			echo '</span>';
		}

		edit_post_link(
			sprintf(
				wp_kses(
					/* translators: %s: Name of current post. Only visible to screen readers */
					__( 'Edit <span class="screen-reader-text">%s</span>', 'th-theme' ),
					array(
						'span' => array(
							'class' => array(),
						),
					)
				),
				wp_kses_post( get_the_title() )
			),
			'<span class="edit-link">',
			'</span>'
		);
	}
endif;

if ( ! function_exists( 'th_post_thumbnail' ) ) :
	/**
	 * Displays an optional post thumbnail.
	 *
	 * Wraps the post thumbnail in an anchor element on index views, or a div
	 * element when on single views.
	 */
	function th_post_thumbnail() {
		if ( post_password_required() || is_attachment() || ! has_post_thumbnail() ) {
			return;
		}

		if ( is_singular() ) :
			?>

<div class="post-thumbnail">
			<?php the_post_thumbnail(); ?>
</div><!-- .post-thumbnail -->

<?php else : ?>

<a class="post-thumbnail" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
	<?php
		the_post_thumbnail(
			'post-thumbnail',
			array(
				'alt' => the_title_attribute(
					array(
						'echo' => false,
					)
				),
			)
		);
	?>
</a>

	<?php
		endif; // End is_singular().
	}
endif;

if ( ! function_exists( 'wp_body_open' ) ) :
	/**
	 * Shim for sites older than 5.2.
	 *
	 * @link https://core.trac.wordpress.org/ticket/12563
	 */
	function wp_body_open() {
		do_action( 'wp_body_open' );
	}
endif;

if ( ! function_exists( 'th_post_nav' ) ) :
	function th_post_nav() {
		$args = array(
			'next_text' => '<span class="screen-reader-text">' . 'Next post:' . ' </span>%title',
			'prev_text' => '<span class="screen-reader-text">' . 'Previous post:' . ' </span>%title',
		);
		the_post_navigation( $args );
	}
endif;

/**
 * Gets the thumbnail with Lazy Load.
 * Should be called in the WordPress Loop.
 *
 * @param int|null $post_id               Post ID.
 * @param string   $size                  The registered image size.
 * @param array    $additional_attributes Additional attributes.
 *
 * @return string
 */
function th_get_the_post_custom_thumbnail( $post_id, $size = 'blog-thumbnail', $additional_attributes = array() ) {
	$custom_thumbnail = '';

	if ( null === $post_id ) {
		$post_id = get_the_ID();
	}

	if ( has_post_thumbnail( $post_id ) ) {
		$default_attributes = array(
			'loading' => 'lazy',
		);

		$attributes = array_merge( $additional_attributes, $default_attributes );

		$custom_thumbnail = wp_get_attachment_image(
			get_post_thumbnail_id( $post_id ),
			$size,
			false,
			$attributes
		);
	}

	return $custom_thumbnail;
}

/**
 * Renders Custom Thumbnail with Lazy Load.
 *
 * @param int    $post_id               Post ID.
 * @param string $size                  The registered image size.
 * @param array  $additional_attributes Additional attributes.
 */
function th_the_post_custom_thumbnail( $post_id, $size = 'blog-thumbnail', $additional_attributes = array() ) {
	echo th_get_the_post_custom_thumbnail( $post_id, $size, $additional_attributes );
}
