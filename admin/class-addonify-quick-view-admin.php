<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://addonify.com
 * @since      1.0.0
 *
 * @package    Addonify_Quick_View
 * @subpackage Addonify_Quick_View/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Addonify_Quick_View
 * @subpackage Addonify_Quick_View/admin
 * @author     Addonify <info@addonify.com>
 */
class Addonify_Quick_View_Admin {

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
	 * Default settings_page_slug
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $settings_page_slug = 'addonify_quick_view';


	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */

	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;

		// store empty variables
		$this->all_db_fields = array();
	}

	
	// enqueue styles
	public function enqueue_styles() {

		if( isset($_GET['page']) && $_GET['page'] == 'addonify_quick_view' ){

			// toggle switch
			wp_enqueue_style( 'lc_switch', plugin_dir_url( __FILE__ ) . 'css/lc_switch.css' );

			
			if( version_compare( get_bloginfo('version'),'3.5', '>=' ) ){
				// feature available from wordpress 3.5

				// built in wp color picker
				wp_enqueue_style( 'wp-color-picker' );
			}

			// admin css
			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/addonify-quick-view-admin.css', array(), $this->version, 'all' );

		}

	}


	// enqueue scripts
	public function enqueue_scripts() {

		if( isset($_GET['page']) && $_GET['page'] == 'addonify_quick_view' ){

			$code_editor_is_available = 0;
			$color_picker_is_available = 0;


			if( version_compare( get_bloginfo('version'),'3.5', '>=' ) ){
				$color_picker_is_available = 1;
			}


			if( version_compare( get_bloginfo('version'),'4.9', '>=' ) ){
				$code_editor_is_available = 1;
				
				// featured available from wordpress 4.9.0
				wp_enqueue_code_editor( array( 'type' => 'text/css' ) );

			}
			
			// toggle switch
			wp_enqueue_script( 'lc_switch', plugin_dir_url( __FILE__ ) . 'js/lc_switch.min.js', array( 'jquery' ), '', false );

			// use built in wp color picker as dependency
			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/addonify-quick-view-admin.js', array('jquery', 'wp-color-picker'), $this->version, false );


			wp_localize_script( 
				$this->plugin_name, 
				'addonify_objects', 
				array( 
					'code_editor_is_available' 		=> $code_editor_is_available,
					'color_picker_is_available' 	=> $color_picker_is_available
				)
			);

		}

	}

	
	// check if woocommerce is active
	private function is_woocommerce_active() {
		if ( class_exists( 'woocommerce' ) )  return true; 
		return false;
	}

	
	// callback function
	// admin menu
	public function add_menu_callback(){

		// do not show menu if woocommerce is not active
		if ( ! $this->is_woocommerce_active() )  return; 

		add_menu_page( 'Addonify Quick View Settings', 'Addonify', 'manage_options', $this->settings_page_slug, array($this, 'get_settings_screen_contents'), 'dashicons-slides', 76 );
		
		// sub menu
		// redirects to main plugin link
		add_submenu_page(  $this->settings_page_slug, 'Addonify Quick View Settings', 'Quick View', 'manage_options', $this->settings_page_slug, array($this, 'get_settings_screen_contents') );

	}


	// add custom "settings" link in the plugins.php page
	private function custom_plugin_link( $links, $file ){
		
		if ( $file == plugin_basename(dirname(__FILE__, 2) . '/addonify-quick-view.php') ) {
			// add "Settings" link
			$links[] = '<a href="admin.php?page='. $this->settings_page_slug .'">' . __('Settings','addonify-quick-view') . '</a>';
		}
		
		return $links;
	}


	// callback function
	// get contents for settings page screen
	public function get_settings_screen_contents(){
		$current_tab = ( isset($_GET['tabs']) ) ? $_GET['tabs'] : 'settings';
		$tab_url = "admin.php?page=$this->settings_page_slug&tabs=";

		ob_start();
		require_once dirname( __FILE__ ) .'/partials/settings-screen.php';
		echo ob_get_clean();

	}

	// callback function
	// generate settings page form elements
	public function settings_page_ui() {

		// ---------------------------------------------
		// General Options
		// ---------------------------------------------

		$settings_args = array(
			'settings_group_name'	=> 'quick_views_settings',
			'section_id' 			=> 'general_options',
			'section_label'			=> __('GENERAL OPTIONS', 'addonify-quick-view' ),
			'section_callback'		=> '',
			'screen'				=> $this->settings_page_slug.'-settings',
			'fields'				=> array(
				array(
					'field_id'				=> ADDONIFY_DB_INITIALS . 'enable_quick_view',
					'field_label'			=> __('Enable Quick View', 'addonify-quick-view' ),
					'field_callback'		=> array($this, "toggle_switch"),
					'field_callback_args'	=> array( 
						array(
							'name' 			=> ADDONIFY_DB_INITIALS . 'enable_quick_view', 
							'checked' 		=> 1
						)
					) 
				),
				array(
					'field_id'				=> ADDONIFY_DB_INITIALS . 'quick_view_btn_position',
					'field_label'			=> __('Quick View Button Position', 'addonify-quick-view' ),
					'field_callback'		=> array($this, "select"),
					'field_callback_args'	=> array( 
						array(
							'name' 				=> ADDONIFY_DB_INITIALS . 'quick_view_btn_position', 
							'options' 			=> array(
								'after_add_to_cart'		=> __('After Add To Cart Button', 'addonify-quick-view' ),
								'before_add_to_cart' 	=> __('Before Add To Cart Button', 'addonify-quick-view' ),
								'overlay_on_image'		=> __('Overlay On The Product Image', 'addonify-quick-view' )
							) ,
						),
					) 
				),
				array(
					'field_id'				=> ADDONIFY_DB_INITIALS . 'quick_view_btn_label',
					'field_label'			=> __('Quick View Button Label', 'addonify-quick-view' ),
					'field_callback'		=> array($this, "text_box"),
					'field_callback_args'	=> array( 
						array(
							'name'			 => ADDONIFY_DB_INITIALS . 'quick_view_btn_label', 
							'placeholder'	 => __('Quick View', 'addonify-quick-view') 
						)
					), 
				),
				
			)
		);

		// create settings fields
		$this->create_settings( $settings_args );


		// ---------------------------------------------
		// Contents Options
		// ---------------------------------------------

		$settings_args = array(
			'settings_group_name'	=> 'quick_views_settings',
			'section_id' 			=> 'contents_to_show',
			'section_label'			=> __('CONTENTS OPTIONS', 'addonify-quick-view' ),
			'section_callback'		=> '',
			'screen'				=> $this->settings_page_slug.'-settings',
			'fields'				=> array(
				array(
					'field_id'				=> ADDONIFY_DB_INITIALS . 'show_product_image_field',
					'field_label'			=> __('Content To Display', 'addonify-quick-view' ),
					'field_callback'		=> array($this, "checkbox_with_label"),
					'field_callback_args'	=> array( 
						array(
							'label'			=> __('Product Image', 'addonify-quick-view'),
							'name'			=> ADDONIFY_DB_INITIALS . 'show_product_image'
						),
						array(
							'label'			=> __('Product Title', 'addonify-quick-view'),
							'name'			=> ADDONIFY_DB_INITIALS . 'show_product_title'
						),
						array(
							'label'			=> __('Product Rating', 'addonify-quick-view'),
							'name'			=> ADDONIFY_DB_INITIALS . 'show_product_rating'
						),
						array(
							'label'			=> __('Product Price', 'addonify-quick-view'),
							'name'			=> ADDONIFY_DB_INITIALS . 'show_product_price'
						),
						array(
							'label'			=> __('Product Excerpt', 'addonify-quick-view'),
							'name'			=> ADDONIFY_DB_INITIALS . 'show_product_excerpt'
						),
						array(
							'label'			=> __('Add To Cart Button', 'addonify-quick-view'),
							'name'			=> ADDONIFY_DB_INITIALS . 'show_add_to_cart_btn'
						),
						array(
							'label'			=> __('Product Meta', 'addonify-quick-view'),
							'name'			=> ADDONIFY_DB_INITIALS . 'show_product_meta'
						),
					),
				),
				array(
					'field_id'				=> ADDONIFY_DB_INITIALS . 'product_image_display_type',
					'field_label'			=> __('Product Image', 'addonify-quick-view' ),
					'field_callback'		=> array($this, "select"),
					'field_callback_args'	=> array( 
						array(
							'name' 				=> ADDONIFY_DB_INITIALS . 'product_image_display_type', 
							'options' 			=> array(
								'image_only' 			=> __('Product Image Only', 'addonify-quick-view' ),
								'image_or_gallery'		=> __('Product Image or Product Gallery', 'addonify-quick-view' ),
							) ,
						),
					) 
				),
				array(
					'field_id'				=> ADDONIFY_DB_INITIALS . 'enable_lightbox',
					'field_label'			=> __('Enable Lightbox', 'addonify-quick-view' ),
					'field_callback'		=> array($this, "toggle_switch"),
					'field_callback_args'	=> array( 
						array(
							'name' 			=> ADDONIFY_DB_INITIALS . 'enable_lightbox', 
							'checked' 		=> 1
						)
					) 
				),
				array(
					'field_id'				=> ADDONIFY_DB_INITIALS . 'display_view_detail_btn',
					'field_label'			=> __('Display View Detail Button', 'addonify-quick-view' ),
					'field_callback'		=> array($this, "toggle_switch"),
					'field_callback_args'	=> array( 
						array(
							'name' 			=> ADDONIFY_DB_INITIALS . 'show_view_detail_btn', 
							'checked' 		=> 1
						)
					) 
				),
				array(
					'field_id'				=> ADDONIFY_DB_INITIALS . 'view_detail_btn_label',
					'field_label'			=> __('View Detail Button Label', 'addonify-quick-view' ),
					'field_callback'		=> array($this, "text_box"),
					'field_callback_args'	=> array( 
						array(
							'name'			 => ADDONIFY_DB_INITIALS . 'view_detail_btn_label', 
							'placeholder'	 => __('View Detail', 'addonify-quick-view') 
						)
					), 
				),
				
			)
		);

		// create settings fields
		$this->create_settings( $settings_args );


		// ---------------------------------------------
		// Styles Options
		// ---------------------------------------------

		$settings_args = array(
			'settings_group_name'	=> 'quick_views_styles',
			'section_id' 			=> 'style_options',
			'section_label'			=> __('STYLE OPTIONS', 'addonify-quick-view' ),
			'section_callback'		=> '',
			'screen'				=> $this->settings_page_slug.'-styles',
			'fields'				=> array(
				array(
					'field_id'				=> ADDONIFY_DB_INITIALS . 'load_styles_from_plugin',
					'field_label'			=> __('Load Styles From Plugin', 'addonify-quick-view' ),
					'field_callback'		=> array($this, "toggle_switch"),
					'field_callback_args'	=> array( 
						array(
							'name' 			=> ADDONIFY_DB_INITIALS . 'load_styles_from_plugin', 
							'checked' 		=> 1
						)
					) 
				),
			)
		);

		// create settings fields
		$this->create_settings( $settings_args );


		// ---------------------------------------------
		// Content Colors
		// ---------------------------------------------

		$settings_args = array(
			'settings_group_name'	=> 'quick_views_styles',
			'section_id' 			=> 'content_colors',
			'section_label'			=> __('CONTENT COLORS', 'addonify-quick-view' ),
			'section_callback'		=> '',
			'screen'				=> $this->settings_page_slug.'-styles',
			'fields'				=> array(
				array(
					'field_id'				=> ADDONIFY_DB_INITIALS . 'modal_box_color',
					'field_label'			=> __('Modal Box', 'addonify-quick-view' ),
					'field_callback'		=> array($this, "color_picker_group"),
					'field_callback_args'	=> array( 
						array(
							'label'			=> __('Overlay Background Color', 'addonify-quick-view'),
							'name'			=> ADDONIFY_DB_INITIALS . 'modal_overlay_bck_color',
						),
						array(
							'label'			=> __('Background Color', 'addonify-quick-view'),
							'name'			=> ADDONIFY_DB_INITIALS . 'modal_bck_color',
						),
						
					),
				),
				array(
					'field_id'				=> ADDONIFY_DB_INITIALS . 'product_title_color',
					'field_label'			=> __('Product Title', 'addonify-quick-view' ),
					'field_callback'		=> array($this, "color_picker_group"),
					'field_callback_args'	=> array( 
						array(
							'name'			=> ADDONIFY_DB_INITIALS . 'title_color',
						),
						
					),
				),
				array(
					'field_id'				=> ADDONIFY_DB_INITIALS . 'product_rating_color',
					'field_label'			=> __('Product Rating', 'addonify-quick-view' ),
					'field_callback'		=> array($this, "color_picker_group"),
					'field_callback_args'	=> array( 
						array(
							'label'			=> __('Empty Color', 'addonify-quick-view'),
							'name'			=> ADDONIFY_DB_INITIALS . 'rating_color_empty',
						),
						array(
							'label'			=> __('Full Color', 'addonify-quick-view'),
							'name'			=> ADDONIFY_DB_INITIALS . 'rating_color_full',
						),
						
					),
				),
				array(
					'field_id'				=> ADDONIFY_DB_INITIALS . 'product_price_color',
					'field_label'			=> __('Product Price', 'addonify-quick-view' ),
					'field_callback'		=> array($this, "color_picker_group"),
					'field_callback_args'	=> array( 
						array(
							'label'			=> __('Regular Price Color', 'addonify-quick-view'),
							'name'			=> ADDONIFY_DB_INITIALS . 'price_color_regular',
						),
						array(
							'label'			=> __('Sale Price Color', 'addonify-quick-view'),
							'name'			=> ADDONIFY_DB_INITIALS . 'price_color_sale',
						),
						
					),
				),
				array(
					'field_id'				=> ADDONIFY_DB_INITIALS . 'product_excerpt_color',
					'field_label'			=> __('Product Excerpt', 'addonify-quick-view' ),
					'field_callback'		=> array($this, "color_picker_group"),
					'field_callback_args'	=> array( 
						array(
							'name'			=> ADDONIFY_DB_INITIALS . 'excerpt_color',
						),
						
					),
				),
				array(
					'field_id'				=> ADDONIFY_DB_INITIALS . 'product_meta_color',
					'field_label'			=> __('Product Meta', 'addonify-quick-view' ),
					'field_callback'		=> array($this, "color_picker_group"),
					'field_callback_args'	=> array( 
						array(
							'label'			=> __('Color', 'addonify-quick-view'),
							'name'			=> ADDONIFY_DB_INITIALS . 'meta_color',
						),
						array(
							'label'			=> __('Color - On Hover', 'addonify-quick-view'),
							'name'			=> ADDONIFY_DB_INITIALS . 'meta_color_hover',
						),
						
					),
				),
				array(
					'field_id'				=> ADDONIFY_DB_INITIALS . 'product_close_btn_color',
					'field_label'			=> __('Close Button', 'addonify-quick-view' ),
					'field_callback'		=> array($this, "color_picker_group"),
					'field_callback_args'	=> array( 
						array(
							'label'			=> __('Text Color', 'addonify-quick-view'),
							'name'			=> ADDONIFY_DB_INITIALS . 'close_btn_text_color',
						),
						array(
							'label'			=> __('Background Color', 'addonify-quick-view'),
							'name'			=> ADDONIFY_DB_INITIALS . 'close_btn_bck_color',
						),
						array(
							'label'			=> __('Text Color - On Hover', 'addonify-quick-view'),
							'name'			=> ADDONIFY_DB_INITIALS . 'close_btn_text_color_hover',
						),
						array(
							'label'			=> __('Background Color - On Hover', 'addonify-quick-view'),
							'name'			=> ADDONIFY_DB_INITIALS . 'close_btn_bck_color_hover',
						),
						
					),
				),
				array(
					'field_id'				=> ADDONIFY_DB_INITIALS . 'product_other_btn_color',
					'field_label'			=> __('Other Buttons', 'addonify-quick-view' ),
					'field_callback'		=> array($this, "color_picker_group"),
					'field_callback_args'	=> array( 
						array(
							'label'			=> __('Text Color', 'addonify-quick-view'),
							'name'			=> ADDONIFY_DB_INITIALS . 'other_btn_text_color',
						),
						array(
							'label'			=> __('Background Color', 'addonify-quick-view'),
							'name'			=> ADDONIFY_DB_INITIALS . 'other_btn_bck_color',
						),
						array(
							'label'			=> __('Text Color - On Hover', 'addonify-quick-view'),
							'name'			=> ADDONIFY_DB_INITIALS . 'other_btn_text_color_hover',
						),
						array(
							'label'			=> __('Background Color - On Hover', 'addonify-quick-view'),
							'name'			=> ADDONIFY_DB_INITIALS . 'other_btn_bck_color_hover',
						),
						
					),
				),
				array(
					'field_id'				=> ADDONIFY_DB_INITIALS . 'custom_css',
					'field_label'			=> __('Custom CSS', 'addonify-quick-view' ),
					'field_callback'		=> array($this, "text_area"),
					'field_callback_args'	=> array( 
						array(
							'name'			=> ADDONIFY_DB_INITIALS . 'custom_css',
							'attr'			=> 'rows="5" class="large-text code"'
						),
						
					),
				),
				
			)
		);

		// create settings fields
		$this->create_settings( $settings_args );
		
	}

	
	// this function will create settings section, fields and register that settings in a database
	private function create_settings($args){
		
		// define section ---------------------------
		add_settings_section($args['section_id'], $args['section_label'], $args['section_callback'], $args['screen'] );

		foreach($args['fields'] as $field){
			
			// create label
			add_settings_field( $field['field_id'], $field['field_label'], $field['field_callback'], $args['screen'], $args['section_id'], $field['field_callback_args'] );
			
			foreach( $field['field_callback_args'] as $sub_field){
				register_setting( $args['settings_group_name'],  $sub_field['name'] );
			}

		}
		
	}


	// -------------------------------------------------
	// form element helpers 
	// -------------------------------------------------

	public function text_box($arguments){
		foreach($arguments as $args){
			$placeholder = isset( $args['placeholder'] ) ? $args['placeholder'] : '';
			$db_value = get_option($args['name'], $placeholder);

			ob_start();
			require dirname( __FILE__ ) .'/partials/input_textbox.php';
			echo ob_get_clean();
		}
	}

	public function text_area($arguments){
		foreach($arguments as $args){
			$placeholder = isset( $args['placeholder'] ) ? $args['placeholder'] : '';
			$db_value = get_option($args['name'], $placeholder);
			$attr = isset( $args['attr'] ) ? $args['attr'] : '';

			ob_start();
			require dirname( __FILE__ ) .'/partials/input_textarea.php';
			echo ob_get_clean();
		}
	}

	public function toggle_switch($arguments){
		foreach($arguments as $args){
			$args['attr'] = ' class="lc_switch"';
			$this->checkbox($args);
		}
	}

	public function color_picker_group($args){
		foreach($args as $arg){
			$default =  isset( $arg['default'] )  ? $arg['default'] : '#000000';
			$db_value = get_option($arg['name'], $default);

			ob_start();
			require dirname( __FILE__ ) .'/partials/input_colorpicker.php';
			echo ob_get_clean();
		}
	}

	public function checkbox_with_label($args){
		foreach($args as $arg){
			ob_start();
			require dirname( __FILE__ ) .'/partials/checkbox_group.php';
			echo ob_get_clean();
		}
	}

	public function checkbox($args){
		$default_state = ( array_key_exists('checked', $args) ) ? $args['checked'] : 1;
		$db_value = get_option($args['name'], $default_state);
		$is_checked = ( $db_value ) ? 'checked' : '';
		$attr = ( array_key_exists('attr', $args) ) ? $args['attr'] : '';

		ob_start();
		require dirname( __FILE__ ) .'/partials/input_checkbox.php';
		echo ob_get_clean();
	}

	public function select($arguments){
		foreach($arguments as $args){

			$db_value = get_option($args['name']);
			$options = ( array_key_exists('options', $args) ) ? $args['options'] : array();
			
			ob_start();
			require dirname( __FILE__ ) .'/partials/input_select.php';
			echo ob_get_clean();
		}
	}

	
	// callback function
	// show notification after form submission
	public function form_submission_notification_callback(){
		settings_errors();
	}

	
	// callback
	// show error message in dashboard if woocommerce is not active
	public function show_woocommerce_not_active_notice_callback(){

		if( ! $this->is_woocommerce_active() ){
			add_action('admin_notices', 'woocommerce_not_active_notice' );
		}


		function woocommerce_not_active_notice() {
			
			ob_start();
			require dirname( __FILE__ ) .'/partials/woocommerce_not_active_notice.php';
			echo ob_get_clean();

		}

	}

}