
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


class DWWP_social_media_plugin {

    static function init() {
        // hookar oss in i admin menu
        add_action('admin_menu', array(__CLASS__, 'create_plugin_menu'));
        add_action( 'admin_init', array(__CLASS__, 'register_feed_settings' ));
        add_action('admin_enqueue_scripts', array(__CLASS__, 'admin_hashtag_separator'));
        //add_shortcode('WP_media_shortcode', array(__CLASS__, 'WP_media_shortcode'));
    }

static function admin_hashtag_separator () {

    wp_enqueue_script('app', plugin_dir_url( __FILE__ ) . '/js/hashtags.js', array('jquery','hashtags','jquery-ui-autocomplete'), false, true);
    wp_enqueue_script('hashtags', plugin_dir_url( __FILE__ ) . '/php/JQuery-hashtags/src/jquery.tagsinput.js', array(), false, false);
    wp_enqueue_style('hashtag-color', plugin_dir_url(__FILE__) . '/php/JQuery-hashtags/src/jquery.tagsinput.css');
    wp_enqueue_style('own-css', plugin_dir_url(__FILE__) . '/style.css');

}

static function create_plugin_menu() {

    //create new top-level menu
    add_menu_page('Add new feed', 'Social-media feed', 'administrator', __FILE__, array(__CLASS__,'feed_settings_page') , do_shortcode('
dashicons-share'));
}



 static function register_feed_settings() {
    //register our settings
    register_setting( 'my-cool-plugin-settings-group', array(__CLASS__,'clientID') );
    register_setting( 'my-cool-plugin-settings-group', array(__CLASS__,'clientSecret') );
    register_setting( 'my-cool-plugin-settings-group', array(__CLASS__,'igName' ) );
}

static function feed_settings_page() {
  ?>

<div class="wrap">




        <h2>Add new feed</h2>
    <?php
    if(isset($_POST['submit_feed'])) {
        require_once __DIR__ . "/php/instagram-api/api.php";
        ?>
        <a href="<?php echo $url ?>" target="_self"><?php _e('Login to get access token') ?></a>
        <?php
        if(isset($_GET['code']))
        {
            echo $_GET['code'];
        }


        $clientID =  preg_replace('/\s+/', '',$_POST['client_id']);
        $clientSecret =  preg_replace('/\s+/', '',$_POST['client_secret']);
        $option = "instagram_settings";
        $array = array(
            'client_id' => $clientID,
            'client_secret' => $clientSecret,
            'success_token' => $_GET['code']
        );

        serialize($array);

        update_option( $option, $array);

        //echo "<pre>".print_r($fetch1['clientSecret'],1)."</pre>";
    }
    ?>
        <form method="post" action="">
            <?php settings_fields( 'my-cool-plugin-settings-group' ); ?>
            <?php do_settings_sections( 'my-cool-plugin-settings-group' ); ?>
            <?php
            $DB = get_option('instagram_settings');
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php _e('Client ID:','tibor')?></th>
                    <td><input type="text" name="client_id" value="<?php echo esc_attr($DB['client_id']); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Client Secret:','tibor')?></th>
                    <td><input type="text" name="client_secret" value="<?php echo esc_attr( $DB['client_secret'] ); ?>" /></td>
                </tr>

            <tr valign="top">
                    <th scope="row"><?php _e('Enter hashtags:','tibor')?></th>

                    <td><input name="tags" id="tags"  /></td>
                </tr>




          <!--      <tr valign="top">
                    <th scope="row">Options, Etc.</th>
                    <td><input type="text" name="option_etc" value="<?php echo esc_attr( get_option('ig_name') ); ?>" /></td>
                </tr> -->

            <tr valign="top">
                <th scope="row"><?php _e ('Specify feed:','tibor' ) ?> </th>
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

            <input type="submit" class="btn btn-prime" name="submit_feed" value="<?php _e('Save')?>">

        </form>
    </div>

<br>
    <div class ="information">

<h2 id="info"> Information </h2>
        <hr>




        <h4> How to use Client ID and Client Secrets</h4>
<p>
Client ID and Client Secrets <br>
    you can find the Client ID and Client Secret at the  <br>developer page on either Instagram or facebook.<br>
    Copy these and put these inside the fields.
</p><hr>

        <h4> How to use hashtags.</h4>
<p> The hashtag-function automatically switch every hashtag to lowercase values. <br>
    to start declaring a hashtag, write the specific hashtag and end every hashtag with a comma. </p>
       <hr>

<h4>Specify feeds. </h4>
<p> You need to specify if it's either a facebook or a instagram feed.</p>
    </div>
<?php }

    static function DWWP_instagram_api(){

        $instagram = new Instagram(array(
            'apiKey'      => get_option('clientID'),
            'apiSecret'   => get_option('clientSecret'),
            'apiCallback' => get_option('http://tibor.dev/success') // testsida
        ));

        echo "<a href='{$instagram->getLoginUrl()}'>Login with Instagram</a>";
    }
}
DWWP_social_media_plugin::init();
?>
