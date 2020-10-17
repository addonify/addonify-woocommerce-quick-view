<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://addonify.com
 * @since      1.0.0
 *
 * @package    Addonify_Quick_View
 * @subpackage Addonify_Quick_View/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Addonify_Quick_View
 * @subpackage Addonify_Quick_View/public
 * @author     Addonify <info@addonify.com>
 */
class Addonify_Quick_View_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;


	/**
	 * Contains all the db fields used by this plugin
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $all_db_fields;

	/**
	 * Enable Compare Product Plugin
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $show_quick_view_btn;

	/**
	 * Compare Button Position
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $quick_view_btn_position;

	 /**
	 * Compare Button Label
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $quick_view_btn_label;


	// constructor function	
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		$this->enable_plugin = (int) $this->get_db_values('enable_quick_view', 1);

		if( ! is_admin() ){

			// if plugin is enabled
			if( $this->enable_plugin === 1 ){

				$this->quick_view_btn_position =  $this->get_db_values('quick_view_btn_position', 'after_add_to_cart' );
				$this->quick_view_btn_label = $this->get_db_values( 'quick_view_btn_label', __('Quick View', 'addonify-quick-views') );
			}
		}

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		// should we load styles ?
		if( ! $this->enable_plugin ) return;


		$style_dependency = array();

		if ( version_compare( WC()->version, '3.0.0', '>=' ) ) {
			
			// these features are supported from woocommerce 3.0.0

			if ( current_theme_supports( 'wc-product-gallery-lightbox' ) ) {

				if( (int) $this->get_db_values( 'enable_lightbox', 1 ) ) {
					$style_dependency[] = 'photoswipe-default-skin';
				}
			}

		}

		if( is_rtl() ){
			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'assets/build/css/addonify-quick-view-public-rtl.css', $style_dependency, $this->version, 'all' );
		}
		else{

			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'assets/build/css/addonify-quick-view-public.css', $style_dependency, $this->version, 'all' );
		}
	}

	
	// enqueue scripts
	public function enqueue_scripts() {

		// should we load scripts ?
		if( ! $this->enable_plugin ) return;


		$script_dependency = array('jquery', 'wc-add-to-cart-variation', 'flexslider');
		
		if ( version_compare( WC()->version, '3.0.0', '>=' ) ) {
			
			// these features are supported from woocommerce 3.0.0

			if ( current_theme_supports( 'wc-product-gallery-zoom' ) ) {
				$script_dependency[] = 'zoom';
			}

			if ( current_theme_supports( 'wc-product-gallery-lightbox' ) ) {

				if( (int) $this->get_db_values( 'enable_lightbox', 1 ) ) {
					$script_dependency[] = 'photoswipe-ui-default';
					
					// this action is required for photoswipe to work
					add_action( 'wp_footer', 'woocommerce_photoswipe', 15 );
				}
			}

			$script_dependency[] = 'wc-single-product';
		}

		wp_enqueue_code_editor( array( 'type' => 'text/html' ) );


		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'assets/build/js/addonify-quick-view-public.min.js', array( 'jquery' ), $this->version, false );

		// for ajax
		wp_enqueue_script( 'addonify_qv_ajax_scripts', plugin_dir_url( __FILE__ ) . 'assets/build/js/addonify-qv-ajax-scripts.js', $script_dependency, '', true );

		// localize ajax script
		wp_localize_script( 
			'addonify_qv_ajax_scripts', 
			'ajax_object', 
			array( 
				'ajax_url' 	=> admin_url( 'admin-ajax.php' ), 
				'action' 	=> 'get_quick_view_contents'
			) 
		);

	}

	// callback function
	// ajax request
	// get contents for quick view
	public function quick_view_contents_callback(){

		if( ! $this->enable_plugin ) return;


		// deny any non ajax requests
		if( ! wp_doing_ajax() ) wp_die('Invalid Requests');

		// product id is required
		if( ! isset( $_GET['id'] ) || ! is_numeric( $_GET['id'] ) ) wp_die( 'product id is missing' );

		$product_id = intval( $_GET['id'] );
		
		// generate contents dynamically
		$this->generate_contents();

		// Set the main wp query for the product.
		wp( 'p=' . $product_id . '&post_type=product' );

		ob_start();
		while ( have_posts() ) {
			the_post();
			$this->get_templates( 'addonify-quick-view-content' );
		}

		echo ob_get_clean();

		wp_die();

	}
	

	// callback function
	// show compare btn after add to cart button
	public function show_quick_view_btn_after_add_to_cart_btn_callback(){
		if( $this->quick_view_btn_position == 'after_add_to_cart'  ) {
			$this->show_quick_view_btn_aside_add_to_cart_btn_callback();
		}
	}

	
	// callback function
	// show compare btn before add to cart button
	public function show_quick_view_btn_before_add_to_cart_btn_callback(){

		if( $this->quick_view_btn_position == 'before_add_to_cart'  ) {
			$this->show_quick_view_btn_aside_add_to_cart_btn_callback();
		}
	}

	
	// show template for comapre button
	private function show_quick_view_btn_aside_add_to_cart_btn_callback() {

		// do not continue if "Enable Product Comparision" is not checked
		if( ! $this->enable_plugin ) return;

		global $product;
		$product_id = $product->get_id();

		// show compare btn after add to cart button
		if( $this->quick_view_btn_label ) {

			ob_start();
			$this->get_templates( 'addonify-quick-view-button', false, array( 'product_id' => $product_id, 'label' => $this->quick_view_btn_label, 'css_class' => '') );
			echo ob_get_clean();

		}

	}

	
	// callback function
	// show quick view button aside image
	public function show_quick_view_btn_aside_image_callback(){

		// do not continue if "Enable Product Comparision" is not checked
		if( ! $this->enable_plugin ) return;

		global $product;
		$product_id = $product->get_id();

		$show_quick_btn = (int) $this->get_db_values( 'enable_quick_view', 1 );
		$quick_view_btn_position = $this->get_db_values( 'quick_view_btn_position', 'after_add_to_cart' );
		$quick_view_btn_label = $this->get_db_values( 'quick_view_btn_label' );

		if( $show_quick_btn && $quick_view_btn_position == 'overlay_on_image' ) {
			ob_start();
			$this->get_templates( 'addonify-quick-view-button', false, array( 'product_id' => $product_id, 'label' => $quick_view_btn_label, 'css_class' => 'addonify-overlay-btn') );
			echo ob_get_clean();
		}

	}

	
	// callback function
	// add custom markup into footer
	public function add_markup_into_footer_callback(){

		// do not continue if "Enable Product Comparision" is not checked
		if( ! $this->enable_plugin ) return;

		ob_start();
		$this->get_templates( 'addonify-quick-view-content-wrapper' );
		echo ob_get_clean();
	}
	
	
	// callback function
	// generate style tag according to options selected by user
	public function generate_custom_styles_callback(){

		// do not continue if "Enable Product Comparision" is not checked
		if( ! $this->enable_plugin ) return;

		// do not continue if plugin styles are disabled by user
		if( ! $this->get_db_values( 'load_styles_from_plugin' ) ) return;

		// generate style markups

		$custom_css = $this->get_db_values('custom_css');
		$custom_styles_output = '';

		$style_args = array(
			'.adfy-quick-view-overlay' => array(
				'background' 	=> 'modal_overlay_bck_color'
			),
			'.adfy-quick-view-modal-content' => array(
				'background' 	=> 'modal_bck_color',
			),
			'.adfy-quick-view-modal-content .entry-title' => array(
				'color'		 	=> 'title_color',
			),
			'.star-rating::before' => array(
				'color'		 	=> 'rating_color_empty',
			),
			'.star-rating span::before' => array(
				'color'		 	=> 'rating_color_full',
			),
			'.adfy-quick-view-modal-content .price' => array(
				'color' 		=> 'price_color_regular',
			),
			'.adfy-quick-view-modal-content .price.del' => array(
				'background' 	=> 'price_color_sale',
			),
			'.adfy-quick-view-modal-content .woocommerce-product-details__short-description' => array(
				'color' 		=> 'excerpt_color',
			),
			'.adfy-quick-view-modal-content .product_meta' => array(
				'color' 		=> 'meta_color',
			),
			'.adfy-quick-view-modal-content .product_meta a:hover' => array(
				'color' 		=> 'meta_color_hover',
			),
			'#addonify-qvm-close-button svg' => array(
				'color' 		=> 'close_btn_text_color',
			),
			'#addonify-qvm-close-button' => array(
				'background'	=> 'close_btn_bck_color',
			),
			'#addonify-qvm-close-button:hover svg' => array(
				'color'		 	=> 'close_btn_text_color_hover',
			),
			'#addonify-qvm-close-button:hover' => array(
				'background' 	=> 'close_btn_bck_color_hover',
			),
			'.adfy-quick-view-modal-content button, .adfy-quick-view-modal-content .button' => array(
				'background' 	=> 'other_btn_bck_color',
				'color' 		=> 'other_btn_text_color',
			),
			'.adfy-quick-view-modal-content button:hover, .adfy-quick-view-modal-content .button:hover' => array(
				'background' 	=> 'other_btn_bck_color_hover',
				'color'		 	=> 'other_btn_text_color_hover',
			),
		);

		foreach($style_args as $css_sel => $property_value){

			$properties = '';

			foreach( $property_value as $property => $db_field){
				$db_value = $this->get_db_values( $db_field );

				if( $db_value ){
					$properties .=  $property . ': ' . $db_value .'; ';
				}
			}
			
			if( $properties ){
				$custom_styles_output .= $css_sel . '{' . $properties .'}';
			}

		}

		// avoid empty style tags
		if( $custom_styles_output || $custom_css ){
			echo "<style id=\"addonify-quick-view-styles\"  media=\"screen\"> \n" . $custom_styles_output .  $custom_css . "\n </style>\n";
		}

	}


	// helper function
	// get db values for selected fields
	private function get_db_values($field_name, $default = NULL ){
		return get_option( ADDONIFY_DB_INITIALS . $field_name, $default );
	}



	// generate contents dynamically to modal templates with hooks
	// called by get_quick_view_contents()
	private function generate_contents() {

		// do not continue if "Enable Product Comparision" is not checked
		if( ! $this->enable_plugin ) return;
		
		// Show Hide Image according to user choices 
		if( (int) $this->get_db_values( 'show_product_image', 1 ) ) {

			// show or hide gallery thumbnails according to user choice
			if( $this->get_db_values( 'product_image_display_type' ) == 'image_only' ) {
				$this->disable_gallery_thumbnails();
			}
			
			// show images
			add_action( 'addonify_qv_product_image', 'woocommerce_show_product_sale_flash', 10 );
			add_action( 'addonify_qv_product_image', 'woocommerce_show_product_images', 20 );
			
		}

		// show or hide title
		if( (int) $this->get_db_values( 'show_product_title', 1 ) ) {
			add_action( 'addonify_qv_product_summary', 'woocommerce_template_single_title', 5 );
		}

		// show or hide product ratings
		if( (int) $this->get_db_values( 'show_product_rating', 1 ) ) {
			add_action( 'addonify_qv_product_summary', 'woocommerce_template_single_rating', 10 );
		}

		// show or hide price
		if( (int) $this->get_db_values( 'show_product_price', 1 ) ) {
			add_action( 'addonify_qv_product_summary', 'woocommerce_template_single_price', 15 );
		}

		// show or hide excerpt
		if( (int) $this->get_db_values( 'show_product_excerpt', 1 ) ) {
			add_action( 'addonify_qv_product_summary', 'woocommerce_template_single_excerpt', 20 );
		}

		// show or hide add to cart button
		if( (int) $this->get_db_values( 'show_add_to_cart_btn', 1 ) ) {
			add_action( 'addonify_qv_product_summary', 'woocommerce_template_single_add_to_cart', 25 );
		}

		// show or hide product meta
		if( (int) $this->get_db_values( 'show_product_meta', 1 ) ) {
			add_action( 'addonify_qv_product_summary', 'woocommerce_template_single_meta', 30 );
		}

		// show  or hide view details button
		if( (int) $this->get_db_values( 'show_view_detail_btn', 1 ) && $this->get_db_values( 'view_detail_btn_label' ) ) {
			add_action( 'addonify_qv_after_product_summary_content', array($this, 'view_details_btn_callback') );
		}
	}

	// callback function
	// view details button markups
	public function view_details_btn_callback( $post_id ){

		// do not continue if "Enable Product Comparision" is not checked
		if( ! $this->enable_plugin ) return;

		$btn_label = $this->get_db_values( 'view_detail_btn_label', __( 'View Detail', 'addonify-quick-views' ));

		if( ! $btn_label ) return;
		
		ob_start();
		$this->get_templates( 'addonify-view-details-button', true, array( 'post_id' => $post_id, 'btn_label' => $btn_label ) );
		echo ob_get_clean();
	}


	// this will hide thumbnails in woocommerce gallery
	private function disable_gallery_thumbnails(){
		// hide thumbnails from gallery in modal
		add_action( 'woocommerce_product_thumbnails', 'remove_hooks' );
		function remove_hooks(){
			remove_action( 'woocommerce_product_thumbnails', 'woocommerce_show_product_thumbnails', 20 );
		}

	}


	// callback function
	// print opening tag of overlay image container
	public function addonify_overlay_container_start_callback(){

		// do not continue if "Enable Product Comparision" is not checked
		if( ! $this->enable_plugin  ) return;

		if( defined('ADDONIFY_OVERLAY_IS_ADDED') && ADDONIFY_OVERLAY_ADDED_BY != 'quick_view' ) return;

		if( $this->quick_view_btn_position == 'overlay_on_image' ){

			if( ! defined('ADDONIFY_OVERLAY_IS_ADDED')) {
				define('ADDONIFY_OVERLAY_IS_ADDED', 1);
			}

			if( ! defined('ADDONIFY_OVERLAY_ADDED_BY')) {
				define('ADDONIFY_OVERLAY_ADDED_BY', 'quick_view' );
			}


			echo '<div class="addonify-overlay-buttons">';
		}

	}


	// callback function
	// print closing tag of overlay image container
	public function addonify_overlay_container_end_callback(){

		// do not continue if "Enable Product Comparision" is not checked
		if( ! $this->enable_plugin  ) return;

		if( defined('ADDONIFY_OVERLAY_IS_ADDED') && ADDONIFY_OVERLAY_ADDED_BY != 'quick_view' ) return;
		
		if( $this->quick_view_btn_position == 'overlay_on_image' ){
			echo '</div>';
		}

	}


	// require proper templates for use in front end
	private function get_templates( $template_name, $require_once = true, $args=array() ){

		// first look for template in themes/addonify/templates
		$theme_path = get_stylesheet_directory() . '/addonify/' . $template_name .'.php';
		$plugin_path = dirname( __FILE__ ) .'/templates/' . $template_name .'.php';

		if( file_exists( $theme_path ) ){
			$template_path = $theme_path;
		}
		else{
			$template_path = $plugin_path;
		}

		if( $require_once ){
			require_once $template_path;
		}
		else{
			require $template_path;
		}
	}

}
