
<?php


/**
 * Plugin Name: Social-media feed plugin
 * Plugin URI: http://www.iqq.se
 * Description: A plugin for Wordpress that connects a specific feeds Facebook and Instagram page and converts it to JSON-text.
 * Author: Tibor Lundberg - IQQ.
 * Version: 0.0.1
 */

// Antingen acessar du via pluginsettings, eller så kommer du inte in alls.
defined( 'ABSPATH' ) or die( 'Access denied' );


class DWWP_social_media_plugin
{

    static function init()
    {
        // the first action hooks us in the admin menu sidebar.
        add_action('admin_menu', array(__CLASS__, 'create_plugin_menu'));
        // registers and saves (?) the input settings.
        add_action('admin_init', array(__CLASS__, 'register_feed_settings'));
        // registers the hahstag-separator.
        add_action('admin_enqueue_scripts', array(__CLASS__, 'admin_hashtag_separator'));
        //add_action('admin_menu', array(__CLASS__, 'create_submenu'));
        //add_shortcode('WP_media_shortcode', array(__CLASS__, 'WP_media_shortcode'));
        add_action ('admin_enqueue_scripts', array(__CLASS__, 'admin_repository'));
    }

    // imports the scripts from JQuery-hashtags.
    static function admin_hashtag_separator()
    {

        wp_enqueue_script('app', plugin_dir_url(__FILE__) . '/js/hashtags.js', array('jquery', 'hashtags', 'jquery-ui-autocomplete'), false, true);
        wp_enqueue_script('hashtags', plugin_dir_url(__FILE__) . '/php/JQuery-hashtags/src/jquery.tagsinput.js', array(), false, false);
        wp_enqueue_style('hashtag-color', plugin_dir_url(__FILE__) . '/php/JQuery-hashtags/src/jquery.tagsinput.css');
        wp_enqueue_style('own-css', plugin_dir_url(__FILE__) . '/style.css');

    }

    static function admin_repository()
    {
        wp_enqueue_script ('repository', plugin_dir_url(__FILE__) . '/js/repository.js');
    }

    // declares the plugin-menu and submenus.
    static function create_plugin_menu()
    {


        //create new top-level menu
        add_menu_page('Social-media-plugin', 'Add new feed', 'administrator', 'social-media-new-feed', array(__CLASS__, 'page_add_feed'), do_shortcode('
dashicons-share'));
        add_submenu_page('social-media-new-feed', 'Edit-existing-feed', 'Edit existing feed', 'administrator', __FILE__, array(__CLASS__, 'page_edit_feed'));

    }

    /* add_action('admin_menu', 'my_menu_pages');
function my_menu_pages(){
    add_menu_page('My Page Title', 'My Menu Title', 'manage_options', 'my-menu', 'my_menu_output' );
    add_submenu_page('my-menu', 'Submenu Page Title', 'Whatever You Want', 'manage_options', 'my-menu' );
    add_submenu_page('my-menu', 'Submenu Page Title2', 'Whatever You Want2', 'manage_options', 'my-menu2' );
}
     */

// registers the feed.
    static function register_feed_settings()
    {
        //register our settings
        register_setting('settings-group', array(__CLASS__, 'clientID'));
        register_setting('settings-group', array(__CLASS__, 'clientSecret'));
        register_setting('settings-group', array(__CLASS__, 'igName'));
    }

    //
    static function page_add_feed()
    {
        ?>

        <div class="wrap">


            <h2>Add new feed</h2>
            <?php
            if (isset($_POST['submit_feed'])) {
                require_once __DIR__ . "/php/instagram-api/api.php";
                ?>
                <a href="<?php echo $url ?>" target="_self"><?php _e('Login to get access token') ?></a>
                <?php
                if (isset($_GET['code'])) {
                    echo $_GET['code'];
                }

// deklarerar lokalvariabler som tar information som begärs
                $clientID = preg_replace('/\s+/', '', $_POST['client_id']);
                $clientSecret = preg_replace('/\s+/', '', $_POST['client_secret']);
                $feedname = $_POST['ig_name'];
                $option = "instagram_settings";
                $array = array(
                    'client_id' => $clientID,
                    'client_secret' => $clientSecret,
                    'ig_name' => $feedname,
                    'success_token' => $_GET['code']
                );

                // serialiserar värdena.
                serialize($array);

                update_option($option, $array);

                //echo "<pre>".print_r($fetch1['clientSecret'],1)."</pre>";
            }
            ?>
            <form method="post" action="">
                <?php settings_fields('settings-group'); ?>
                <?php do_settings_sections('settings-group'); ?>
                <?php
                $DB = get_option('instagram_settings');
                ?>
                <table class="form-table">

                    <!-- deklararerar namnet på själva feeden -->
                    <tr valign="top">
                        <th scope="row"><?php _e('Feed Name:', 'tibor') ?> </th>
                        <td><input type="text" name="feed_name"
                                   value="<?php echo esc_attr($DB['ig_name']); //get_option('ig_name') );// ?>"/></td>
                    </tr>


                    <!--Deklarerar KlientID't från developer-sidan-->
                    <tr valign="top">
                        <th scope="row"><?php _e('Client ID:', 'tibor') ?></th>
                        <td><input type="text" name="client_id" value="<?php echo esc_attr($DB['client_id']); ?>"/></td>
                    </tr>

                    <!-- deklarerar Klient-secret-koden från developer-sidan-->
                    <tr valign="top">
                        <th scope="row"><?php _e('Client Secret:', 'tibor') ?></th>
                        <td><input type="text" name="client_secret"
                                   value="<?php echo esc_attr($DB['client_secret']); ?>"/></td>
                    </tr>

                    <!-- Gör så man kan deklarerar hashtagsen-->
                    <tr valign="top">
                        <th scope="row"><?php _e('Enter hashtags:', 'tibor') ?></th>

                        <td><input name="tags" id="tags"/></td>
                    </tr>


                    <!--      <tr valign="top">
                              <th scope="row">Options, Etc.</th>
                              <td><input type="text" name="option_etc" value=" /></td>
                          </tr> -->

                    <!-- En combobox som gör att man väljer antingen instagram eller facebook-feed-->
                    <tr valign="top">
                        <th scope="row"><?php _e('Specify feed:', 'tibor') ?> </th>
                        <td>
                            <select>
                                <option value="Instagram">Instagram</option>
                                <option value="Facebook">Facebook</option>
                            </select>
                        </td>

                    </tr>
                </table>

                <br>
                <br>

                <!--if-sats som sparar värdena -->
                <input type="submit" class="btn btn-prime" name="submit_feed" value="<?php _e('Save') ?>">

            </form>
        </div>

        <br>

        <!-- informations-box -->
        <div class="information">

            <h2 id="info"> Information </h2>
            <hr>


            <h4> How to use Client ID and Client Secrets</h4>
            <p>
                Client ID and Client Secrets <br>
                you can find the Client ID and Client Secret at the <br>developer page on either Instagram or
                facebook.<br>
                Copy these and put these inside the fields.
            </p>
            <hr>

            <h4> How to use hashtags.</h4>
            <p> The hashtag-function automatically switch every hashtag to lowercase values. <br>
                to start declaring a hashtag, write the specific hashtag and end every hashtag with a comma. </p>
            <hr>

            <h4>Specify feeds. </h4>
            <p> You need to specify if it's either a facebook or a instagram feed.</p>
        </div>

        <!--Instagram-apiet som läggs in i filen.-->
    <?php }

    static function DWWP_instagram_api()
    {

        $instagram = new Instagram(array(
            'apiKey' => get_option('clientID'),
            'apiSecret' => get_option('clientSecret'),
            'apiCallback' => get_option('http://tibor.dev/success') // testsida
        ));

        echo "<a href='{$instagram->getLoginUrl()}'>Login with Instagram</a>";
    }


    static function page_edit_feed()
    { ?>
        <!-- ÄNDRA TEXTEN -->
        <div class="wrap">
        <h2> Edit existing feed </h2>

<br>
            <h4 id = "rubrik"> Enter the feedname you wish to edit</h4>
           <select id="long">
            <option>Hej</option>

               <option> Heeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeej</option>
               <option>Exempelfeed Som ej är länkad</option>
               </select>
            <hr>
            <br>
            <!--Deklarerar KlientID't från developer-sidan-->

            <h4 id = "rubrik"> If you wish to update the access-token: </h4>


            <tr valign="top">
                <th scope="row"></th>
                <td><input id = "long" name="tags" id="tags"/></td>
            </tr>

            <br>
            <br>


            <button id="button" onclick="update_access_token()">Update access-token </button>






<br><br><hr>

            <!--Ska tillåta att ta bort eller ändra hashtags-->
            <h4 id = "rubrik">Edit the existing hashtags: </h4>


            <tr valign="top">
                <th scope="row"></th>
                <td><input id = "long"type="text"/></td>
            </tr>
            <br><br>
            <button id="button" onclick="hashtag_update()">Update the hashtags </button>

<br><br><hr>



        <!--Ta bort en feed-->
            <h4 id = "rubrik">Delete selected feed? </h4>

            <button id="button" onclick="delete_feed()">Delete the feed </button>

            <br><br>


        </div>
        <br>
        <div class="information">

            <h2 id="info"> Information </h2>
            <hr>


            <h4> How to use Client ID and Client Secrets</h4>
            <p>
                Client ID and Client Secrets <br>
                you can find the Client ID and Client Secret at the <br>developer page on either Instagram or
                facebook.<br>
                Copy these and put these inside the fields.
            </p>
            <hr>

            <h4> How to use hashtags.</h4>
            <p> The hashtag-function automatically switch every hashtag to lowercase values. <br>
                to start declaring a hashtag, write the specific hashtag and end every hashtag with a comma. </p>
            <hr>

            <h4>Specify feeds. </h4>
            <p> You need to specify if it's either a facebook or a instagram feed.</p>
        </div>

    <?php }

}
DWWP_social_media_plugin::init();
 ?>

