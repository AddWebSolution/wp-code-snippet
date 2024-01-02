<?php 
/**
 * Timer Popup Class
 * @package   Post Timer
 * @author    AddWeb Solution
 * @license   GPL-2.0+
 * @link      http://www.addwebsolution.com
 * @copyright 2016 AddWeb Solution
 **/
class addweb_pt_post_timer {

	/**
	 * Unique identifier for plugin.
	 *
	 * 
	 * @var string
	 */
	protected $addweb_pt_plugin_slug = 'post_timer';

	/**
	 * Instance of this class.
	 *
	 * 
	 * @var object
	 */
	protected static $addweb_pt_instance = null;

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 * 
	 *
	 * @var string
	 */
	const ADDWEB_PT_VERSION = '1.0';

	/**
	 * Stores popup active status
	 *
	 * 
	 * @var string
	 */
	protected $addweb_pt_popup_active;

	/**
	 * Stores popup Colour
	 *
	 * 
	 * @var string
	 */
	protected $addweb_pt_popup_color;

	/**
	 * Stores popup place
	 *
	 * 
	 * @var string
	 */
	protected $addweb_pt_popup_place;

	/**
	 * Stores popup top margin in percentage
	 *
	 * 
	 * @var string
	 */
	protected $addweb_pt_popup_top_margin;

	/**
	 * Stores All posts that activated for popup
	 *
	 * 
	 * @var array
	 */
	protected $addweb_pt_popup_posts;

	/**
	 * Stores current url page
	 *
	 * 
	 * @var string
	 */
	protected $addweb_pt_url_page;

	/**
	 * Stores current page query string
	 *
	 * 
	 * @var string
	 */
	protected $addweb_pt_query_string;

    /**
     * Constructor for the class.
     */
	public function __construct() {
		// Get current URL and query parameters
		$addweb_pt_url = $_SERVER['REQUEST_URI']; 
		$addweb_pt_querystr = $_GET['post_type'];
		$addweb_pt_actionqry = $_GET['action'];
		$addweb_pt_postid = $_GET['post'];

		// Initialize properties with plugin options and request data
		$this->addweb_pt_popup_active = get_option( 'addweb_pt_popup_active' );
		$this->addweb_pt_popup_color  = get_option( 'addweb_pt_popup_color' );
		$this->addweb_pt_popup_place = get_option( 'addweb_pt_popup_place' );
		$this->addweb_pt_popup_top_margin = get_option( 'addweb_pt_popup_top_margin' );
		$this->addweb_pt_popup_posts = get_option('addweb_pt_popup_posts');

		// Extract page and query parameters
		$addweb_pt_url = substr($addweb_pt_url, strrpos($addweb_pt_url, '/') + 1);
		list($addweb_pt_url,$addweb_pt_param) = explode("?", $addweb_pt_url);
		$this->addweb_pt_url_page = $addweb_pt_url;
		$this->addweb_pt_query_string = $addweb_pt_querystr;
		
		// Additional request data
		$this->addweb_pt_action_query = $addweb_pt_actionqry;
		$this->addweb_post_type = get_post_type($addweb_pt_postid);

		// Setup actions and hooks if in the admin area
		if ( is_admin() ) {
			// Add the settings page and menu item.
			add_action( 'admin_menu', array( $this, 'addweb_pt_plugin_admin_menu' ) );
			// Add an action link pointing to the settings page.
			$plugin_basename = plugin_basename( plugin_dir_path( __FILE__ ) . $this->addweb_pt_plugin_slug . '.php' );
			add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'addweb_pt_add_action_links' ) );
			
			add_action( 'admin_enqueue_scripts', array( $this, 'addweb_pt_enqueue_admin_styles' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'addweb_pt_enqueue_admin_scripts' ) );
			add_action( 'admin_init', array( $this, 'addweb_pt_load_timer_popup' ) );
		} 
	}

	/**
     * Load the timer popup.
     */
	public function addweb_pt_load_timer_popup () {	
		$show_timer_popup = false;

		// Enqueue styles for admin area
		add_action( 'admin_enqueue_scripts', array( $this, 'addweb_pt_enqueue_styles' ) );
		if($this->addweb_pt_popup_active) {
			//Show popup when create new custom post.
			if($this->addweb_pt_url_page == 'post-new.php' && in_array($this->addweb_pt_query_string , $this->addweb_pt_popup_posts)){
				$show_timer_popup = true;	
			}

			//Show popup when edit a custom post.
			if($this->addweb_pt_url_page == 'post.php' && $this->addweb_pt_action_query == 'edit' && in_array($this->addweb_post_type , $this->addweb_pt_popup_posts)){
				$show_timer_popup = true;	
			}

			//Show popup when create a new or edit a simple post.
			if(empty($this->addweb_pt_query_string) && $this->addweb_pt_url_page == 'post-new.php' && in_array('post' , $this->addweb_pt_popup_posts) || $this->addweb_pt_url_page == 'post.php' && $this->addweb_pt_action_query == 'edit' && in_array('post' , $this->addweb_pt_popup_posts) && empty($this->addweb_post_type)){
				$show_timer_popup = true;	
			} 
			if($show_timer_popup) {
				// Enqueue scripts for admin area
				add_action( 'admin_enqueue_scripts', array( $this, 'addweb_pt_enqueue_scripts' ) );
				add_action( 'admin_head', array( $this, 'addweb_pt_head_styles' ) );
				add_action( 'admin_head', array( $this, 'addweb_pt_timer_clock' ) );
				add_filter( 'admin_footer', array( $this, 'addweb_pt_get_timer_popup' ) );
				add_action( 'admin_footer', array( $this, 'addweb_pt_footer_scripts' ) );
			}
		}
	}

	/**
     * Get a singleton instance of the class.
     *
     * @return object
    */
	public static function addweb_pt_get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null == self::$addweb_pt_instance ) {
			self::$addweb_pt_instance = new self;
		}
		return self::$addweb_pt_instance;
	}

	/**
	 * Register the settings menu for this plugin into the WordPress Settings menu.
	*/
	public function addweb_pt_plugin_admin_menu() {
		add_options_page( __( 'Post Timer Settings', 'addweb-pt-timer-popup' ), __( 'Post Timer', 'addweb-pt-timer-popup' ), 'manage_options', $this->addweb_pt_plugin_slug, array( $this, 'addweb_pt_timer_popup_options' ) );
	}

	/**
     * Enqueue scripts for admin styles.
    */
	public function addweb_pt_enqueue_admin_scripts() {
		$screen = get_current_screen();
		if ( 'settings_page_'.$this->addweb_pt_plugin_slug == $screen->id ) {			
			wp_enqueue_script( $this->addweb_pt_plugin_slug . '-admin-script', plugins_url( 'js/admin.js', __FILE__ ), array( 'jquery', 'wp-color-picker' ), addweb_pt_post_timer::ADDWEB_PT_VERSION );
			wp_enqueue_media();        	
		}
	}

	/**
     * Enqueue styles for admin styles.
    */
	public function addweb_pt_enqueue_admin_styles() {
		$screen = get_current_screen();
		if ( 'settings_page_'.$this->addweb_pt_plugin_slug == $screen->id ) {
			wp_enqueue_style( 'wp-color-picker' );
		}
	}

	/**
     * Add custom action links to the plugin settings on the plugin page.
     *
     * @param array $links Existing links.
     *
     * @return array
    */
	public function addweb_pt_add_action_links( $links ) {
		return array_merge(
			array(
				'settings' => '<a href="' . admin_url( 'options-general.php?page=' . $this->addweb_pt_plugin_slug ) . '">' . __( 'Settings', $this->addweb_pt_plugin_slug ) . '</a>'
			),
			$links
		);	
	}


	public function addweb_pt_timer_popup_options() {
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		
		if ( ! empty( $_POST ) && check_admin_referer( 'addweb_pt_post_timer', 'save_timer_popup' ) ) {
			//add or update timer popup active stats
			if ( $this->addweb_pt_popup_active !== false ) {
				update_option( 'addweb_pt_popup_active', $_POST['addweb_pt_popup_active'] );
			} 
			else {
				add_option( 'addweb_pt_popup_active', $_POST['addweb_pt_popup_active'], null, 'no' );
			}

			//add or update timer popup header color
			if ( $this->addweb_pt_popup_color !== false ) {
				update_option( 'addweb_pt_popup_color', $_POST['addweb_pt_popup_color'] );
			} 
			else {
				add_option( 'addweb_pt_popup_color', $_POST['addweb_pt_popup_color'], null, 'no' );
			}

			//add or update timer popup place
			if ( $this->addweb_pt_popup_place !== false ) {
				update_option( 'addweb_pt_popup_place', $_POST['addweb_pt_popup_place'] );
			} 
			else {
				add_option( 'addweb_pt_popup_place', $_POST['addweb_pt_popup_place'], null, 'no' );
			}

			//add or update timer popup Top Margin when position is left or right included since 1.1
			if ( $this->addweb_pt_popup_top_margin !== false ) {
				update_option( 'addweb_pt_popup_top_margin', $_POST['addweb_pt_popup_top_margin'] );
			} 
			else {
				add_option( 'addweb_pt_popup_top_margin', $_POST['addweb_pt_popup_top_margin'], null, 'no' );
			}

			if ( $this->addweb_pt_popup_posts !== false ) {
				update_option( 'addweb_pt_popup_posts', $_POST['addweb_pt_popup_posts'] );
			} 
			else {
				add_option( 'addweb_pt_popup_posts', $_POST['addweb_pt_popup_posts'], null, 'no' );
			}

			wp_redirect( admin_url( 'options-general.php?page='.$_GET['page'].'&updated=1' ) );
		}
		// Display settings for timer popup
		?>
		<div class="pt-wrap" style="max-width: 1000px; width:100%;">
				<div class="fa-plugin-setting">
				<ul>
					<li><a href = "#pt-setting">Settings</a></li>
	                <li><a href = "#pt-about">About Us</a></li>
				</ul>
				<div id="pt-setting">
					<h2><?php _e( 'Timer Popup Settings', 'addweb-pt-timer-popup' );?></h2>
					<form method="post" action="<?php echo esc_url( admin_url( 'options-general.php?page='.$_GET['page'].'&noheader=true' ) ); ?>" enctype="multipart/form-data"><?php wp_nonce_field( 'addweb_pt_post_timer', 'save_timer_popup' ); ?>
                        <div class="timer_popup_form">
							<table class="form-table" width="100%">
								<tr>
									<th scope="row"></th>
									<td>
										<input type="checkbox" name="addweb_pt_popup_active" id="addweb_pt_popup_active" value="1" <?php if($this->addweb_pt_popup_active)  echo 'checked="checked"'; else '';?>>&nbsp;<label for="addweb_pt_popup_active"><strong><?php _e( 'Enable', 'addweb-pt-timer-popup' );?></strong></label>
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="addweb_pt_popup_color"><?php _e( 'Popup Color', 'addweb-pt-timer-popup' );?></label></th>
									<td><input type="text" name="addweb_pt_popup_color" id="popup_color" maxlength="255" size="25" value="<?php echo $this->addweb_pt_popup_color; ?>"></td>
								</tr>
								<tr>
									<th scope="row"><label for="addweb_pt_popup_place"><?php _e( 'Popup Place', 'addweb-pt-timer-popup' );?></label></th>
									<td><select name="addweb_pt_popup_place" id="addweb_pt_popup_place">
									<?php foreach ( $this->addweb_pt_get_popup_place() as $key => $value ): ?>
									    <option value="<?php esc_attr_e( $key ); ?>" <?php esc_attr_e( $key == $this->addweb_pt_popup_place ? ' selected="selected"' : '' ); ?>><?php esc_attr_e( $value ); ?></option>
									<?php endforeach;?>
									</select></td>
								</tr>
								<tr>
									<th scope="row"><label for="addweb_pt_popup_top_margin"><?php _e( 'Popup Top Margin', 'addweb-pt-timer-popup' );?></label></th>
									<td><input type="number" name="addweb_pt_popup_top_margin" id="addweb_pt_popup_top_margin" maxlength="255" size="25" value="<?php echo $this->addweb_pt_popup_top_margin; ?>">%<br>
										<small>Top margin is only included if popup place Left or Right is selected. Please enter numeric value.</small>
									</td>
								</tr>
								<tr>
									<th scope="row" colspan="2">Choose Where To Show Popup</th>
								</tr>
								<?php 
									foreach ( get_post_types( array(), 'objects' ) as $addweb_pt_post_type ) { 
										$addweb_pt_post_name = $addweb_pt_post_type->name;
										$addweb_pt_post_remove = array('attachment','revision','nav_menu_item','ml-slider','oembed_cache');
										if(in_array($addweb_pt_post_name, $addweb_pt_post_remove)){ echo '';} else{
											?><tr>
												<th scope="row"></th>
												<td><input type="checkbox" name="addweb_pt_popup_posts[]" id="<?php echo esc_attr( $addweb_pt_post_type->name ); ?>" value="<?php echo esc_attr( $addweb_pt_post_type->name ); ?>" <?php if(in_array($addweb_pt_post_type->name, $this->addweb_pt_popup_posts))  echo 'checked="checked"'; else '';?> />
												 	<label for="<?php echo esc_attr( $addweb_pt_post_type->name ); ?>">
														<strong><?php echo esc_html( $addweb_pt_post_type->label ); ?></strong>
													</label>
												</td>
											</tr><?php 
										} 
									}
								?>
							</table>
							<p class="submit">
								<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes' ) ?>" />
							</p>
						</div>
					</form>
				</div>
				<div id="pt-about">
					<div style="margin:0 auto;width:54%;">
						<a href="http://www.addwebsolution.com" style="outline: hidden;" target="_blank"><img src="<?php echo plugins_url( '/images/addweb-logo.png', __FILE__);?>" alt="AddwebSolution" height=60px ></a>
					</div><?php
					$arrAddwebPlugins = array(
			      'woo-cart-customizer' => 'Woo Cart Customizer',
			      'widget-social-share' => 'WSS: Widget Social Share',
			      //'wp-all-in-one-social' => 'WP All In One Social',
			      //'football-match-tracker' => 'Football Match Tracker',
			      'aws-cookies-popup' => 'AWS Cookies Popup'
    			);?>
			    <div class="advertise">
			    <div class="ad-heading">Visit Our Other Plugins:</div>
			    <div class="ad-content"><?php
				    foreach($arrAddwebPlugins as $slug=>$name) {?>
				        <div class="ad-detail">
				          <a href="https://wordpress.org/plugins/<?php echo $slug;?>" target="_blank"><img src="<?php echo plugins_url( 'images/', __FILE__).$slug;?>.svg"></a>..
				          <a href="https://wordpress.org/plugins/<?php echo $slug;?>" class="ad-link" target="_blank"><?php echo $name;?></a>
				        </div><?php
				    } ?></div>
    			</div>
				</div>
			</div><?php
			$plugin_basename = plugin_basename( plugin_dir_path( __FILE__ ) );
			?></div><?php
	}

	/**
	 * Get Popup Place Options
	*/
	public function addweb_pt_get_popup_place() {
		return array(
				'right-bottom' => 'Right Bottom',
				'left-bottom' => 'Left Bottom',
				'top-left' => 'Top Left',
				'top-right' => 'Top Right',
				'right' => 'Right',
				'left' => 'Left',
			);
	}

	/**
	 * Register and enqueue public-facing style sheet.
	 */
	public function addweb_pt_enqueue_styles() {
		wp_enqueue_style( $this->addweb_pt_plugin_slug . '-style', plugins_url( 'css/post-timer-popup.css', __FILE__ ), array(), self::ADDWEB_PT_VERSION );
		wp_enqueue_script( 'jquery-ui-tabs' );
    	wp_enqueue_style( $this->addweb_pt_plugin_slug . 'jquery-ui-css', plugins_url( 'css/jquery-ui.min.css', __FILE__ ), array(), self::ADDWEB_PT_VERSION );
	}

	/**
	 * Register and enqueues public-facing JavaScript files.
	 */
	public function addweb_pt_enqueue_scripts() {
		wp_enqueue_script( $this->addweb_pt_plugin_slug . '-modernizr-script', plugins_url( 'js/modernizr.custom.js', __FILE__ ), array(), self::ADDWEB_PT_VERSION );		
	}

	public function addweb_pt_get_timer_popup(){
		// Retrieve popup settings from options
		$this->addweb_pt_popup_active = get_option( 'addweb_pt_popup_active' );
		$this->addweb_pt_popup_place  = get_option( 'addweb_pt_popup_place' );
		$this->addweb_pt_popup_color = get_option( 'addweb_pt_popup_color' );
		$this->addweb_pt_popup_top_margin = get_option( 'addweb_pt_popup_top_margin' );
		
		// HTML markup for the timer popup
		$addweb_pt_popup_html  = '<div class="addweb-pt-timer-popup" onclick="start();">';
		$addweb_pt_popup_html .= '<div class="popup-wrap">';
		if($this->addweb_pt_popup_place!='top-left' && $this->addweb_pt_popup_place!='top-right') {
			$addweb_pt_popup_html .= '<div class="popup-header">';
			$addweb_pt_popup_html .= '<span class="popup-title" id="timer-clock">';			
			$addweb_pt_popup_html .= '</span>';
			$addweb_pt_popup_html .= '</div>';
		}
		
		if($this->addweb_pt_popup_place == 'top-left' || $this->addweb_pt_popup_place == 'top-right') {
			$addweb_pt_popup_html .= '<div class="popup-header">';
			$addweb_pt_popup_html .= '<span class="popup-title" id="timer-clock">';
			$addweb_pt_popup_html .= '</span>';
			$addweb_pt_popup_html .= '</div>';
		}
		$addweb_pt_popup_html .= '</div>';
		$addweb_pt_popup_html .= '</div>';
		echo $addweb_pt_popup_html;

		// Auto-trigger the popup when creating a new post or editing an existing one
		if($this->addweb_pt_url_page == 'post-new.php' || ($this->addweb_pt_url_page == 'post.php' && $this->addweb_pt_action_query == 'edit')){ ?>
			<script>
				jQuery(document).ready(function(){
					jQuery('.addweb-pt-timer-popup').click();
				});
			</script><?php 
		}
	}

	/**
	 * Add styles for popup header color
	 */
	public function addweb_pt_head_styles() {
		// Retrieve popup settings for color, place, and top margin from options
		$this->addweb_pt_popup_color = get_option( 'addweb_pt_popup_color' );
		$this->addweb_pt_popup_place = get_option( 'addweb_pt_popup_place' );
		$this->addweb_pt_popup_top_margin = get_option( 'addweb_pt_popup_top_margin' );
		?><style type="text/css">
			.addweb-pt-timer-popup .popup-header
			{
			<?php
			if( $this->addweb_pt_popup_color !='' ) {
			?>	
				background-color : <?php echo $this->addweb_pt_popup_color; ?>;		
			<?php
			} else {
			?>
				background-color : #2C5A85;		
			<?php
			}
			?>
		}
		
		<?php
		if($this->addweb_pt_popup_place == 'left' || $this->addweb_pt_popup_place == 'right')
		{
		?>
			.addweb-pt-timer-popup-right, .addweb-pt-timer-popup-left
			{
				<?php
				if( $this->addweb_pt_popup_top_margin !='' ) {
				?>	
					top : <?php echo $this->addweb_pt_popup_top_margin; ?>%;		
				<?php
				} else {
				?>
					top : 25%;		
				<?php
				}
				?>
			}

		<?php } ?>
		</style><?php
	}

	/**
	 * Add Javascript for popup place
	 */
	public function addweb_pt_footer_scripts() {
		if( $this->addweb_pt_popup_place == 'right-bottom' ) {
		?><script type="text/javascript">
				jQuery( document ).ready(function() {	
					jQuery( ".addweb-pt-timer-popup" ).addClass('right-bottom');
					var contheight = jQuery( ".popup-content" ).outerHeight()+2;      	
			      	jQuery( ".addweb-pt-timer-popup" ).css( "bottom", "-"+contheight+"px" );
			      	jQuery( ".addweb-pt-timer-popup" ).css( "visibility", "visible" );
			      	jQuery('.addweb-pt-timer-popup').addClass("open_timer_popup");
			      	jQuery('.addweb-pt-timer-popup').addClass("popup-content-bounce-in-up");
				});
			</script><?php
		} 
		elseif( $this->addweb_pt_popup_place == 'left-bottom' ) {
			?><script type="text/javascript">
				jQuery( document ).ready(function() {	
					jQuery( ".addweb-pt-timer-popup" ).addClass('left-bottom');
					var contheight = jQuery( ".popup-content" ).outerHeight()+2;      	
			      	jQuery( ".addweb-pt-timer-popup" ).css( "bottom", "-"+contheight+"px" );
			      	jQuery( ".addweb-pt-timer-popup" ).css( "visibility", "visible" );
			      	jQuery('.addweb-pt-timer-popup').addClass("open_timer_popup");
			      	jQuery('.addweb-pt-timer-popup').addClass("popup-content-bounce-in-up");   
				});
			</script><?php
		} 
		elseif( $this->addweb_pt_popup_place == 'left' ) {
			?><script type="text/javascript">
				jQuery( document ).ready(function() {	
					if (/*@cc_on!@*/true) {						
						var ieclass = 'ie' + document.documentMode; 
						jQuery( ".popup-wrap" ).addClass(ieclass);
					} 
					jQuery( ".addweb-pt-timer-popup" ).addClass('addweb-pt-timer-popup-left');
					var contwidth = jQuery( ".popup-content" ).outerWidth()+2;      	
					jQuery( ".addweb-pt-timer-popup" ).css( "left", "-"+contwidth+"px" );
					jQuery( ".addweb-pt-timer-popup" ).css( "visibility", "visible" );
					jQuery('.addweb-pt-timer-popup').addClass("open_timer_popup_left");
					jQuery('.addweb-pt-timer-popup').addClass("popup-content-bounce-in-left");
				});
			</script><?php
		} 
		elseif( $this->addweb_pt_popup_place == 'right' ) {
			?><script type="text/javascript">
				jQuery( document ).ready(function() {
					if (/*@cc_on!@*/true) {
						var ieclass = 'ie' + document.documentMode; 
						jQuery( ".popup-wrap" ).addClass(ieclass);
					} 
					jQuery( ".addweb-pt-timer-popup" ).addClass('addweb-pt-timer-popup-right');
					var contwidth = jQuery( ".popup-content" ).outerWidth()+2;
					jQuery( ".addweb-pt-timer-popup" ).css( "right", "-"+contwidth+"px" );
					jQuery( ".addweb-pt-timer-popup" ).css( "visibility", "visible" );
					jQuery('.addweb-pt-timer-popup').addClass("open_timer_popup_right");
					jQuery('.addweb-pt-timer-popup').addClass("popup-content-bounce-in-right");
				});
			</script><?php
		} 
		elseif( $this->addweb_pt_popup_place == 'top-left' ) {
			?><script type="text/javascript">
				jQuery( document ).ready(function() {	
					jQuery( ".addweb-pt-timer-popup" ).addClass('top-left');
					var contheight = jQuery( ".popup-content" ).outerHeight()+2;      	
					jQuery( ".addweb-pt-timer-popup" ).css( "top", "-"+contheight+"px" );
					jQuery( ".addweb-pt-timer-popup" ).css( "visibility", "visible" );
					jQuery('.addweb-pt-timer-popup').addClass("open_timer_popup_top");
					jQuery('.addweb-pt-timer-popup').addClass("popup-content-bounce-in-down");
				});
			</script><?php
		} 
		elseif( $this->addweb_pt_popup_place == 'top-right' ) {
			?><script type="text/javascript">
				jQuery( document ).ready(function() {	
					jQuery( ".addweb-pt-timer-popup" ).addClass('top-right');
					var contheight = jQuery( ".popup-content" ).outerHeight()+2;      	
					jQuery( ".addweb-pt-timer-popup" ).css( "top", "-"+contheight+"px" );
					jQuery( ".addweb-pt-timer-popup" ).css( "visibility", "visible" );
					jQuery('.addweb-pt-timer-popup').addClass("open_timer_popup_top");
					jQuery('.addweb-pt-timer-popup').addClass("popup-content-bounce-in-down");  
				});
			</script><?php
		} 
		else {
			?><script type="text/javascript">
				jQuery( document ).ready(function() {	
					jQuery( ".addweb-pt-timer-popup" ).addClass('right-bottom');
					var contheight = jQuery( ".popup-content" ).outerHeight()+2;      	
					jQuery( ".addweb-pt-timer-popup" ).css( "bottom", "-"+contheight+"px" );
					jQuery( ".addweb-pt-timer-popup" ).css( "visibility", "visible" );
					jQuery('.addweb-pt-timer-popup').addClass("open_timer_popup");
					jQuery('.addweb-pt-timer-popup').addClass("popup-content-bounce-in-up"); 
				});
			</script><?php
		}
	}
	//Timer Script
	public function addweb_pt_timer_clock(){
		?><script>
					var	clsStopwatch = function() {
					// Private vars
					var	startAt	= 0;	// Time of last start / resume. (0 if not running)
					var	lapTime	= 0;	// Time on the clock when last stopped in milliseconds

					var	now	= function() {
							return (new Date()).getTime(); 
						}; 
			 
					// Public methods
					// Start or resume
					this.start = function() {
							startAt	= startAt ? startAt : now();
						};

					// Stop or pause
					this.stop = function() {
							// If running, update elapsed time otherwise keep it
							lapTime	= startAt ? lapTime + now() - startAt : lapTime;
							startAt	= 0; // Paused
						};

					// Reset
					this.reset = function() {
							lapTime = startAt = 0;
						};

					// Duration
					this.time = function() {
							return lapTime + (startAt ? now() - startAt : 0); 
						};
				};

			var x = new clsStopwatch();
			var $time;
			var clocktimer;

			function pad(num, size) {
				var s = "0000" + num;
				return s.substr(s.length - size);
			}

			function formatTime(time) {
				var h = m = s = ms = 0;
				var newTime = '';

				h = Math.floor( time / (60 * 60 * 1000) );
				time = time % (60 * 60 * 1000);
				m = Math.floor( time / (60 * 1000) );
				time = time % (60 * 1000);
				s = Math.floor( time / 1000 );
				ms = time % 1000;

				newTime = pad(h, 2) + ':' + pad(m, 2) + ':' + pad(s, 2);
				return newTime;
			}

			jQuery(document).ready(function show() {
				$time = document.getElementById('timer-clock');
				update();
			});


			function update() {
				$time.innerHTML = formatTime(x.time());
			}

			function start() {
				clocktimer = setInterval("update()", 1);
				x.start();
			}

			function stop() {
				x.stop();
				clearInterval(clocktimer);
			}

			function reset() {
				stop();
				x.reset();
				update();
			}
		</script><?php
	}
}
?>