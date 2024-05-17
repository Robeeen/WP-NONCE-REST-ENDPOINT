<?php
/*
 * Plugin Name: Rest Crud Plugin
 * Description: This plugin provides functionality for adding and displaying data in a custom table. 
 * It also includes search functionality & custom endpoint to insert and OOP Try
 * Version: 1.0.0
 * Requires at least: 5.0
 * Requires PHP: 7.0
 * Author: Shams Khan
 * Author URI: https://shamskhan.com
 * License: GPL-2.0+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:
 * Text Domain: custom-table
 * Domain Path: /languages/asset/
 */

// Exit if accessed directly
 if (!defined('ABSPATH')){
    exit;
  }

class restPlugin{
    public function __construct() {
        //Create assets
        add_action('wp_enqueue_scripts', array($this, 'load_custom_css_js'));
        //Create Shortcodes for front-ends
        add_shortcode('my_form', array($this, 'my_shortcode_form'));
        add_shortcode('my_list', array($this, 'my_shortcode_list'));
        //Create and load script ajax-jquery for 
        add_action('wp_footer', array($this, 'load_scripts'));
        //Create and Register new Rest Route/endpoint/
        add_action('rest_api_init', array($this, 'registering_routes'));
    }
    
    public function load_custom_css_js() {
        // Add custom CSS file
        wp_enqueue_style('custom-style', plugin_dir_url(__FILE__) . 'css/custom-style.css');

        // Add custom JavaScript file
        wp_enqueue_script('custom-script', plugin_dir_url(__FILE__) . 'js/custom-script.js', array('jquery'), '1.0', true);

        // Add Bootstrap cdn
        wp_enqueue_style('bootstrap4', 'https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css');
    }

        // Callback function for shortcode1
    public function my_shortcode_form(){
       // $data = insert_data_to_my_table();?>
    <div class="heading">
    <h3>Fill the form to create user</h3>
    <p>Implementation of CSRF using nonce & ajax to use endpoints</p>
    </div>   
   
       <div class="simple-contact-form">
            <form id="create-user">
                <div class="form-group">
                    <label for="fullname">Full Name</label>
                    <input type="text" class="form-control" id="fullname" aria-describedby="namelHelp" placeholder="Enter name">
                </div>
                <div class="form-group">
                    <label for="emaill">Email address</label>
                    <input type="email" class="form-control" id="email" aria-describedby="emailHelp" placeholder="Enter email">
                </div>          
                
                <button type="submit" class="btn btn-primary">Submit</button>
        </form>
    </div>

    <?php }   

// create js to handle the nonce, endpoint, form submission by jquery
public function load_scripts(){?>
    <script>
        var nonce = '<?php echo wp_create_nonce('wp-rest');?>';
       // console.log(nonce);
        (function($){
            $('#create-user').submit(function(e){
                e.preventDefault();
                var form = $(this).serialize();
                //console.log(form);

                $.ajax({
                    method: 'post',
                    url: '<?php echo get_rest_url(null, 'test-mine/v1/create-user/');?>',
                    headers: { 'X-WP-Nonce': nonce },
                    data: form
                })               
            });
        })(jQuery)
    </script>
<?php }
    //endpoint to display data from the table
 public function registering_routes(){
    register_rest_route(
        'form_submission_route/v1',
        '/form-submission',
        array(
            'method' => 'GET',
            'callback' => 'form_sub_callback',
            'permission_callback' => '__return_true'
        )
    );

    //endpoint to insert data to the table
    register_rest_route(
        'test-mine/v1',
        '/create-user',
        array(
            'methods' => WP_REST_SERVER::CREATABLE,
            //'methods' => POST,
            'permission_callback' => '__return_true',
            'callback' => array($this, 'form_get_callback'),
           //'permission_callback' => 'wp_check_permission'
            
        )
    );
}

//callback function for displaying data
public function form_sub_callback(){
    global $wpdb;
    $table_name = $wpdb->prefix . 'form_submission';
    $results = $wpdb->get_results( "SELECT * FROM $table_name" );
    return $results;
}

//add security
public function wp_check_permission(){
	return current_user_can('edit_posts');
}

//callback function to insert data to table
public function form_get_callback($data){


	$headers = $data->get_headers();
	//$params = $data->get_params();
	$nonce = $headers['x_wp_nonce'];
	
	if(!wp_verify_nonce($nonce, 'wp_rest')){
		return new WP_REST_response('Message not sent', 422);
	}

    echo "This endpoint is working nicely";
    global $wpdb;
    $table_name = $wpdb->prefix . 'form_submission';

    $row = $wpdb->insert(
        $table_name,
        array(
            'name' => $request['name'],
            'email' => $request['email']
        )
    );
    return $row;
}
}


$restPlugin = new restPlugin();
