<?php
// let users change the session cookie name
if( ! defined( 'WP_SESSION_COOKIE' ) )
	define( 'WP_SESSION_COOKIE', '_wp_session' );

if ( ! class_exists( 'Recursive_ArrayAccess' ) ) {
	require_once( 'class-recursive-arrayaccess.php' );
}

// Only include the functionality if it's not pre-defined.
if ( ! class_exists( 'WP_Session' ) ) {
	require_once( 'class-wp-session.php' );
	require_once( 'wp-session.php' );
}
