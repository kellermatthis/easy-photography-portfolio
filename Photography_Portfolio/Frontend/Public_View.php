<?php


namespace Photography_Portfolio\Frontend;


use Photography_Portfolio\Settings\Gallery\lightGallery;

class Public_View {


	protected $build_dir;


	/**
	 * Public_View constructor.
	 */
	public function __construct() {

		// Hook into WordPress
		add_action( 'wp_head', [ $this, 'detect_javascript' ] );
		add_filter( 'body_class', [ $this, 'adjust_body_class' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue' ] );

		// Hook into Self
		add_action( 'phort/wrapper/start', [ $this, 'render_wrapper_start' ] );
		add_action( 'phort/wrapper/end', [ $this, 'render_wrapper_end' ] );

		// Adjust .PP_Wrapper classes
		add_filter( 'phort_get_class', [ $this, 'adjust_wrapper_class' ], 10, 2 );


		// Add Photoswipe template
		// @TODO: This probably doesn't really belong here.
		if ( 'photoswipe' === phort_get_option( 'popup_gallery' ) ) {
			add_action( 'get_footer', [ $this, 'display_photoswipe_html' ], 1000 );
		}

		$this->build_dir = CLM_PLUGIN_DIR_URL . 'public/build/';
	}


	public function enqueue() {


		// Register scripts & styles
		$this->register();

		// Enqueue style
		wp_enqueue_style( 'phort-style' );


		// Enqueue dependencies dynamically
		if ( in_array( phort_slug_current(), [ 'masonry', 'masonry-hovercard' ] ) ) {
			wp_enqueue_script( 'jquery-masonry' );
		}

		// Enqueue the active gallery script
		$gallery = phort_get_option( 'popup_gallery' );
		if ( $gallery !== 'disabled' ) {
			wp_enqueue_script( 'phort-gallery-' . $gallery );
			wp_enqueue_style( 'phort-gallery-' . $gallery );
		}


		// Enqueue photography-portfolio.js last
		wp_enqueue_script( 'phort-app' );
	}


	public function register() {

		$dependencies = [
			'jquery',
			'imagesloaded',
			'wp-js-hooks',
		];


		// Styles
		wp_register_style( 'phort-style', $this->build_dir . 'photography-portfolio.css' );
		wp_register_style( 'phort-gallery-lightgallery', $this->build_dir . 'libs/lightgallery.css' );
		wp_register_style( 'phort-gallery-photoswipe-ui', $this->build_dir . 'libs/photoswipe-ui.css' );
		wp_register_style( 'phort-gallery-photoswipe', $this->build_dir . 'libs/photoswipe.css', [ 'phort-gallery-photoswipe-ui' ] );


		// Scripts
		wp_register_script( 'wp-js-hooks', $this->build_dir . 'libs/wp-js-hooks.js', NULL, NULL, true );
		wp_register_script( 'phort-app', $this->build_dir . 'photography-portfolio.js', $dependencies, CLM_VERSION, true );

		// Gallery Scripts
		wp_register_script( 'phort-gallery-lightgallery', $this->build_dir . 'libs/light-gallery-custom.js', [ 'jquery' ], NULL, true );
		wp_register_script( 'phort-gallery-photoswipe-ui', $this->build_dir . 'libs/photoswipe-ui.js', NULL, NULL, true );
		wp_register_script(
			'phort-gallery-photoswipe',
			$this->build_dir . 'libs/photoswipe.js',
			[ 'jquery', 'phort-gallery-photoswipe-ui' ],
			NULL,
			true
		);


		// Pass options to JavaScript side
		wp_localize_script( 'phort-app', '__phort', $this->javascript_settings() );
	}


	public function javascript_settings() {


		$gallery = phort_get_option( 'popup_gallery' );


		$settings = [
			'popup_gallery' => $gallery,
		];

		lightGallery::register();

		return apply_filters( 'phort/js/__phort', $settings );
	}


	public function detect_javascript() {

		echo "<script>document.documentElement.classList.add('js');</script>";
	}


	public function adjust_body_class( $classes ) {

		if ( ! phort_is_portfolio() ) {
			return $classes;
		}

		// If this is portfolio, add core portfolio class
		$classes[] = 'PP_Portfolio';


		// Single Portfolio
		if ( phort_instance()->query->is_single() ) {

			$classes[] = 'PP_Single';
			$classes[] = 'PP_Single--' . phort_slug_single();

			$gallery_type = phort_get_option( 'popup_gallery' );

			if ( 'disabled' !== $gallery_type && ! empty( $gallery_type ) ) {
				$classes[] = 'PP_Popup--' . sanitize_html_class( $gallery_type );
			}

		}

		// Portfolio Archive & Categories
		if ( phort_instance()->query->is_archive() || phort_instance()->query->is_category() ) {
			$classes[] = 'PP_Archive';
			$classes[] = 'PP_Archive--' . phort_slug_archive();
		}


		return $classes;
	}


	public function adjust_wrapper_class( $classes, $class ) {

		/**
		 * Only affect .PP_Wrapper
		 */

		if ( ! in_array( 'PP_Wrapper', $classes ) ) {
			return $classes;
		}

		$custom_classes = phort_get_option( 'wrapper_class' );

		if ( $custom_classes ) {
			$classes = array_merge( $classes, phort_get_class( $custom_classes ) );
		}

		return $classes;

	}


	public function render_wrapper_start() {

		phort_get_template( 'partials/wrapper-start' );


	}


	public function render_wrapper_end() {

		phort_get_template( 'partials/wrapper-end' );
	}


	public function display_photoswipe_html() {

		phort_get_template( 'partials/photoswipe' );
	}

}