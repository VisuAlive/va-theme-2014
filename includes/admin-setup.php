<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


if ( ! current_user_can('update_core') ) {
	define( 'DISALLOW_FILE_MODS', true );
	define( 'DISALLOW_FILE_EDIT', true );
	define( 'DISALLOW_UNFILTERED_HTML', true );
}
add_filter( 'auto_update_plugin', '__return_true' );
add_filter( 'auto_update_theme', '__return_true' );
add_filter( 'login_errors', '__return_false' );


if ( ! function_exists( '_visualive_theme_basic_auth' ) ) :
function _visualive_theme_basic_auth() {
	$auth_id   = VACB2014_AUTH_ID;
	$auth_pass = VACB2014_AUTH_PASS;
	$improper  = array('', 'root', 'admin', 'webmaster', 'pass', 'password');

	nocache_headers();

	if ( is_user_logged_in() || in_array( $auth_id, $improper ) || in_array( $auth_pass, $improper ) ) {
		return;
	}

	/**
	 * Echo basic auth
	 *
	 * @link http://www.phpbook.jp/tutorial/auth/index1.html
	 */
	if ( ! isset($_SERVER['PHP_AUTH_USER']) && ! isset($_SERVER['PHP_AUTH_PW']) ) {
		header( 'WWW-Authenticate: Basic realm="Private Page"' );
		header( 'HTTP/1.0 401 Unauthorized' );
		die( __('Authorization Required.', VACB2014_TEXTDOMAIN) );
	} else {
		if ( $_SERVER['PHP_AUTH_USER'] != $auth_id || $_SERVER['PHP_AUTH_PW'] != $auth_pass ) {
			header( 'WWW-Authenticate: Basic realm="Private Page"' );
			header( 'HTTP/1.0 401 Unauthorized' );
			die( __('Authorization Required.', VACB2014_TEXTDOMAIN) );
		}
	}
}
endif; // _visualive_theme_basic_auth
add_action( 'login_init', '_visualive_theme_basic_auth', 0 );


/**
 * The message of the purport that initial setting of theme is carried out is sent.
 *
 * @return string
 */
if ( ! function_exists( '_visualive_theme_admin_setup_message' ) ) :
function _visualive_theme_admin_setup_message() {
	$auth_id   = VACB2014_AUTH_ID;
	$auth_pass = VACB2014_AUTH_PASS;
	$improper  = array('', 'root', 'admin', 'webmaster', 'pass', 'password');
	// functions.phpを開いて、VACB2014_AUTH_IDとVACB2014_AUTH_PASSを編集して、セットアップを完成してください。
	$message   = __( 'Please open ' . get_template_directory() . '/functions.php,<br>edit VACB2014_AUTH_ID and VACB2014_AUTH_PASS, and complete a setup.', VACB2014_TEXTDOMAIN );

	if ( in_array( $auth_id, $improper ) || in_array( $auth_pass, $improper ) ) {
		echo "<div id='message' class='error'><p><strong>$message</strong></p></div>";
	}
}
endif; // _visualive_theme_admin_setup_message
add_action( 'admin_notices', '_visualive_theme_admin_setup_message' );


/**
 * Login page custom css
 *
 * @return string
 */
if ( ! function_exists( '_visualive_theme_admin_css_url' ) ) :
function _visualive_theme_admin_css_url() {
	wp_enqueue_style( 'va-login', get_stylesheet_directory_uri() . '/assets/css/login.css', array() );
	wp_print_styles();
}
endif;
add_action( 'login_enqueue_scripts', '_visualive_theme_admin_css_url' );


if ( ! function_exists( '_visualive_theme_remove_dashboard_widgets' ) ) :
function _visualive_theme_remove_dashboard_widgets() {
	global $wp_meta_boxes;
	// 現在の状況
	unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_right_now']);
	// 最近のコメント
	unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_comments']);
	// 被リンク
	unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_incoming_links']);
	// プラグイン
	unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_plugins']);
	// クイック投稿
	unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_quick_press']);
	// 最近の下書き
	unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_recent_drafts']);
	// WordPressブログ
	unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_primary']);
	// WordPressフォーラム
	unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_secondary']);
}
endif;
// add_action( 'wp_dashboard_setup', '_visualive_theme_remove_dashboard_widgets' );


/**
 * フッタの先頭に何かを出力する場合、echo
 * @return void
 */
// if ( ! function_exists( '_visualive_theme_in_admin_footer' ) ) :
// function _visualive_theme_in_admin_footer() {
// 	echo '<p>in_admin_footer</p>';
// }
// endif;
// add_filter( 'in_admin_footer', '_visualive_theme_in_admin_footer' );


if ( ! function_exists( '_visualive_theme_custom_admin_footer_text' ) ) :
/**
 * フッタの文章を変更する。
 * @param string $code
 */
function _visualive_theme_custom_admin_footer_text() {
	return __( 'ウェブサイト制作でお困りの際は <a href="http://visualive.jp/" target="_blank">ヴィジュアライブ</a> 迄お問い合わせください。', VACB2014_TEXTDOMAIN );
}
endif;
add_filter( 'admin_footer_text', '_visualive_theme_custom_admin_footer_text' );


if ( ! function_exists( '_visualive_theme_custom_update_footer' ) ) :
/**
 * フッタのワードプレスのバージョン部分のテキストを設定する。
 * @param strign $string
 */
function _visualive_theme_custom_update_footer() {
	return 'Powered by VisuAlive';
}
endif;
add_filter( 'update_footer', '_visualive_theme_custom_update_footer', 9999 );
