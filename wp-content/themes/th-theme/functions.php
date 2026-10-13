<?php
/**
 * TH THEME functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package TH_THEME
 */

if ( ! defined( 'THEME_VERSION' ) ) {
	// Replace the version number of the theme on each release.
	define( 'THEME_VERSION', '1.0.0' );
}

require get_template_directory() . '/inc/th-helpers.php';

require get_template_directory() . '/inc/class-th-theme.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/th-template-tags.php';

/**
 * BreadCrumb feature.
 */
require get_template_directory() . '/inc/th-breadcrumbs.php';

/**
 * Pagination feature.
 */
require get_template_directory() . '/inc/th-pagination.php';

add_action(
	'wp',
	function () {
		if ( is_product() ) {
			remove_action(
				'woocommerce_before_main_content',
				'woocommerce_breadcrumb',
				20
			);
		}
	}
);


/**
 * Redirect logged-out visitors to the custom login page.
 */
function th_require_login_for_site() {
	if ( is_user_logged_in() || is_admin() || wp_doing_ajax() ) {
		return;
	}

	// Allow WordPress core login, logout, and password-reset actions.
	global $pagenow;

	if ( 'wp-login.php' === $pagenow ) {
		return;
	}

	// Allow the custom login page.
	if ( is_page( 'login' ) ) {
		return;
	}

	$login_url = home_url( '/login/' );

	$request_uri = isset( $_SERVER['REQUEST_URI'] )
		? wp_unslash( $_SERVER['REQUEST_URI'] )
		: '/';

	$requested_url = home_url( $request_uri );

	$redirect_url = add_query_arg(
		'redirect_to',
		$requested_url,
		$login_url
	);

	wp_safe_redirect( $redirect_url );
	exit;
}
add_action( 'template_redirect', 'th_require_login_for_site' );

/**
 * Display the WordPress login form using a shortcode.
 *
 * Shortcode: [private_site_login]
 */
function th_private_site_login_form() {
	if ( is_user_logged_in() ) {
		return '<p>You are already logged in.</p>';
	}

	$redirect_to = isset( $_GET['redirect_to'] )
		? wp_validate_redirect(
			wp_unslash( $_GET['redirect_to'] ),
			home_url( '/' )
		)
		: home_url( '/' );

	return wp_login_form(
		array(
			'echo'           => false,
			'redirect'       => $redirect_to,
			'form_id'        => 'th-private-login-form',
			'label_username' => 'Username or Email',
			'label_password' => 'Password',
			'label_remember' => 'Remember Me',
			'label_log_in'   => 'Log In',
			'remember'       => true,
		)
	);
}
add_shortcode( 'private_site_login', 'th_private_site_login_form' );
