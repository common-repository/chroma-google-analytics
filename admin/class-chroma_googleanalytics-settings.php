<?php

// The admin/settings functionality of the plugin.
class chroma_googleanalytics_Settings {

    // The ID of this plugin.
    private $chroma_googleanalytics;

    // The version of this plugin.
    private $version;

    // Initialize the class and set its properties.
    public $settings;
    public $options;
    public $roles;

    public function __construct( $chroma_googleanalytics, $version ) {
        $this->chroma_googleanalytics = $chroma_googleanalytics;
        $this->version = $version;

        $this->options = chroma_googleanalytics::$options;
        $this->settings = chroma_googleanalytics::$settings;

        if ( ! function_exists( 'get_editable_roles' ) ) {
            require_once ABSPATH . 'wp-admin/includes/user.php';
        }
        chroma_googleanalytics::$roles = get_editable_roles();
    }

    // Enqueue settings page CSS
    public function enqueue_styles(){
        wp_enqueue_style( $this->chroma_googleanalytics."-settings", plugin_dir_url( __FILE__ ) . 'css/chroma_googleanalytics-settings.css', array(), $this->version, 'all' );
    }


	// Enqueue settings page scripts
	public function enqueue_scripts() {

		$options = chroma_googleanalytics::$options;

		wp_enqueue_script( $this->chroma_googleanalytics."-settings", plugin_dir_url( __FILE__ ) . 'js/chroma_googleanalytics-settings.min.js', array(), $this->version, false );

		// default options array
		$array = array(
			'debug' => chroma_googleanalytics::$debug,
			'options' =>  $options,
		);
		// localize chromabox scripts
		wp_localize_script( $this->chroma_googleanalytics."-settings", 'chroma_googleanalytics_settings_vars', $array );

	}

    // Universal add form function
    public function add_form($type, $subtype, $name, $value, $args = array()) {

        // Start Field HTML string
        $field = "<".$type." ";
        $field .= "type='".$subtype."' ";
        $field .= "id='".$name."' ";
        $field .= "name='chroma_googleanalytics[settings][".$name."]' ";

        if ($subtype=="number" && array_key_exists("step",$args)) {
            $field .= "step='".$args["step"]."' ";
        }

        if ($subtype=="text" || $subtype=="number" || $subtype=="textarea" || $subtype=="hidden"){
            $field .= "value='".$value."' ";
        } elseif ($subtype=="checkbox" ){
            if ($value == "on") {
                $field .= "checked='checked' ";
            }
        }

        $field .= ">";
        // Close Field opening tag

        if ($subtype=="textarea") {
            $field .= $value."</textarea>";
        } elseif ($subtype=="select") {
            foreach ($args as $op) {
                $op = strtolower($op);
                $sel = ($op == $value) ? "selected" : "";
                $field .= "<option value='".$op."' ".$sel.">" .ucfirst($op)."</option>";
            }
            $field .= "</select>";
        }

        // Complete field HTML and echo
        echo $field;

    }

    // Add options page
    public function settings() {
        add_options_page( 'Chroma Google Analytics Options', 'Chroma Google Analytics', 'manage_options', 'chroma_googleanalytics_settings', array($this,'settings_callback') );
    }

    // Settings Callback
    public function settings_callback() {
        ?>
        <div id="chroma_googleanalytics-settings-container" class="wrap">
            <h2><?php _e('Chroma Google Analytics Settings', 'textdomain'); ?></h2>
            <form action="options.php" method="POST">
                <?php settings_fields('chroma_googleanalytics-settings-group'); ?>
                <?php do_settings_sections('chroma_googleanalytics_settings'); ?>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    // Filter settings Saved
    // to add version key to options array
    public function filter_update_option($new_value, $old_value){
        $new_value["version"] = $this->version;
        return $new_value;
    }

    public function settings_init() {

        // Filter settings Saved
        add_filter( 'pre_update_option_chroma_googleanalytics', array($this,'filter_update_option'), 10, 2 );

        // REGISTER SETTING
        // register_setting( $option_group, $option_name, $sanitize_callback );
        register_setting( 'chroma_googleanalytics-settings-group', 'chroma_googleanalytics', array($this, "validate_settins") );

        // SECTIONS
        // add_settings_section( $id, $title, $callback, $page );
        $this->settings_sections("section_1","Tracking ID");
        $this->settings_sections("section_2","User Roles to Exclude From Tracking");
        $this->settings_sections("section_3","Run Tracking Code after full page load");
        $this->settings_sections("section_4","Exclude traffic from LOCALHOST domain");

        // FIELDS
        // $this->settings_fields( $section, $name, $label, $type, $subtype, $args );
        $this->settings_fields("section_1", "chroma_googleanalytics-id", "Tracking ID", "input", "text", array());

        foreach (chroma_googleanalytics::$roles as $key => $value) {
            $this->settings_fields("section_2", "chroma_googleanalytics-roles-".$key, $key, "input", "checkbox", array());
        }
        $this->settings_fields("section_3", "chroma_googleanalytics-infooter", "Wait for full page load", "input", "checkbox", array());
        $this->settings_fields("section_4", "chroma_googleanalytics-localhost", "Exclude LOCALHOST traffic from tracking", "input", "checkbox", array());

    }

    // Universal add settings section function
    public function settings_sections($section,$text){
        add_settings_section( $section.'_start', "", array($this,'section_start_callback'), 'chroma_googleanalytics_settings');
        add_settings_section( $section, __( $text, 'textdomain' ), array($this, $section."_callback"), 'chroma_googleanalytics_settings' );
        add_settings_section( $section.'_end', "", array($this,'section_end_callback'), 'chroma_googleanalytics_settings' );
    }

    // Universal add field function
    public function settings_fields($section,$name,$label,$type,$subtype,$args){
        add_settings_field( $name,__( $label, 'textdomain' ), array($this, 'field_callback'), 'chroma_googleanalytics_settings', $section, array($type,$subtype,$name,$args) );
    }

    // Callback responsible for section wrapper opening tag
    public function section_start_callback($args) {
        if ($args["id"] == "section_2_start" ) {
            echo "<h2 class='chroma_googleanalytics-settings-subhead'>Advanced Options</h2>";
            echo "<p class='chroma_googleanalytics-settings-subhead-text'>Features allowing for fine tuning of how the plugin functions. Leave at default settings if you are unsure.</p>";
        }
        echo "<div id='chroma_googleanalytics-settings-section-".$args["id"]."' class='chroma_googleanalytics-settings-section-container'>";
    }

    // Callback responsible for section wrapper closing tag
    public function section_end_callback() {
        echo "</div>";
    }

    // Callback responsible description wrapper
    public function section_callback($text) {
        echo "<p class='chroma_googleanalytics-settings-description'>";
        _e( $text, 'textdomain' );
        echo "</p>";
    }

    // Tracking ID setting description
    public function section_1_callback() {
        $text = 'Input your Google Analyitics Tracking ID below. This can be found in your Google Analytics admin panel: Admin &gt; Property &gt; Tracking Info &gt; Tracking Code.<br>Go to <a href="https://analytics.google.com">analytics.google.com</a>';
        $this->section_callback($text);
    }

    // USer roles setting description
    public function section_2_callback() {
        $text = 'Check the user roles to be excluded from tracking. For example, an administrator may spend hours a day on their website and not want their activity to be logged.';
        $this->section_callback($text);
    }

    // Head/footer setting description
    public function section_3_callback() {
        $text = 'By default, the tracking code is placed in the &#60;head&#62; section of a page, meaning a visit will be tracked even if the visitor leaves before the page has fully loaded. To force tracking to wait till the page has loaded before registering a visit, check this option.';
        $this->section_callback($text);
    }

    // LOCALHOST setting description
    public function section_4_callback() {
        $text = 'When this setting is checked, tracking will not register from LOCALHOST domains. Handy for designers migrating a website back and forth between local and live environements and not needing to remember ot disable/re-enable the plugin.';
        $this->section_callback($text);
    }

    // Universal field callback
    public function field_callback(array $args) {
        $type = $args[0];
        $subtype = $args[1];
        $name = $args[2];

        if (isset(chroma_googleanalytics::$settings[$name])) {
            $value = esc_attr( chroma_googleanalytics::$settings[$name] );
        } else {
            $value = null;
        }

        $this->add_form($type,$subtype,$name,$value);
    }

    // INPUT VALIDATION
    public function validate_settins( $input ) {

        $output = $input;

        $str = sanitize_text_field($input['settings']['chroma_googleanalytics-id']);
        $str = strtoupper($str);
        $reg = preg_match("/^(U|u)(A|a)-\d+-\d+$/i", strval($str)) ? true : false;

        if ( $reg ) {
            $output['settings']['chroma_googleanalytics-id'] = $str;
        } else {
            add_settings_error( 'chroma_googleanalytics', 'invalid-chroma_googleanalytics-id', 'You have entered an invalid GA Tracking ID. Please check and try again.' );
        }

        //$output = $input;
        return $output;
    }

}
?>
