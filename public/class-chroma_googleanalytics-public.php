<?php

/**
* The public-facing functionality of the plugin.
*/
class chroma_googleanalytics_Public {

	/**
	* The ID of this plugin.
	*/
	private $chroma_googleanalytics;

	/**
	* The version of this plugin.
	*/
	private $version;

	/**
	* Initialize the class and set its properties.
	*/
	public function __construct( $chroma_googleanalytics, $version ) {
		// default construct
		$this->chroma_googleanalytics = $chroma_googleanalytics;
		$this->version = $version;
		// plugin specific construct

	}

	/**
	* Register the JavaScript for the public-facing side of the site.
	*/
	public function enqueue_scripts() {

		$options = chroma_googleanalytics::$options;
		$footer = (isset($options["settings"]["chroma_googleanalytics-infooter"])) ? true : false;

		wp_enqueue_script( $this->chroma_googleanalytics."-public", plugin_dir_url( __FILE__ ) . 'js/chroma_googleanalytics-public.min.js', array(), $this->version, $footer );

		// default options array
		$array = array(
			'debug' => chroma_googleanalytics::$debug,
			'options' =>  $options,
		);
		// localize chromabox scripts
		wp_localize_script( $this->chroma_googleanalytics."-public", 'chroma_googleanalytics_public_vars', $array );

	}

}
