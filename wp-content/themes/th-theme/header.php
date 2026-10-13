<!doctype html>
<html <?php language_attributes(); ?>>

<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="format-detection" content="telephone=no">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
	<?php wp_body_open(); ?>
	<div id="page" class="site wrapper">
		<header class="main-header">
			<a class="logo-link fw-bold" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				KNL LED
			</a>
			<div class="header-nav-wrap">
				<?php get_template_part( 'template-parts/header/nav', 'header' ); ?>
			</div>
		</header>
		<?php get_template_part( 'template-parts/header/nav', 'hamburger' ); ?>
		<main id="contents">
