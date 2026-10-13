<?php
/**
 * Main Class for TH WP THEME
 *
 * @package TH_WP
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'TH_Theme' ) ) :

	/**
	 * Main Theme Class for TH WP Theme
	 *
	 * @package TH_WP
	 */
	class TH_Theme {
		public function __construct() {
			// Theme Setup.
			add_action( 'after_setup_theme', array( $this, 'th_theme_supports' ), 5 );
			add_action( 'after_setup_theme', array( $this, 'th_setup' ), 10 );

			// Assets.
			add_action( 'wp_enqueue_scripts', array( $this, 'th_enqueue_styles' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'th_enqueue_scripts' ) );

			// Widgets.
			add_action( 'widgets_init', array( $this, 'th_init_widgets' ) );

			// Customizer.
			// add_action( 'template_redirect', array( $this, 'th_disable_author_archive_404' ) );

			// Filters.
			add_filter( 'body_class', array( $this, 'th_body_classes' ) );
			add_filter( 'document_title_separator', array( $this, 'th_custom_title_separator' ) );
			add_filter( 'acf/settings/remove_wp_meta_box', '__return_false' );

			// Remove unwanted filters.
			// remove_filter( 'pre_term_description', 'wp_filter_kses' );
			// remove_filter( 'term_description', 'wp_kses_data' );
		}

		/**
		 * Theme setup.
		 * This function sets up theme defaults and registers support for various WordPress features.
		 * It also registers navigation menus.
		 */
		public function th_setup() {
			$GLOBALS['content_width'] = apply_filters( 'th_wp_content_width', 640 );

			register_nav_menus(
				array(
					'th-header-menu'    => esc_html__( 'Header Menu', 'th-theme' ),
					'th-hamburger-menu' => esc_html__( 'Hamburger Menu', 'th-theme' ),
					'th-footer-menu'    => esc_html__( 'Footer Menu', 'th-theme' ),
				)
			);
		}

		/**
		 * Enqueue styles.
		 * This function registers and enqueues various stylesheets for the theme.
		 */
		public function th_enqueue_styles() {
			// Register google fonts.
			wp_enqueue_style( 'google-fonts', 'https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap', array(), null );

			// Register styles.
			wp_register_style( 'select2-lib', th_template_dir_uri( 'assets/css/lib/select2.min.css' ), array(), THEME_VERSION, 'all' );

			wp_register_style( 'th-utilities', th_template_dir_uri( 'assets/css/utilities.css' ), array(), THEME_VERSION, 'all' );

			wp_register_style( 'th-common', th_template_dir_uri( 'assets/css/common.css' ), array( 'google-fonts', 'th-utilities' ), filemtime( th_template_dir( 'assets/css/common.css' ) ), 'all' );

			wp_register_style( 'th-single', th_template_dir_uri( 'assets/css/single.css' ), array(), filemtime( th_template_dir( 'assets/css/single.css' ) ), 'all' );

			wp_register_style( 'th-single-product', th_template_dir_uri( 'assets/css/single-product.css' ), array(), filemtime( th_template_dir( 'assets/css/single-product.css' ) ), 'all' );

			// Enqueue Styles.
			wp_enqueue_style( 'th-common' );

			if ( is_product() ) {
				wp_enqueue_style( 'th-single-product' );
			}
		}

		/**
		 * Enqueue scripts.
		 * This function registers and enqueues various JavaScript libraries and custom scripts for the theme.
		 */
		public function th_enqueue_scripts() {
			wp_register_script( 'select2-lib', th_template_dir_uri( 'assets/js/lib/select2.min.js' ), array(), THEME_VERSION, true );

			// Register JS custom scripts.
			wp_register_script( 'th-common', th_template_dir_uri( 'assets/js/common.js' ), array( 'jquery', 'gsap-scroll-trigger-plugin', 'gsap-scroll-to-plugin', 'lenis-lib' ), filemtime( th_template_dir( 'assets/js/common.js' ) ), true );

			wp_register_script( 'th-index', th_template_dir_uri( 'assets/js/index.js' ), array( 'jquery' ), filemtime( th_template_dir( 'assets/js/index.js' ) ), true );

			// Enqueue JS custom scripts.
			wp_enqueue_script( 'th-common' );

			if ( is_front_page() ) {
				wp_enqueue_script( 'th-index' );
			}
		}

		/**
		 * This function adds various classes to the body element based on the current page context.
		 *
		 * @param array $classes
		 * @return array
		 */
		public function th_body_classes( array $classes ) {
			// Adds a class of hfeed to non-singular pages.
			if ( ! is_singular() ) {
				$classes[] = 'hfeed';
			}

			// Adds a class of no-sidebar when there is no sidebar present.
			if ( ! is_active_sidebar( 'sidebar-1' ) ) {
				$classes[] = 'no-sidebar';
			}

			return $classes;
		}

		/**
		 * Theme supports
		 * This function adds support for various theme features such as automatic feed links, title tag,
		 */
		public function th_theme_supports() {
			add_theme_support( 'automatic-feed-links' );

			add_theme_support( 'title-tag' );

			add_theme_support( 'post-thumbnails' );

			add_theme_support( 'woocommerce' );

			add_theme_support(
				'html5',
				array(
					'search-form',
					'comment-form',
					'comment-list',
					'gallery',
					'caption',
					'style',
					'script',
				)
			);

			// Add theme support for selective refresh for widgets.
			add_theme_support( 'customize-selective-refresh-widgets' );

			/**
			 * Add custom image sizes.
			 * This function adds a custom image size for post thumbnails.
			 */
			add_image_size( 'blog-thumbnail', 320, 180, false );
			add_image_size( 'single-thumbnail', 500, 500, false );
		}

		/**
		 * Register widget area.
		 *
		 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
		 */
		public function th_init_widgets() {
			register_sidebar(
				array(
					'name'          => esc_html__( 'Sidebar', 'th-theme' ),
					'id'            => 'sidebar-1',
					'description'   => esc_html__( 'Add widgets here.', 'th-theme' ),
					'before_widget' => '<section id="%1$s" class="widget %2$s">',
					'after_widget'  => '</section>',
					'before_title'  => '<h2 class="widget-title">',
					'after_title'   => '</h2>',
				)
			);
		}

		/**
		 * Add custom seperator in title tag
		 *
		 * @param mixed $sep
		 * @return string
		 */
		public function th_custom_title_separator( $sep ) {
			return '｜';
		}

		/**
		 * Disable author archive pages by returning a 404 error.
		 *
		 * @return void
		 */
		public function th_disable_author_archive_404() {
			if ( is_author() ) {
				global $wp_query;
				$wp_query->set_404();
				status_header( 404 );
				nocache_headers();
			}
		}
	}
endif;

return new TH_Theme();
