<?php
/**
 * Plugin Name: BuddyPress Google Authenticator Helper
 * Version: 1.0.0
 * Plugin URI: https://buddydev.com
 * Author: BuddyDev
 * Author URI: https://buddydev.com
 * Description: Uses WordPress Google Authenticator plugin( https://wordpress.org/plugins/google-authenticator/ )
 *  and allows users to manage their settings from BuddyPress Profile.
 */


/**
 * Note, you must have https://wordpress.org/plugins/google-authenticator/ active else the plugin won't display settings
 *
 * Class BP_Google_Authenticator_Helper
 */
class BP_Google_Authenticator_Helper {

	public function __construct() {
	}

	/**
	 * Set up hooks
	 */
	public function setup() {

		add_action( 'bp_core_general_settings_after_save', array( $this, 'update_settings' ) );
		add_action( 'bp_core_general_settings_before_submit', array( $this, 'display_settings' ) );
		add_action( 'bp_enqueue_scripts', array( $this, 'load_js' ) );

		add_filter( 'get_user_option_googleauthenticator_description', array( $this, 'filter_description' ) );
	}

	/**
	 * Display settings on user profile->settings->general tab
	 */
	public function display_settings() {

		if ( ! $this->is_active() ) {
			return ;
		}
		//now, let us prepare the environment for the GA plugin

		global $google_authenticator, $user_id;
		//yes, we are doing it, setting $user_id to global to help GA use it, there should not be any side effect
		$user_id = bp_displayed_user_id();
		//another compat requirement
		if ( ! defined( 'IS_PROFILE_PAGE' ) ) {
			define('IS_PROFILE_PAGE', true );
		}

		if ( is_callable( array( $google_authenticator, 'profile_personal_options' ) ) ) {
			call_user_func( array( $google_authenticator, 'profile_personal_options' ) );
		}
	}

	/**
	 * Save settings
	 */
	public function update_settings() {

		if ( ! $this->is_active() ) {
			return ;
		}

		global $google_authenticator, $user_id;
		$user_id = bp_displayed_user_id();

		//personal_options_update();
		if ( is_callable( array( $google_authenticator, 'personal_options_update' ) ) ) {
			call_user_func( array( $google_authenticator, 'personal_options_update' ) );

			$bp = buddypress();

			if ( isset( $bp->template_message_type ) && $bp->template_message_type !='errror' ) {
				bp_core_add_message( 'Settings updated' );
			}
		}

	}

	/**
	 * Load js
	 */
	public function load_js() {

		if ( ! $this->is_active() ) {
			return ;
		}

		global $google_authenticator;

		if ( is_callable( array( $google_authenticator, 'add_qrcode_script' ) ) ) {
			call_user_func( array( $google_authenticator, 'add_qrcode_script' ) );
		}

	}

	public function filter_description( $desc = '' ) {

		if ( ! $desc ) {
			$desc = get_bloginfo( 'name' );
		}

		return $desc;
	}

	/**
	 * Is Google authenticator plugin active and is it My General settings page?
	 *
	 * @return bool
	 */
	public function is_active() {
		return class_exists( 'GoogleAuthenticator' ) && bp_is_my_profile() && bp_is_settings_component() && bp_is_current_action( 'general' );
	}
}

$helper = new BP_Google_Authenticator_Helper();
$helper->setup();