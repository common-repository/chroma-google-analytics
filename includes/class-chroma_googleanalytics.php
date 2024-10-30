<?php

// The core plugin class.
class chroma_googleanalytics {


	// The loader that's responsible for maintaining and registering all hooks that power
	// the plugin.
	protected $loader;


	// The unique identifier of this plugin.
	protected $chroma_googleanalytics;


	// The current version of the plugin.
	protected $version;


	// Default boilerplate variables
	public static $options;
	public static $settings;
	public static $default_options;
	public static $debug = 0;

	// Plug-in specific variables
	public static $roles;

	// Define the core functionality of the plugin.
	public function __construct() {
		$this->chroma_googleanalytics = 'chroma_googleanalytics';
		$this->version = '1.3';

		// Define option/settins static variables
		self::$options = get_option('chroma_googleanalytics');
		self::$settings = self::$options["settings"];

		// If options dont exist, create them
		if (!self::$options || !self::$settings) {
			$this->define_options();
			$this->fill_missing_options();
		};

		// If plugin updated, update options
		if (self::$options["version"] != $this->version ) {
			$this->define_options();
			$this->new_version();
		}

		$this->load_dependencies();

		// Run approriate functions
		// depending on whether user is on admin or public facing side
		if (is_admin()) {

			// Run Admin settings hooks
			$this->define_settings_hooks();

		} elseif (!is_admin()) {

			$skip = false;

			// Check if currently in LOCALHOST environment and do not track if LOCALHOST is excluded in options
			if (isset(self::$settings["chroma_googleanalytics-localhost"]) && strtolower($_SERVER['SERVER_NAME']) == "localhost" || !self::$settings["chroma_googleanalytics-id"]){
				$skip = true;
			} else {
				// Check current user roles against exclusions defined in options
				$current_user = wp_get_current_user();
				if ($current_user->ID && $skip == false) {
					foreach ($current_user->roles as $value) {
						if (isset(self::$settings["chroma_googleanalytics-roles-".$value])){
							$skip = true;
							break;
						}
					}

				};
			}

			// Run public hooks
			// assuming $skip is not flagged as true
			if (!$skip)
			$this->define_public_hooks();
		}

	}

	public function new_version() {
		self::$options["version"] = $this->version;
		update_option("chroma_googleanalytics", self::$options);
	}

	// Load the required dependencies for this plugin.
	private function load_dependencies() {

		// The class responsible for orchestrating the actions and filters of the core plugin.
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-chroma_googleanalytics-loader.php';

		// The class responsible for defining all actions that occur in the admin settings area.
		if (is_admin())
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-chroma_googleanalytics-settings.php';

		// The class responsible for defining all actions that occur in the public-facing side of the site.
		if (!is_admin())
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-chroma_googleanalytics-public.php';



		$this->loader = new chroma_googleanalytics_Loader();
	}

	// Register all of the hooks related to the admin area functionality of the plugin.
	private function define_settings_hooks() {
		$plugin_settings = new chroma_googleanalytics_Settings( $this->get_chroma_googleanalytics(), $this->get_version() );

		// settings page init
		$this->loader->add_action( 'admin_menu', $plugin_settings, 'settings' );
		$this->loader->add_action( 'admin_init', $plugin_settings, 'settings_init' );

		// enqueue scrips and styles if on settings page
		if (isset($_GET["page"]) && $_GET["page"] == "chroma_googleanalytics_settings" ) {
			$this->loader->add_action( 'admin_enqueue_scripts', $plugin_settings, 'enqueue_styles' );
			$this->loader->add_action( 'admin_enqueue_scripts', $plugin_settings, 'enqueue_scripts' );
		}
	}


	// Register all of the hooks related to the public-facing functionality of the plugin.
	private function define_public_hooks() {
		$plugin_public = new chroma_googleanalytics_Public( $this->get_chroma_googleanalytics(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}


	// Define all the default options
	private function define_options() {
		// Define $roles static variable
		if ( ! function_exists( 'get_editable_roles' ) ) {
			require_once ABSPATH . 'wp-admin/includes/user.php';
		}
		self::$roles = get_editable_roles();

		self::$default_options = array(
			'settings' => array(
				'chroma_googleanalytics-id' => "",
				'chroma_googleanalytics-localhost' => "on",
			),
			'version' => $this->version,
		);

	}

	// Fix any missing options
	public function fill_missing_options(){
		if (!self::$options || !self::$settings) {
			update_option("chroma_googleanalytics", self::$default_options);
			self::$options = self::$default_options;
			self::$settings = self::$options["settings"];
		} else {
			$default_settings = self::$default_options['settings'];
			foreach ($default_settings as $key=>$values) {
				if (!isset(self::$settings[$key])) {
					self::$settings[$key] = $values;
				}
			}

			self::$options["settings"] = self::$settings;
			update_option("chroma_googleanalytics", self::$options);
		}
	}

	// Run the loader to execute all of the hooks with WordPress.
	public function run() {
		$this->loader->run();
	}

	// The name of the plugin used to uniquely identify it within the context of
	// WordPress and to define internationalization functionality.
	public function get_chroma_googleanalytics() {
		return $this->chroma_googleanalytics;
	}

	// The reference to the class that orchestrates the hooks with the plugin.
	public function get_loader() {
		return $this->loader;
	}

	// Retrieve the version number of the plugin.
	public function get_version() {
		return $this->version;
	}

}
