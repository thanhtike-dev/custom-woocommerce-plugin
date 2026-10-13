<?php
/**
 * The template for displaying search results pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#search-result
 *
 * @package TH_THEME
 */

get_header();
?>
<main id="search-list">
	<?php if ( have_posts() ) : ?>
	<header class="page-header">
		<h1 class="page-title">
			<?php printf( 'Search Results for: %s', '<span>' . get_search_query() . '</span>' ); ?>
		</h1>
	</header><!-- .page-header -->
		<?php
		echo '<div class="row row-cols-1 row-cols-md-3 g-4">';
		while ( have_posts() ) :

			the_post();
			get_content_template( 'search' );

	endwhile;
		echo '</div>';
		the_posts_navigation();

	else :
		get_content_template( 'none' );
	endif;
	?>

</main><!-- #main -->

<?php
get_footer();
