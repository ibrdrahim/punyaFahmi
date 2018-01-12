<?php
/**
 * BI_Session Class.
 *
 * @class       BI_Session
 * @version		1.0
 * @author lafif <lafif@astahdziq.in>
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * BI_Session Class
 *
 * @since 1.0
 */
class BI_Session {

	/**
	 * Holds our session data
	 *
	 * @var array
	 * @access private
	 * @since 1.0
	 */
	private $session;

	/**
	 * Get things started
	 *
	 * Defines our WP_Session constants, includes the necessary libraries and
	 * retrieves the WP Session instance
	 *
	 * @since 1.0
	 */
	public function __construct() {
		$this->includes();

		$this->session = WP_Session::get_instance();;

	}

	/**
	 * Retrieve a session variable
	 *
	 * @access public
	 * @since 1.0
	 * @param string $key Session key
	 * @return string Session variable
	 */
	public function get( $key ) {
		$key = sanitize_key( $key );
		return isset( $this->session[ $key ] ) ? maybe_unserialize( $this->session[ $key ] ) : false;
	}

	/**
	 * Set a session variable
	 *
	 * @since 1.0
	 *
	 * @param string $key Session key
	 * @param integer $value Session variable
	 * @return string Session variable
	 */
	public function set( $key, $value ) {

		$key = sanitize_key( $key );

		if ( is_array( $value ) ) {
			$this->session[ $key ] = serialize( $value );
		} else {
			$this->session[ $key ] = $value;
		}

		return $this->session[ $key ];
	}

	public function includes(){
		// Use WP_Session (default)
		if ( ! defined( 'WP_SESSION_COOKIE' ) ) {
			define( 'WP_SESSION_COOKIE', 'bi_wp_session' );
		}
		if ( ! class_exists( 'Recursive_ArrayAccess' ) ) {
			require_once 'wp-session-manager/class-recursive-arrayaccess.php';
		}
		if ( ! class_exists( 'WP_Session' ) ) {
			require_once 'wp-session-manager/class-wp-session.php';
			require_once 'wp-session-manager/wp-session.php';
		}
	}

}
