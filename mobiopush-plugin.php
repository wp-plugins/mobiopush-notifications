<?php 
    /*
    Plugin Name: Mobiopush Push Notifications
    Plugin URI: https://mobiopush.com
    Description: Plugin to improve user engagement by using push notifications.Mobiopush provides Web Notification & Push Notification services in one package allowing you to reach out to your Active and Passives users respectively. These Notifications are the new way to identify and re-enage your users.
    Author: MobioPush
    Version: 1.0
    Author URI: https://mobiopush.com
    */
    
    
 define( 'PG_PATH', plugin_dir_url( __FILE__ ) );   
    

    
 add_action( 'wp_footer', 'mp_footer'); 
 add_action( 'publish_post ', 'mp_footer'); 
 add_action( 'post_submitbox_misc_actions', 'add_checkbox');
 add_action( 'save_post', 'mpush_post_saved');
 add_action( 'add_meta_boxes_post', 'mobio_custom_box'  );
 add_action( 'admin_menu', 'mp_menu');
 add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'add_action_links' );
 add_action( 'admin_post_save_services_options', array($this, 'on_save_changes'));

 
 $shortname = "mobio";
				
add_action('admin_menu', array($this,'admin_menu'), 11);
function admin_menu()
{
    global $pagenow;

    if( $pagenow == 'plugins.php' )
    {
        $hook = apply_filters('acf/get_info', 'hook');

        add_action( 'in_plugin_update_message-' . $hook, array($this, 'in_plugin_update_message'), 10, 2 );
    }
}

function in_plugin_update_message( $plugin_data, $r )
{
    $version = apply_filters('acf/get_info', 'version');
    $readme = wp_remote_fopen( 'http://plugins.svn.wordpress.org/mobiopush-notifications/trunk/readme.txt' );
    $regexp = '/== Changelog ==(.*)= ' . $version . ' =/sm';
    $o = '';

    if( !$readme )
    {
        return;
    }

    preg_match( $regexp, $readme, $matches );

    if( ! isset($matches[1]) )
    {
        return;
    }

    $changelog = explode('*', $matches[1]);
    array_shift( $changelog );

    if( !empty($changelog) )
    {
        $o .= '<div class="acf-plugin-update-info">';
        $o .= '<h3>' . __("What's new", 'acf') . '</h3>';
        $o .= '<ul>';

        foreach( $changelog as $item )
        {
            $item = explode('http', $item);

            $o .= '<li>' . $item[0];

            if( isset($item[1]) )
            {
                $o .= '<a href="http' . $item[1] . '" target="_blank">' . __("credits",'acf') . '</a>';
            }

            $o .= '</li>';


        }

        $o .= '</ul></div>';
    }

    echo $o;
}				

function add_action_links ( $links ) {
        $rlink = array(
            '<a href="' . admin_url( 'admin.php?page=mobiopush' ) . '">Configure MobioPush</a>',
        );
        return array_merge( $rlink, $links );
    }

function mp_menu() {
	
	add_action( 'admin_init', 'register_mysettings' );
	
	
	
	add_menu_page(
        	'MobioPush',
            'MobioPush',
            'manage_options',
            'mobiopush',
        	'baw_settings_page' ,
            PG_PATH.'logo.png'
        );
}



function register_mysettings() {




	register_setting( 'mobio-settings-group', 'mobio_site_key' );
	register_setting( 'mobio-settings-group', 'mobio_api_key' );
	register_setting( 'mobio-settings-group', 'mobio_enable_push' );
	register_setting( 'mobio-settings-group', 'mobio_enable_web' );
	register_setting( 'mobio-settings-group', 'mobio_enable_auto' );	
	register_setting( 'mobio-settings-group', 'mobio_web_time' );	
	register_setting( 'mobio-settings-group', 'mobio_default_title' );	

        
        
}

function baw_settings_page() {

?>
<div class="wrap">
<h2>MobioPush Settings</h2>

<?php
 if ( isset( $_GET['settings-updated'] ) )  {
 ?>
<div id="message" class="updated below-h2"><p>Settings updated Successfuly</p></div>
<?php
}
?>
<form method="post" action="options.php?status=heheh">
    <?php settings_fields( 'mobio-settings-group' ); ?>
    <?php do_settings_sections( 'mobio-settings-group' ); ?>
    <table class="form-table">
    
    
       
        


        <tr valign="top">
        <th scope="row">MobioPush Site Key</th>
        
        <td> <input type="text" name="mobio_site_key" value="<?php echo esc_attr( get_option('mobio_site_key') ); ?>" class="regular-text"/>
        <br><p class="description" >*Required . Goto <a href="https://mobiopush.com/Dashboard/Settings.php">MobioPush Settings</a> to get the SITE Key</span><br>
        </td>
        </tr>
         
        <tr valign="top">
        <th scope="row">MobioPush API Key</th>
        <td><input type="text" name="mobio_api_key" value="<?php echo esc_attr( get_option('mobio_api_key') ); ?>"  class="regular-text"/>
        <br><p class="description" > *Required . Goto <a href="https://mobiopush.com/Dashboard/Settings.php">MobioPush Settings</a> to get the API Key</span><br>
        </td>
        
        </tr>
        
        <tr valign="top">
        <th scope="row">Default Notification Title</th>
        <td><input type="text" name="mobio_default_title" value="<?php echo esc_attr( get_option('mobio_default_title') ); ?>"  class="regular-text"/>
        <br><p class="description" > If you leave blank , blog name will be used as title</span><br>
        </td>
        </tr>
         <tr valign="top">
        <th scope="row">Push Notifications</th>
        <td> 
        <div class="mnt-radio">
        <?php
        
         $options = array("name" => "Push notification status",
				"options" => array("true" => "Enable", "false" => "Disable"),
				"std" => "right");
				
			foreach ($options['options'] as $option_value => $option_text) {
			$checked = ' ';
			if (get_option('mobio_enable_push') == $option_value) {
				$checked = ' checked="checked" ';
			}
			else if (get_option('mobio_enable_push') === FALSE && $value['std'] == $option_value){
				$checked = ' checked="checked" ';
			}
			else {
				$checked = ' ';
			}
			echo ' <input type="radio" name="mobio_enable_push" value="'.
				$option_value.'" '.$checked."/> ".$option_text."";
		}	
			?>	
   </div> 
       
 </td>
        </tr>
        
       
           <tr valign="top">
        <th scope="row">Web Notifications</th>
        <td> 
        <div class="mnt-radio">
        <?php
        
         $options = array("name" => "Web notification status",
				"options" => array("true" => "Enable", "false" => "Disable"),
				"std" => "right");
				
			foreach ($options['options'] as $option_value => $option_text) {
			$checked = ' ';
			if (get_option('mobio_enable_web') == $option_value) {
				$checked = ' checked="checked" ';
			}
			else if (get_option('mobio_enable_web') === FALSE && $value['std'] == $option_value){
				$checked = ' checked="checked" ';
			}
			else {
				$checked = ' ';
			}
			echo ' <input type="radio" name="mobio_enable_web" value="'.
				$option_value.'" '.$checked."/>".$option_text."";
		}	
			?>	
   </div>     
 </td>
        </tr>
        
        
            <tr valign="top">
        <th scope="row">Notify Automatically</th>
        <td> 
        <div class="mnt-radio">
        <?php
        
         $options = array("name" => "Sidebar Position",
				"desc" => "push notification status",
				"options" => array("true" => "Enable", "false" => "Disable"),
				"std" => "right");
				
			foreach ($options['options'] as $option_value => $option_text) {
			$checked = ' ';
			if (get_option('mobio_enable_auto') == $option_value) {
				$checked = ' checked="checked" ';
			}
			else if (get_option('mobio_enable_auto') === FALSE && $value['std'] == $option_value){
				$checked = ' checked="checked" ';
			}
			else {
				$checked = ' ';
			}
			echo ' <input type="radio" name="mobio_enable_auto" value="'.
				$option_value.'" '.$checked."/>".$option_text."";
		}	
			?>	
   </div>     
 </td>
 
 
        </tr>
        
        
        
		
		
           <tr valign="top">
        <th scope="row">Default web notification time period </th>
        <td> 
        
        
         <?php
        
         $options = 
 array("name" => "category posts to show on the front page",
				"desc" => "Select the web notification time",
				"options" => array(1400=>"1 Day",60=>"60 Minutes",20=>"20 Minutes",15=>"15 Minutes",10=>"10 Minutes",5=>"5 Minutes"),
				"std" => array("ddd","fsfd")
		);
		
		echo "<select id='mobio_web_time' class='post_form' name='mobio_web_time' value='true'>\n";
		
		foreach ($options['options'] as $option_value => $option_list) {
		
		if (get_option('mobio_web_time') == $option_value)
		echo '<option value="'.get_option('mobio_web_time').'" class="level-0" '.$checked.' />'.$option_list."</option>\n";
		}
		
		foreach ($options['options'] as $option_value => $option_list) {
				$checked = ' ';
				
				echo get_option('mobio_web_time');
				echo $option_list;
				//echo 'value_id=' . $value['id'] .' value_id=' . get_option('mobio_web_time') . ' options_value=' . $option_value;
			if (get_option('mobio_web_time') == $option_value) {
				$checked = ' checked="checked" ';
				
			}
			
				echo '<option value="'.$option_value.'" class="level-0" '.$checked.' />'.$option_list."</option>\n";
				//$all_categoris .= $option_list['name'] . ',';
			}	
			echo "</select>\n </div>";
		?>
 
        	</td>
        </tr>
    </table>
    
    <?php submit_button();
    

     ?>

</form>
</div>
<?php }
 
 
 
 
function mobio_custom_box( $post ) {

        add_meta_box(
            '_mobio',
            'MobioPush advanced setttings (Optional)',
            'mobio_custom_box_content' ,
            'post',
            'normal',
            'high'
        );
    }

function mobio_custom_box_content( $post ) {
        ?>

        <div id="mobio-box">
            <input type="text" id="mobio_title" placeholder="Enter notification title" name="mobio_title" value="" style="font-size: 1.2em;height: 1.7em;width: 100%;background-color: #fff;"/>
            
            <span id="mobio_desc" > Leave blank if title of post can be used as title of notification</span>
        <br><br>
        <input type="text" name="mobio_body" size="30" value="" id="title" spellcheck="true" autocomplete="off" placeholder="Enter notification content" style="font-size: 1.2em;height: 1.7em;width: 100%;background-color: #fff;">
        
        <span id="mobio_desc" > Leave blank if first 140 characters of post can be used as title of notification</span>

 <br><br>

Web Notification time period : <select name="mobio_web_time" aria-invalid="false">
<option value=""></option>
												<option value="1400">1 Day</option>
						<option value="60">60 Minutes</option>
						<option value="20">20 Minutes</option>
						<option value="15">15 Minutes</option>
						<option value="10">10 Minutes</option>
						<option value="5">5 Minutes</option>
					</select>
        </div>
    <?php
    }
 

    
function mpush_post_saved( $postId ) {

$mobio_title=esc_attr( get_option('mobio_default_title') );
if(empty($mobio_title))
$mobio_title=get_bloginfo('name');
$mobio_web_time_api=esc_attr( get_option('mobio_web_time') );
if(empty($mobio_web_time_api))
$mobio_web_time_api=1400;
else {

$mobio_web_time_api=esc_attr( get_option('mobio_web_time') );

}
$mobio_body=get_the_title( $postId );
$mobio_url_api=wp_get_shortlink( $post_id );
$mobio_web_not_api=0;
$mobio_push_not_api=0;

if(esc_attr( get_option('mobio_enable_web') ) == "true")
$mobio_web_not_api=1;
if(esc_attr( get_option('mobio_enable_push') ) == "true")
$mobio_push_not_api=1;

$mobio_web_time=esc_attr( get_option('mobio_enable_web') );


// Autosave, do nothing
if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
        return;
// AJAX? Not used here
if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) 
        return;
// Check user permissions
if ( ! current_user_can( 'edit_post', $postId ) )
        return;
// Return if it's a post revision
if ( false !== wp_is_post_revision( $postId ) )
        return;
        
        if($GET['preview'])
        return;
        
        if ( 'trash' == get_post_status( $postId ))
        return;
        
        if ( 'draft' == get_post_status( $postId ))
        return;

        if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || empty( $_POST['mobio_active_checkbox'] ) ) {
            return false;
        } else {
         
        
        if ( isset( $_POST['mobio_title'] ) && ! empty( $_POST['mobio_title'] ) ) {
                        $mobio_title = $_POST['mobio_title'];
                    }
                    
         if ( isset( $_POST['mobio_body'] ) && ! empty( $_POST['mobio_body'] ) ) {
                        $mobio_body = $_POST['mobio_body'];
                    }
                    
         if ( isset( $_POST['mobio_web_time'] ) && !($_POST['mobio_web_time'] =='' )) {
                        $mobio_web_time_api = $_POST['mobio_web_time'];
                    }           
        
           
            if ( isset( $_POST['mobio_check_box'] )) {
            $ch = curl_init(); 
curl_setopt($ch, CURLOPT_URL,"http://api.mobiopush.com/v1/");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, 
http_build_query(array(			'SITE_KEY' => esc_attr( get_option('mobio_site_key')),
        						'API_KEY' =>esc_attr( get_option('mobio_api_key')),
        						'TITLE' => substr($mobio_title,0,70),
        						'MESSAGE' => substr($mobio_body,0,120), 
								'WEB_NOTIFICATION'					=> $mobio_web_not_api,
								'WEB_NOTIFICATION_TIME'				=> $mobio_web_time_api,
								'PUSH_NOTIFICATION'					=> $mobio_push_not_api,
        						'LINK' => $mobio_url_api,
        						)));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$server_output = curl_exec ($ch);
echo $server_output;
            } 
            
        }
    }
    
 
function add_checkbox() {

            printf('<div class="misc-pub-section misc-pub-section-last" id="mobio_check_box">');
            
            

       if(esc_attr( get_option('mobio_enable_auto') ) == "true")

                    printf('<br><label><input type="checkbox" checked="checked" value="1" id="mobio_check_box" name="mobio_check_box" style="margin: -3px 9px 0 1px;"/>Send Mobiopush Notification</label><input type=hidden value="1" name="mobio_active_checkbox">' );
        else
        
                    printf('<br><label><input type="checkbox" value="0" id="mobio_check_box" name="mobio_check_box" style="margin: -3px 9px 0 1px;"/>Send Mobiopush Notification</label><input type=hidden value="1" name="mobio_active_checkbox">' );

               
               printf('</div>');
    }
    
    function mp_footer() {
    
    ?>
<!-- start of mobio code -->
<script src="//cdn.mobiopush.com/mobiojs/<?php echo esc_attr( get_option('mobio_site_key') ); ?>" type="text/javascript" id="_mobio_js"></script>
<!-- end of mobio code -->    
    

  
    
    <?php
    
    }
    
    
    
    
?>