<?php
/*
Plugin Name: YoCommerce
Plugin URI: http://google.com
Description: Simple plugin for create internet-shop on your wordpress blog
Version: Номер версии плагина, например: 1.0
Author: Aleksandr
Author URI: http://google.com
*/
 
function yocommerce_install() {  // install plugin
  global $wpdb;
   
  // create table "orders"
 $table = $wpdb->prefix . "plugin_yocommerce_orders";
  if($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {	
	$sql = "CREATE TABLE `" . $table . "` (
	  `order_id` int(9) NOT NULL AUTO_INCREMENT,
	  `order_ip_address` VARCHAR(15) NOT NULL,
      `order_post_id` int(9),
	  `order_date` date,
	  UNIQUE KEY `id` (order_id)
	) DEFAULT CHARACTER SET utf8 COLLATE utf8_bin;";
	  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	  dbDelta($sql);
 } 
}
register_activation_hook( __FILE__,'yocommerce_install');

function yocommerce_uninstall() { // uninstall plugin
 
 global $wpdb;
 $table = $wpdb->prefix . "plugin_yocommerce_orders";	
 $wpdb->query("DROP TABLE IF EXISTS $table");
}
register_deactivation_hook( __FILE__,'yocommerce_uninstall');

function yocommerce_show_buy_button($content) {
	
	global $post;
	
	$goods = get_post_meta( $post->ID, 'product_cost',true );
	
	if (is_single($post->ID) && isset($goods['cost'])) {
	    $content .= 'Cost is ' . $goods['cost'] . ' $ <br>'; 
        $content .= "<b>BUY</b>";
	}
	
	
	
    return $content;  
}
add_action('the_content','yocommerce_show_buy_button');


// admin page

function yocommerce_main_page() { // admin page
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	echo '<div class="wrap">';
	echo '<h2>Yo Commerce</h2>';
	    
    // get list posts
    global $wpdb;
   
    $posts = get_posts();
    foreach ( $posts as $post ) {
       var_dump($post->post_name);
    }
     
	echo '</div>';
}

function yocommerce_my_plugin_menu() {
add_menu_page('YoCommerce', 'YoCommerce', 'manage_options', 'YoCommerce', 'yocommerce_main_page' );
}

add_action('admin_menu', 'yocommerce_my_plugin_menu');

/* Adds a box to the main column on the Post and Page edit screens */
function yocommerce_dynamic_add_custom_box() {
    add_meta_box(
        'yocommerce_sectionid',
        __( 'YoCommerce - Product\'s cost', 'myplugin_textdomain' ),
        'yocommerce_dynamic_inner_custom_box',
        'post');
}
add_action( 'add_meta_boxes', 'yocommerce_dynamic_add_custom_box' );


/* Prints the box content */
function yocommerce_dynamic_inner_custom_box() {
    global $post;
    // Use nonce for verification
    wp_nonce_field( plugin_basename( __FILE__ ), 'dynamicMeta_noncename' );
    ?>
       <div id="meta_inner">
		<?php
   
	   //get the saved meta as an arry
   
	   $product_cost = get_post_meta($post->ID,'product_cost',true);
	   if (!isset($product_cost['cost'])) {
			   	   $product_cost['cost'] = 0;
	   }
       echo '<p  style="border-bottom:1px solid #f1f1f1">  
          Cost: <input type="text"  name="product_cost[cost]" value="' . (int) $product_cost['cost']  . '" placeholder="Item Name..."  style="margin:0px 15px"/> $
          </p>';
	
}


/* When the post is saved, saves our custom data */
function yocommerce_dynamic_save_postdata( $post_id ) {
    // verify if this is an auto save routine. 
    // If it is our form has not been submitted, so we dont want to do anything
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
        return;

    // verify this came from the our screen and with proper authorization,
    // because save_post can be triggered at other times
    if ( !isset( $_POST['dynamicMeta_noncename'] ) )
        return;

    if ( !wp_verify_nonce( $_POST['dynamicMeta_noncename'], plugin_basename( __FILE__ ) ) )
        return;

    // OK, we're authenticated: we need to find and save the data
    $product_cost = $_POST['product_cost'];
	$product_cost['cost'] = (int) $product_cost['cost'];

    update_post_meta($post_id,'product_cost',$product_cost);
}
/* Do something with the data entered */
add_action( 'save_post', 'yocommerce_dynamic_save_postdata' );

?>