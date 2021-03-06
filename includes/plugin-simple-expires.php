<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'VA_Simple_Expires' ) ) :
class VA_Simple_Expires {
	function __construct() {
		add_action( 'admin_menu', array( $this, 'loadAdmin' ) );
		add_action( 'admin_head', array( $this, 'add_post_status' ) );
		add_action( 'init', array( $this, 'simple_expires' ) );
		add_action( 'add_meta_boxes', array( $this, 'scadenza_add_custom_box' ) );
		add_action( 'save_post', array( $this, 'scadenza_save_postdata' ) );
	}

	function loadAdmin() {
		wp_enqueue_style( 'vase-style', get_stylesheet_directory_uri() . '/assets/css/plugin/vase-style.css' );
	}

	private function va_se_get_post_types() {
		$default_post = array('post' => 'post', 'page' => 'page');
		$custom_post  = get_post_types( array( 'public' => true, '_builtin' => false ), 'names' );
		return array_merge( $default_post, $custom_post );
	}
	// end Riboni Igor

	function add_post_status() {
		global $post;
		$complete = '';
		$label = '';
		$get_posttype = $this->va_se_get_post_types();
		$now_posttype = get_post_type();

		if( in_array($now_posttype, $get_posttype) ) :
			if($post->post_status == 'expiration'){
				$complete = ' selected=\"selected\"';
				$label = '<span id=\"post-status-display\"> ' . __( '公開終了', VACB_TEXTDOMAIN ) . '</span>';
			}
			$option = '<option value=\"expiration\" '.$complete.'> ' . __( '公開終了', VACB_TEXTDOMAIN ) . '</option>';
			?>
<script>
jQuery(document).ready(function($){
	$("select#post_status").append("<?php echo $option; ?>");
	$("span#post-status-display").append("<?php echo $label; ?>");
});
</script>
		<?php
		endif;
	}

	function simple_expires() {
		global $wpdb;

		// Register post status
		register_post_status( 'expiration', array(
			'label'                     => _x( '公開終了', 'post', VACB_TEXTDOMAIN ),
			'protected'                 => true,
			'_builtin'                  => true,
			'public'                    => false,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( '公開終了 <span class="count">(%s)</span>', '公開終了 <span class="count">(%s)</span>', VACB_TEXTDOMAIN )
		) );

		//20 june 2011: bug fix by Kevin Roberts for timezone
		$result = $wpdb->get_results( $wpdb->prepare("
			SELECT postmetadate.post_id
			FROM $wpdb->postmeta AS postmetadate, $wpdb->postmeta AS postmetadoit, $wpdb->posts AS posts
			WHERE postmetadoit.meta_key = 'scadenza-enable'
			AND postmetadoit.meta_value = '1'
			AND postmetadate.meta_key = 'scadenza-date'
			AND postmetadate.meta_value <= %s
			AND postmetadate.post_id = postmetadoit.post_id
			AND postmetadate.post_id = posts.ID
			AND posts.post_status = 'publish'
		", current_time("mysql") ) );
		// Act upon the results
		if ( ! empty( $result ) ) :
			// Proceed with the updating process
			// step through the results
			foreach ( $result as $cur_post ) :
				$update_post = array('ID' => $cur_post->post_id);
				// Get the Post's ID into the update array
				$update_post['post_status'] = 'expiration';
				wp_update_post( $update_post );
			endforeach;
		endif;
	}

	/* Adds a box to the main column on the Post and Page edit screens */
	function scadenza_add_custom_box() {
		$custom_post_types = $this->va_se_get_post_types();
		foreach ( $custom_post_types as $t ) {
			add_meta_box( 'scadenza_plugin', __( '公開終了日時設定', VACB_TEXTDOMAIN ), array( $this, 'scadenza_' ), $t, 'side', 'high' );
		}
	}

	/* Prints the box content */
	function scadenza_( $post ) {
		global $wp_locale;
		// Use nonce for verification
		wp_nonce_field( VACB_TEXTDOMAIN, 'va-simple-expires-nonce' );

		$scadenza = get_post_meta( $post->ID,'scadenza-date', true );
		$time_adj = current_time('timestamp');
		$anno     = ( ! empty($scadenza) ) ? mysql2date( 'Y', $scadenza, false ) : gmdate( 'Y', $time_adj );
		$mese     = ( ! empty($scadenza) ) ? mysql2date( 'm', $scadenza, false ) : gmdate( 'm', $time_adj );
		$giorno   = ( ! empty($scadenza) ) ? mysql2date( 'd', $scadenza, false ) : gmdate( 'd', $time_adj );
		$ore      = ( ! empty($scadenza) ) ? mysql2date( 'H', $scadenza, false ) : gmdate( 'H', $time_adj );
		$min      = ( ! empty($scadenza) ) ? mysql2date( 'i', $scadenza, false ) : gmdate( 'i', $time_adj );

		$years = "<select  id=\"anno\" name=\"anno\">\n";
		$years_limit = $anno + 11;
		for ( $i = $anno; $i < $years_limit; $i = $i +1 ) {
			$years .= "\t\t\t" . '<option value="' . esc_attr($i) . '"';
			if ( $i == $anno )
				$years .= ' selected="selected"';
				$years .= '>' . esc_html($i) . "</option>\n";
		}
		$years .= '</select>';

		$month = "<select  id=\"mese\" name=\"mese\">\n";
		for ( $i = 1; $i < 13; $i = $i +1 ) {
			$month .= "\t\t\t" . '<option value="' . esc_attr( zeroise( $i, 2 ) ) . '"';
			if ( $i == $mese )
				$month .= ' selected="selected"';
				$month .= '>' . esc_html( $wp_locale->get_month_abbrev( $wp_locale->get_month( $i ) ) ) . "</option>\n";
		}
		$month .= '</select>';

		$days = "<select  id=\"giorno\" name=\"giorno\">\n";
		for ( $i = 0; $i < 32; $i = $i +1 ) {
			$days .= "\t\t\t" . '<option value="' . esc_attr( zeroise($i, 2) ) . '"';
			if ( $i == $giorno )
				$days .= ' selected="selected"';
				$days .= '>' . esc_html( zeroise($i, 2) ) . "</option>\n";
		}
		$days .= '</select>';

		$time_h = "<select  id=\"ore\" name=\"ore\">\n";
		for ( $i = 0; $i < 24; $i = $i +1 ) {
			$time_h .= "\t\t\t" . '<option value="' . esc_attr( str_pad($i, 2, "0", STR_PAD_LEFT) ) . '"';
			if ( $i == $ore )
				$time_h .= ' selected="selected"';
				$time_h .= '>' . esc_html( str_pad($i, 2, "0", STR_PAD_LEFT) ) . "</option>\n";
		}
		$time_h .= '</select>';

		$time_i = "<select  id=\"min\" name=\"min\">\n";
		for ( $i = 0; $i < 60; $i = $i +1 ) {
			$time_i .= "\t\t\t" . '<option value="' . esc_attr( str_pad($i, 2, "0", STR_PAD_LEFT) ) . '"';
			if ( $i == $min )
				$time_i .= ' selected="selected"';
				$time_i .= '>' . esc_html( str_pad($i, 2, "0", STR_PAD_LEFT) ) . "</option>\n";
		}
		$time_i .= '</select>';

		echo'<div id="timestampdiv_scadenza" class="">';
		$the_data = get_post_meta( $post->ID, 'scadenza-enable', true );
		// Checkbox for scheduling this Post / Page, or ignoring
		$items = array( __( '有効にする', VACB_TEXTDOMAIN ), __( '無効にする', VACB_TEXTDOMAIN ) );
		$value = array( 1, 0 );
		$i     = 0;
		foreach( $value as $item ) {
			$checked = ( ( $the_data == $item ) || ( $the_data=='') ) ? ' checked="checked" ' : '';
			echo "<label><input" . $checked . " value='" . $item . "' name='scadenza-enable' id='scadenza-enable' type='radio'>" . $items[$i] . " </label>";
			$i++;
		} // end foreach
		echo "<br>\n<br>\n";
		echo '<div class="">' . $years . $month . $days . '<br>' . $time_h . ' : ' . $time_i . '</div></div>';
		echo "<p>".__( '公開終了日時を入力', VACB_TEXTDOMAIN )."</p>";
	}


	/* When the post is saved, saves our custom data */
	function scadenza_save_postdata( $post_id ) {
		// verify this came from the our screen and with proper authorization,
		// because save_post can be triggered at other times
		if ( isset( $_POST['va-simple-expires-nonce'] ) ) {
			$nonce = esc_attr( $_POST['va-simple-expires-nonce'] );
		} else {
			$nonce = NULL;
		}

		if ( ! wp_verify_nonce( $nonce, VACB_TEXTDOMAIN ) ) {
			return $post_id;
		}

		// verify if this is an auto save routine. If it is our form has not been submitted, so we dont want
		// to do anything
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE )
			return $post_id;

		// Check permissions
		if ( 'page' == $_POST['post_type'] ) {
			if ( ! current_user_can( 'edit_page', $post_id ) ) {
				return $post_id;
			}
		} else {
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return $post_id;
			}
		}

		// OK, we're authenticated: we need to find and save the data
		$mydata = esc_attr( $_POST['anno'] ) . "-" . esc_attr( $_POST['mese'] ) . "-" . esc_attr( zeroise( $_POST['giorno'], 2 ) ) . " " . esc_attr( zeroise( $_POST['ore'], 2 ) ) . ":" . esc_attr( $_POST['min'] ) . ":00";
		($mydata);
		$enabled = esc_attr( $_POST['scadenza-enable'] );

		// Do something with $mydata
		update_post_meta( $post_id,'scadenza-date', $mydata );
		update_post_meta( $post_id, 'scadenza-enable', $enabled );
		return $mydata;
	}
} // class VA_Simple_Expires
new VA_Simple_Expires;
endif;
