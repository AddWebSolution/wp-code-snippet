<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @since           1.0.0
 * @package         WPTOH_Track_Order_History
 * @subpackage  	WPTOH_Track_Order_History/admin
 * @author      	Saurabh Dhariwal <contact@addwebsolution.com>
 * @copyright       2020 Saurabh Dhariwal
 * 
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if( ! class_exists( 'WPTOH_Track_Order_History_Admin' ) ):
	
class WPTOH_Track_Order_History_Admin {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( is_admin() ){

			add_action( 'admin_enqueue_scripts', array( $this, 'wptoh_enqueue_admin_styles') );
			add_action( 'admin_enqueue_scripts', array( $this, 'wptoh_enqueue_admin_scripts') );
			add_action( 'manage_edit-shop_order_columns', array( $this, 'wptoh_custom_shop_order_column'), 10, 2 );
			add_action( 'manage_shop_order_posts_custom_column', array( $this, 'wptoh_custom_orders_list_column_content'), 10, 2 );
			add_action( 'wp_ajax_get_all_order_details', array( $this, 'wptoh_get_all_order_details') );
			add_action( 'wp_ajax_nopriv_get_all_order_details', array( $this, 'wptoh_get_all_order_details') );
		}
	}

	/**
	 * Load Admin Styles.
	 *
	 * Enqueues the required admin styles.
	 *
	 * @since 1.0
	 * @param string $hook Page hook
	 * @return void
	 */
	function wptoh_enqueue_admin_styles( $hook ) {
		
		$css_dir = WPTOH_PLUGIN_URL . 'assets/css/';
	 	wp_enqueue_style('awwptoh-admin', $css_dir . 'wptoh-admin.css', false, "" );
		
	}

	/**
	 * Load Admin Scripts.
	 *
	 * Enqueues the required admin scripts.
	 *
	 * @since 1.0
	 * @param string $hook Page hook
	 * @return void
	 */
	function wptoh_enqueue_admin_scripts( $hook ) {

		$js_dir  = WPTOH_PLUGIN_URL . 'assets/js/';
		wp_enqueue_script( 'awwptoh-admin-js', $js_dir . 'wptoh-admin.js?ver'.rand('0000','9999'), array('jquery'), '', false);
		$admin_ajax_url = array( 'ajax_url' => esc_url( admin_url( 'admin-ajax.php' ) ) );
		wp_localize_script( 'awwptoh-admin-js', 'admin_ajax_call', $admin_ajax_url );

	}

	/**
	 * Add custom column in order table.
	 *
	 * @since 1.0
	 * @return $reordered_columns
	 */
	function wptoh_custom_shop_order_column( $columns )
	{
		$reordered_columns = array();

		// Inserting columns to a specific location
		foreach( $columns as $key => $column){
			$reordered_columns[$key] = $column;
			if( $key ==  'order_total' ){
				// Inserting after "Total" column
				$reordered_columns['customer_history'] = esc_html__( 'Past Orders', 'wc-past-orders' );
			}
		}
		return $reordered_columns;
	}

	
	/**
	 * Add custom fields meta data for each new column
	 *
	 * @since 1.0
	 * @return void
	 */

	function wptoh_custom_orders_list_column_content( $column, $post_id )
	{
		switch ( $column )
		{
			case 'customer_history' :
			$order = wc_get_order( $post_id );
			$order_row_uid = $order->get_user_id();

			if ( $order_row_uid == 0 ) { ?>
				<div class="container mainDivContainer"><button type="button" class="btn btn-primary btn-count"><?php esc_html_e( 'Guest', 'wc-past-orders' );?></button></div>
				<?php break;
			} else {
				$customer_orders = get_posts( array(
					'numberposts' => -1,
					'meta_key'    => '_customer_user',
					'meta_value'  => $order_row_uid,
					'post_type'   => wc_get_order_types(),
					'post_status' => array_keys( wc_get_order_statuses() ),
				) );

				$count = 0 ;
				foreach ( $customer_orders as $key => $value ) {
					if( $value->ID != $post_id ){
						$count = $count + 1;
					}
				}

				//includes template file for order detail content
				include WPTOH_PLUGIN_DIR .'templates/wptoh-order-details-modal.php';
				
				
			}
			break;
		}
	}
	
	/**
	 * Get response with the user order details 
	 *
	 * @since 1.0
	 * @return array
	 */
	function wptoh_get_all_order_details(){
		$user_id = sanitize_text_field($_POST['user_id']);
                $current_user_order = sanitize_text_field($_POST['cur_ordrid']);
		$current_user =  isset( $user_id ) ? esc_attr( $user_id ) : '';
		$current_order = isset( $current_user_order ) ? esc_attr( $current_user_order ) : '';
	
		$customer_orders = get_posts( array(
			'numberposts' => -1,
			'meta_key'    => '_customer_user',
			'meta_value'  => $current_user,
			'post_type'   => wc_get_order_types(),
			'post_status' => array_keys( wc_get_order_statuses() ),
		) );
		$arrayOrder = array();	
		$html = '<div id="myModal_'.$current_order.'" class="wptoh-modal modal model-order-close"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h4>'.esc_html__( 'Order history', 'wc-past-orders' ).'</h4><span class="close" id="closeMyModal">&times;</span></div><div class="modal-body"><div class="list-type1"><ul>';
		foreach ( $customer_orders as $key => $value ) {
			if( $value->ID != $current_order ) {
				$post_edit_url = get_edit_post_link( $value->ID );
				array_push( $arrayOrder,array( "orderid" => $value->ID, "guid" => $value->guid ) );
				$html.='<li>';
				$html.='<a href="'.esc_url( $post_edit_url ).'" target="_blank" class="btn btn-outline-primary">'.esc_html__( 'Order', 'wc-past-orders' ).' #'.esc_attr( $value->ID ).'</a>';
				$html.='</li>';
			}
		}
		$html.= '</ul></div></div></div></div></div>';
		wp_send_json_success( $html );
	}


}

endif;