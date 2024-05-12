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
    <h3>Fill the form to create user</h3>
    <p>Implementation of CSRF using nonce & ajax to use endpoints</p>
       <div class="simple-contact-form">
            <form id="create-user">
                <div class="form-group">
                    <label for="fullname">Full Name</label>
                    <input type="text" class="form-control" id="fullname" aria-describedby="namelHelp" placeholder="Enter email">
                </div>
                <div class="form-group">
                    <label for="emaill">Email address</label>
                    <input type="email" class="form-control" id="email" aria-describedby="emailHelp" placeholder="Enter email">
                </div>          
                
                <button type="submit" class="btn btn-primary">Submit</button>
        </form>
    </div>

    <?php }   

    public function insert_data_to_my_table(){
        global $wpdb;
        $table_name = $wpdb->prefix . 'form_submission';
        if (isset($_POST['submit'])) {
          $name = sanitize_text_field($_POST['names']);
          $email = sanitize_email($_POST['email']);
          $format = array( '%s', '%s' );
          $wpdb->insert($table_name, array('name' => $name, 'email' => $email), $format );    
         }      
     ?>
     <div class="card-body">
        <div class="wrap">
           <h3>Add Information</h3>
             <table class="table">
                 <thead>
                     <tr>
                         <th>User ID</th>
                         <th>Name</th>
                         <th>Email Address</th>
                         <th>Actions</th>
                     </tr>
                 </thead>
                 <tbody>
                     <form action="" method="post" id="simple-form">
                         <tr>
                             <td><input type="text" value="AUTO_GENERATED" disabled style="width:100%"></td>
                             <td><input type="text" required id="names" name="names" style="width:100%"></td>
                             <td><input type="text" required id="email" name="email" style="width:100%"></td>
                             <td><input type="submit" name="submit" type="submit" style="width:100%"></td>
                         </tr>
                     </form>
                 </tbody>
             </table>
         </div>
     </div>
     <?php
     }
// Callback function for shortcode2
     public function my_shortcode_list() {
        global $wpdb;       
        $results = '<div class="card-body"><table class="table">
                   <thead>
                    <tr>
                        <th>id</th>
                        <th>name</th>
                        <th>email id</th>
                    </tr>
                </thead>
            <tbody>';
    
            // sending query
            $WPQuery = $wpdb->get_results ("SELECT * FROM wp_form_submission");
                foreach ( $WPQuery as $print )   {
                    $results .= "<tr>
                                <td>$print->id</td>
                                <td>$print->name</td>
                                <td>$print->email</td>
                            </tr>";
                        }
                    $results .= "</tbody></table></div>";
    
        //Print results
        echo $results;
        
        //Call the Search function on table
     $search = get_my_table_data();
    }
    
    //Create serach function from table to display data.
    public function get_my_table_data(){
        global $wpdb;
       ?>
       <div class="card-body"><h3>Search on Table</h3>
       <form action="" method="get">
        <input type="text" required id="searchme" name="searchme" value='<?php if(isset($_GET['searchme'])){echo $_GET['searchme'];}?>' placeholder="Search">
        <input type="submit" id="search-item" name="search-item" value="search">
        </form>
        <table class="table">
                <thead>
                    <tr>
                        <th>id</th>
                        <th>name</th>
                        <th>email id</th>
                    </tr>
                </thead>
                <tbody>
        <?php
        
        $table_name = $wpdb->prefix . 'form_submission';
        if (isset($_GET['searchme'])) {
            $input = sanitize_text_field($_GET['searchme']);
            $sresults = $wpdb->get_results( "SELECT * FROM $table_name WHERE name LIKE '%$input%'" );
            if($sresults){
            foreach($sresults as $display){
                echo "<tr><td>$display->id</td>";
                echo "<td>$display->name</td>";
                echo "<td>$display->email</td>";
                echo "</tr>";
                echo "</tbody>";
                echo "</table></div>";  
            } }else echo "Not found";
        }else echo "<table>";
    }
    

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
        'form_submission_route/v1',
        '/create-user',
        array(
            'methods' => WP_REST_SERVER::CREATABLE,
            'callback' => 'form_get_callback',
            'permission_callback' => 'wp_check_permission'
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
public function form_get_callback($request){
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

