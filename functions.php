<?php	// (C) Copright Bobbing Wide 2017-2024
function genesis_bw_after_setup_theme() {
//* Start the engine
	include_once( get_template_directory() . '/lib/init.php' );

//* Setup Theme
	include_once( get_stylesheet_directory() . '/lib/theme-defaults.php' );

//* Set Localization (do not remove)
//load_child_theme_textdomain( 'genesis_bw', apply_filters( 'child_theme_textdomain', get_stylesheet_directory() . '/languages', 'genesis_bw' ) );

//* Add Image upload and Color select to WordPress Theme Customizer
	require_once( get_stylesheet_directory() . '/lib/customize.php' );

//* Include Customizer CSS
	include_once( get_stylesheet_directory() . '/lib/output.php' );

//* Child theme (do not remove)
	define( 'CHILD_THEME_NAME', 'Genesis BW Theme' );
	define( 'CHILD_THEME_URL', 'https://www.bobbingwide.com/oik-themes/genesis-bw/' );

	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		$timestamp=filemtime( get_stylesheet_directory() . "/style.css" );
		define( 'CHILD_THEME_VERSION', $timestamp );
	} else {
		define( 'CHILD_THEME_VERSION', '0.0.0' );
	}

//* Enqueue Google Fonts
	add_action( 'wp_enqueue_scripts', 'genesis_bw_enqueue_scripts_styles' );
	function genesis_bw_enqueue_scripts_styles() {

		// wp_enqueue_style( 'google-fonts', '//fonts.googleapis.com/css?family=Roboto+Condensed:300italic,700italic,700,300', array(), CHILD_THEME_VERSION );
		wp_enqueue_style( 'dashicons' );

		wp_enqueue_script( 'genesis_bw-responsive-menu', get_stylesheet_directory_uri() . '/js/responsive-menu.js', array( 'jquery' ), '1.0.0', true );
		$output=array(
			'mainMenu'=>__( 'Menu', 'genesis_bw' ),
			'subMenu' =>__( 'Menu', 'genesis-bw' ),
		);
		wp_localize_script( 'genesis_bw-responsive-menu', 'Genesis_bwL10n', $output );

	}

//* Add HTML5 markup structure
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption' ) );

//* Add accessibility support
	add_theme_support( 'genesis-accessibility', array( 'drop-down-menu', 'headings', 'search-form', 'skip-links' ) );

//* Add viewport meta tag for mobile browsers
	add_theme_support( 'genesis-responsive-viewport' );

//* Add support for custom header
	add_theme_support( 'custom-header', array(
		'flex-height'    =>true,
		'width'          =>300,
		'height'         =>60,
		'header-selector'=>'.site-title a',
		'header-text'    =>false,
	) );

//* Add support for structural wraps
	add_theme_support( 'genesis-structural-wraps', array(
		'header',
		'footer',
	) );

//* Add support for after entry widget... this only works for post
// We need add_post_type_support( 

	$post_types=array(
		'oik-plugins',
		'oik_pluginversion',
		'oik_premiumversion'
	,
		'oik-themes',
		'oik_themeversion',
		'oik_themiumversion'
	,
		'oik_shortcodes',
		'portfolio'
	);
	foreach ( $post_types as $post_type ) {
		add_post_type_support( $post_type, 'genesis-after-entry-widget-area' );
	}
	$post_types[]="portfolio";
	foreach ( $post_types as $post_type ) {
		add_post_type_support( $post_type, 'publicize' );
	}

	add_theme_support( 'genesis-after-entry-widget-area' );

//* Add new image sizes
	add_image_size( 'featured-content-lg', 1200, 600, true );
	add_image_size( 'featured-content-sm', 600, 400, true );

//* Unregister layout settings
	genesis_unregister_layout( 'content-sidebar-sidebar' );
	genesis_unregister_layout( 'sidebar-content-sidebar' );
	genesis_unregister_layout( 'sidebar-sidebar-content' );

//* Unregister secondary sidebar
	unregister_sidebar( 'sidebar-alt' );

//* Unregister the header right widget area
	unregister_sidebar( 'header-right' );
	add_filter( 'genesis_pre_get_option_site_layout', '__genesis_return_full_width_content' );


//* Rename Primary Menu
	add_theme_support( 'genesis-menus', array(
		'primary'  =>__( 'Header Navigation Menu', 'genesis-bw' ),
		'secondary'=>__( 'Before Header Navigation Menu', 'genesis-bw' )
	) );

//* Remove output of primary navigation right extras
	remove_filter( 'genesis_nav_items', 'genesis_nav_right', 10, 2 );
	remove_filter( 'wp_nav_menu_items', 'genesis_nav_right', 10, 2 );

//* Reposition the navigation
	remove_action( 'genesis_after_header', 'genesis_do_nav' );
	remove_action( 'genesis_after_header', 'genesis_do_subnav' );
	add_action( 'genesis_before_header', 'genesis_do_subnav' );
	add_action( 'genesis_header', 'genesis_do_nav', 5 );


//* Remove skip link for primary navigation and add skip link for footer widgets
	add_filter( 'genesis_skip_links_output', 'genesis_bw_skip_links_output' );
	function genesis_bw_skip_links_output( $links ) {

		if ( isset( $links['genesis-nav-primary'] ) ) {
			unset( $links['genesis-nav-primary'] );
		}

		$new_links=$links;
		array_splice( $new_links, 3 );

		if ( is_active_sidebar( 'flex-footer' ) ) {
			$new_links['footer']=__( 'Skip to footer', 'genesis-bw' );
		}

		return array_merge( $new_links, $links );

	}

//* Reposition the entry meta in the entry header
	remove_action( 'genesis_entry_header', 'genesis_post_info', 12 );
	add_action( 'genesis_entry_header', 'genesis_post_info', 8 );

//* Reposition the entry image
	remove_action( 'genesis_entry_content', 'genesis_do_post_image', 8 );
	add_action( 'genesis_entry_header', 'genesis_do_post_image', 5 );

//* Add featured image above the entry content
//add_action( 'genesis_entry_header', 'genesis_bw_featured_photo', 5 );
	function genesis_bw_featured_photo() {

		if ( is_attachment() || ! genesis_get_option( 'content_archive_thumbnail' ) ) {
			return;
		}

		if ( is_singular() && $image=genesis_get_image( array(
				'format'=>'url',
				'size'  =>genesis_get_option( 'image_size' )
			) ) ) {
			printf( '<div class="featured-image"><img src="%s" alt="%s" class="entry-image"/></div>', $image, the_title_attribute( 'echo=0' ) );
		}

	}

//* Add Excerpt support to Pages
	add_post_type_support( 'page', 'excerpt' );

//* Output Excerpt on Pages
	add_action( 'genesis_meta', 'genesis_bw_page_description_meta' );
	function genesis_bw_page_description_meta() {

		if ( is_front_page() ) {
			remove_action( 'genesis_site_description', 'genesis_seo_site_description' );
			add_action( 'genesis_after_header', 'genesis_bw_open_after_header', 5 );
			add_action( 'genesis_after_header', 'genesis_seo_site_description', 10 );
			add_action( 'genesis_after_header', 'genesis_bw_close_after_header', 15 );
		}

		if ( is_archive() && ! is_post_type_archive() ) {
			remove_action( 'genesis_before_loop', 'genesis_do_taxonomy_title_description', 15 );
			add_action( 'genesis_after_header', 'genesis_bw_open_after_header', 5 );
			add_action( 'genesis_after_header', 'genesis_do_taxonomy_title_description', 10 );
			add_action( 'genesis_after_header', 'genesis_bw_close_after_header', 15 );
		}

		if ( is_post_type_archive() && genesis_has_post_type_archive_support() ) {
			remove_action( 'genesis_before_loop', 'genesis_do_cpt_archive_title_description' );
			add_action( 'genesis_after_header', 'genesis_bw_open_after_header', 5 );
			add_action( 'genesis_after_header', 'genesis_do_cpt_archive_title_description', 10 );
			add_action( 'genesis_after_header', 'genesis_bw_close_after_header', 15 );
		}

		if ( is_author() ) {
			remove_action( 'genesis_before_loop', 'genesis_do_author_title_description', 15 );
			add_action( 'genesis_after_header', 'genesis_bw_open_after_header', 5 );
			add_action( 'genesis_after_header', 'genesis_do_author_title_description', 10 );
			add_action( 'genesis_after_header', 'genesis_bw_close_after_header', 15 );
		}

		if ( is_page_template( 'page_blog.php' ) && has_excerpt() ) {
			remove_action( 'genesis_before_loop', 'genesis_do_blog_template_heading' );
			add_action( 'genesis_after_header', 'genesis_bw_open_after_header', 5 );
			add_action( 'genesis_after_header', 'genesis_bw_add_page_description', 10 );
			add_action( 'genesis_after_header', 'genesis_bw_close_after_header', 15 );
		} elseif ( is_singular() && is_page() && has_excerpt() ) {
			remove_action( 'genesis_entry_header', 'genesis_do_post_title' );
			add_action( 'genesis_after_header', 'genesis_bw_open_after_header', 5 );
			add_action( 'genesis_after_header', 'genesis_bw_add_page_description', 10 );
			add_action( 'genesis_after_header', 'genesis_bw_close_after_header', 15 );
		}

	}

	function genesis_bw_add_page_description() {

		echo '<div class="page-description">';
		echo '<h1 itemprop="headline" class="page-title">' . get_the_title() . '</h1>';
		echo '<p>' . get_the_excerpt() . '</p></div>';

	}

	function genesis_bw_open_after_header() {
		echo '<div class="after-header"><div class="wrap">';
	}

	function genesis_bw_close_after_header() {
		echo '</div></div>';
	}

//* Setup widget counts
	function genesis_bw_count_widgets( $id ) {

		global $sidebars_widgets;

		if ( isset( $sidebars_widgets[ $id ] ) ) {
			return count( $sidebars_widgets[ $id ] );
		}

	}

	function genesis_bw_widget_area_class( $id ) {

		$count=genesis_bw_count_widgets( $id );

		$class='';

		if ( $count == 1 ) {
			$class.=' widget-full';
		} elseif ( $count % 3 == 1 ) {
			$class.=' widget-thirds';
		} elseif ( $count % 4 == 1 ) {
			$class.=' widget-fourths';
		} elseif ( $count % 6 == 0 ) {
			$class.=' widget-uneven';
		} elseif ( $count % 2 == 0 ) {
			$class.=' widget-halves uneven';
		} else {
			$class.=' widget-halves';
		}

		return $class;

	}

//* Add the flexible footer widget area
	add_action( 'genesis_before_footer', 'genesis_bw_footer_widgets' );
	function genesis_bw_footer_widgets() {

		genesis_widget_area( 'flex-footer', array(
			'before'=>'<div id="footer" class="flex-footer footer-widgets"><h2 class="genesis-sidebar-title screen-reader-text">' . __( 'Footer', 'genesis-bw' ) . '</h2><div class="flexible-widgets widget-area wrap' . genesis_bw_widget_area_class( 'flex-footer' ) . '">',
			'after' =>'</div></div>',
		) );

	}

//* Register widget areas
	genesis_register_sidebar( array(
		'id'         =>'front-page-1',
		'name'       =>__( 'Front Page 1', 'genesis-bw' ),
		'description'=>__( 'This is the front page 1 section.', 'genesis-bw' ),
	) );
	genesis_register_sidebar( array(
		'id'         =>'front-page-2',
		'name'       =>__( 'Front Page 2', 'genesis-bw' ),
		'description'=>__( 'This is the front page 2 section.', 'genesis-bw' ),
	) );
	genesis_register_sidebar( array(
		'id'         =>'front-page-3',
		'name'       =>__( 'Front Page 3', 'genesis-bw' ),
		'description'=>__( 'This is the front page 3 section.', 'genesis-bw' ),
	) );
	genesis_register_sidebar( array(
		'id'         =>'front-page-4',
		'name'       =>__( 'Front Page 4', 'genesis-bw' ),
		'description'=>__( 'This is the front page 4 section.', 'genesis-bw' ),
	) );
	genesis_register_sidebar( array(
		'id'         =>'flex-footer',
		'name'       =>__( 'Flexible Footer', 'genesis-bw' ),
		'description'=>__( 'This is the footer section.', 'genesis-bw' ),
	) );

	add_theme_support( 'woocommerce' );

	add_filter( 'genesis_pre_get_option_footer_text', 'genesis_bw_footer_creds_text' );

	/**
	 * Implement 'genesis_footer_creds_text' filter
	 *
	 * @param string $creds_text
	 *
	 * @return string what we want
	 */

	function genesis_bw_footer_creds_text( $text ) {
		do_action( "oik_add_shortcodes" );
		$text="[bw_wpadmin]";
		$text.='<br />';
		$text.="[bw_copyright]";
		$text.='<hr />';
		$text.='Website designed and developed by [bw_link text="Herb Miller" herbmiller.me]';
		$text.='<br />';
		$text.='[bw_power]';
		$text.=' and <a href="//oik-plugins.com" title="oik plugins">oik plugins</a>';
		$text.='<br />';
		$text.=' [footer_childtheme_link before="Running: " after=" with "]';
		$text.=' [footer_genesis_link url="http://www.studiopress.com/" before=""]';
		$text.=' <a href="http://www.bobbingwide.com" title="bobbing wide - web design web development" class="bwlogo">[bw cp=h]</a>';

		return ( $text );
	}

	/**
	 * Disable the WPSEO v3.1+ Primary Category feature.
	 */
	add_filter( 'wpseo_primary_term_taxonomies', '__return_empty_array' );


//add_filter( 'woocommerce_empty_price_html', "genesis_bw_woocommerce_empty_price_html", 10, 2 );

	function genesis_bw_woocommerce_empty_price_html( $price, $wc_product ) {
		return ( "Call for price" );
	}

	add_filter( 'woocommerce_get_price_html', "genesis_bw_woocommerce_get_price_html", 10, 2 );
	/**
	 * Gets the price
	 *
	 * Applies a prefix or suffix depending on the product.
	 *
	 * - Websites are sold individually; prefix with "From:".
	 * - Services are per hour; append " per hour".
	 *
	 * @param string $price the HTML price
	 * @param object $wc_product
	 */
	function genesis_bw_woocommerce_get_price_html( $price, $wc_product ) {
		if ( $wc_product->is_sold_individually() ) {
			$price="From: $price";
		} else {
			$price.=" per hour";
		}

		return $price;
	}

}

add_action( 'after_setup_theme', 'genesis_bw_after_setup_theme', 11);
