<?php
/**
 * CMB Theme Options
 * @version 0.1.0
 */
class myprefix_Admin{
   /**
	 * Options field prefix
	 * @var string
	 */
	protected static $prefix = '_visualive_theme_';

	/**
	 * Option key, and option page slug
	 * @var string
	 */
	protected static $key = 'options';

	/**
	 * Array of metaboxes/fields
	 * @var array
	 */
	protected static $theme_options = array();

	/**
	 * Options Page title
	 * @var string
	 */
	protected $title = '';

	/**
	 * Constructor
	 * @since 0.1.0
	 */
	public function __construct() {
		// Set our title
		$this->title = __( 'Site Options', VACB2014_TEXTDOMAIN );
	}

	/**
	 * Initiate our hooks
	 * @since 0.1.0
	 */
	public function hooks() {
		add_action( 'admin_init', array( $this, 'myprefix_init' ) );
		add_action( 'admin_menu', array( $this, 'add_page' ) );
	}

	/**
	 * Register our setting to WP
	 * @since  0.1.0
	 */
	public function myprefix_init() {
		register_setting( self::$key, self::$key );
	}

	/**
	 * Add menu options page
	 * @since 0.1.0
	 */
	public function add_page() {
		$this->options_page = add_menu_page( $this->title, $this->title, 'manage_options', self::$key, array( $this, 'admin_page_display' ) );
		add_action( 'admin_head-' . $this->options_page, array( $this, 'admin_head' ) );
	}

	/**
	 * CSS, etc
	 * @since  0.1.0
	 */
	public function admin_head() {
		// CSS, etc
	}

	/**
	 * Admin page markup. Mostly handled by CMB
	 * @since  0.1.0
	 */
	public function admin_page_display() {
		?>
		<div class="wrap cmb_options_page <?php echo self::$key; ?>">
			<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
			<?php cmb_metabox_form( $this->option_fields(), self::$key ); ?>
		</div>
		<?php
	}

	/**
	 * Defines the theme option metabox and field configuration
	 * @since  0.1.0
	 * @return array
	 */
	public static function option_fields() {

		// Only need to initiate the array once per page-load
		if ( ! empty( self::$theme_options ) )
			return self::$theme_options;

		self::$theme_options = array(
			'id'         => 'theme_options',
			'show_on'    => array( 'key' => 'options-page', 'value' => array( self::$key, ), ),
			'show_names' => true,
			'fields'     => array(
				array(
					'name' => __( 'Test Text', VACB2014_TEXTDOMAIN ),
					'desc' => __( 'field description (optional)', VACB2014_TEXTDOMAIN ),
					'id'   => self::$prefix . 'test_text',
					'type' => 'text',
				),
				array(
					'name'    => __( 'Test Color Picker', VACB2014_TEXTDOMAIN ),
					'desc'    => __( 'field description (optional)', VACB2014_TEXTDOMAIN ),
					'id'      => self::$prefix . 'test_colorpicker',
					'type'    => 'colorpicker',
					'default' => '#ffffff'
				),
			),
		);
		return self::$theme_options;
	}

	/**
	 * Make public the protected $key variable.
	 * @since  0.1.0
	 * @return string  Option key
	 */
	public static function key() {
		return self::$key;
	}

}
// Get it started
$myprefix_Admin = new myprefix_Admin();
$myprefix_Admin->hooks();

/**
 * Wrapper function around cmb_get_option
 * @since  0.1.0
 * @param  string  $key Options array key
 * @return mixed        Option value
 */
function myprefix_get_option( $key = '' ) {
	return cmb_get_option( myprefix_Admin::key(), $key );
}
