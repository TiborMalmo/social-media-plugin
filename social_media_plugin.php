<?php


/**
 * Plugin Name: Simple social-media to JSON
 * Plugin URI: http://www.iqq.se
 * Description: A plugin for Wordpress that connects a specific feeds Facebook and Instagram page and converts it to JSON-text.
 * Author: Tibor Lundberg, intern @ IQQ.
 * Version: 0.0.1
 */

// if not accessed by wordpress, you don't get permission to access the code.
defined( 'ABSPATH' ) or die( 'Access denied' );


class DWWP_social_media_plugin {

	static function init() {
		// the first action hooks us in the admin menu sidebar.
		add_action( 'admin_menu', array( __CLASS__, 'create_plugin_menu' ) );
		// registers the feed settings.
		add_action( 'admin_init', array( __CLASS__, 'register_feed_settings' ) );



		//add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_repository' ) );
		// connects the fb api

		add_action('admin_enqueue_scripts', array(__CLASS__, 'style'));

		add_action( 'init', array( __CLASS__, 'myStartSession' ), 1 );
		add_action( 'wp_logout', array( __CLASS__, 'myEndSession' ) );
		add_action( 'wp_login', array( __CLASS__, 'myEndSession' ) );

		add_action('admin_init', array(__CLASS__, 'redirect_facebook'));

	}

	// redirects a facebook-page.
	static function redirect_facebook() {
		if(isset($_POST['submit'])){

			update_option('page-id', $_POST['page-id']);
			wp_redirect(  'http://tibor.dev/wp-admin/admin.php?page=facebook-feed-login&page-id=' . get_option('page-id') );

			exit;
		}
	}

	static function style(){
		wp_enqueue_style('style', plugin_dir_url(__FILE__) . '/style.css');
	}

	static function admin_repository() {
		wp_enqueue_script( 'repository', plugin_dir_url( __FILE__ ) . '/js/repository.js' );
	}


	// declares the plugin-menu and submenus.
	static function create_plugin_menu() {


		//create new top-level menu
		add_menu_page( 'Social-media-plugin', 'Feeds', 'administrator', 'social-media-feeds', array(
			__CLASS__,
			'feeds_page'
		), do_shortcode( '
dashicons-share' ) );

		add_submenu_page( 'social-media-feeds', 'Add new instagram feed', 'Add new instagram feed', 'administrator', 'social-media-instagram-feed', array(
			__CLASS__, 'page_add_instagram_feed')
		);

		add_submenu_page( 'social-media-feeds', 'Edit-existing-feed', 'Edit existing feed', 'administrator', 'edit-feed', array(
			__CLASS__, 'page_edit_feed')
		);


		add_submenu_page ('social-media-feeds', 'Facebook-feed-login', 'Facebook feed login', 'administrator', 'facebook-feed-login', array (
			__CLASS__, 'page_facebook_feed'

		) );

	}


// registers the feed in instagram.
	static function register_feed_settings() {
		//register our settings
		register_setting( 'settings-group', array( __CLASS__, 'clientID' ) );
		register_setting( 'settings-group', array( __CLASS__, 'clientSecret' ) );
		register_setting( 'settings-group', array( __CLASS__, 'feed_name' ) );
		register_setting( 'settings-group', array( __CLASS__, 'code' ) );
	}

	static function myStartSession() {
		if ( ! session_id() ) {
			session_start();
		}
	}


	static function myEndSession() {
		session_destroy();
	}


	//start-page.
static function feeds_page() { ?>

	<h2 class="testar"> Welcome to social media-feeds plugin. </h2>
	<hr>
		<div id = "welcomemenu">
			<h3 id ="feed-welcome-rubrik"> Instagram feeds.</h3>
		<p> This plugin connects a specific social media, and converts it to JSON-text. To start using this plugin, please select a submenu that corresponds to what you want to convert to JSON-text. </p>
			<h4> Add new Instagram Feed.</h4>
			<p> To convert Instagram feeds to JSON-text, you need to have access to your instagrams client ID and client secret.
				Simply copy and paste this information to the respective fields on the Instagram submenu-page.
				You can name the feed anything you want, the JSON-information and data about access-tokens will be saved in the database. </p>

				<h4> Edit a instagram-feed.</h4>

			<p> If you wish to update a instagram-client, you can simply do it by accessing the edit-instagram submenu-page. There are support for deleting the feed or updating the client id if it should expire. </p>

		</div>
<div id= "welcomemenu">
	<h3 id ="feed-welcome-rubrik"> Facebook feeds. </h3>

	<p> like the instagram json-feed, the ...</p>
	<h4> Add new Facebook feed.</h4>
	<p> will change when included</p>
	<h4> Edit a Facebook-feed</h4>
	<p> will change when included</p>

	<div >

	</div>

</div>
<?php }

	static function page_add_instagram_feed() {
		?>

		<div class="wrap">


			<h2>Add new feed</h2>
			<?php
			DWWP_social_media_plugin::fetch_access_token();
			if ( isset( $_POST['submit_feed'] ) ) {
				?>
				<pre>Saved into the DB!</pre> <?php
				require_once __DIR__ . "/php/instagram-api/code.php";
				?>
				<a href="<?php echo $url ?>" target="_self"><?php _e( 'fetch instagram code ' ) ?></a>
				<?php
				if ( isset( $_GET['response_type'] ) ) {
					echo $_GET['response_type'];
				}

				// deklarerar lokalvariabler som tar information som begärs
				$clientID     = preg_replace( '/\s+/', '', $_POST['client_id'] );
				$clientSecret = preg_replace( '/\s+/', '', $_POST['client_secret'] );
				$feedname     = $_POST['feed_name'];
				$option       = "instagram_settings_" . sanitize_title( $feedname );


				$array = array(
					'client_id'     => $clientID,
					'client_secret' => $clientSecret,
					'feed_name'     => $feedname,
					'code'          => $_GET['code'],
					'hashtags'      => array( $_POST['tags'] )

				);

				// serialiserar värdena.
				serialize( $array );

				update_option( $option, $array );


			}
			?>


			<form method="post" action="">
				<?php settings_fields( 'settings-group' ); ?>
				<?php do_settings_sections( 'settings-group' ); ?>
				<?php
				$DB = get_option( 'instagram_settings' );


				?>

				<table class="form-table">


					<!-- deklararerar namnet på själva feeden -->
					<tr valign="top">
						<th scope="row"><?php _e( 'Feed Name:', 'tibor' ) ?> </th>
						<td><input type="text" name="feed_name"/>
						</td>
					</tr>


					<!--Deklarerar KlientID't från developer-sidan-->
					<tr valign="top">
						<th scope="row"><?php _e( 'Client ID:', 'tibor' ) ?></th>
						<td><input type="text" name="client_id"/></td>
					</tr>

					<!-- deklarerar Klient-secret-koden från developer-sidan-->
					<tr valign="top">
						<th scope="row"><?php _e( 'Client Secret:', 'tibor' ) ?></th>
						<td><input type="text" name="client_secret"
							/></td>
					</tr>





				</table>

				<br>
				<br>

				<!--if-sats som sparar värdena -->
				<input type="submit" class="btn btn-prime" name="submit_feed" value="<?php _e( 'Save' ) ?>">

			</form>
		</div>

		<br>
		<?php DWWP_social_media_plugin::DWWP_instagram_api(); ?>

		<!-- informations-box -->
		<div class="information">

			<h2 id="info"> Information </h2>
			<hr>

			<h3> You can find the instagram json in the table "instagram-values".</h3>
			<h4> How to use Client ID and Client Secrets</h4>
			<p>
				Client ID and Client Secrets <br>
				you can find the Client ID and Client Secret at the <br>developer page on either Instagram or
				facebook.<br>
				Copy these and put these inside the fields.
			</p>
			<hr>



			<h4>Specify feeds. </h4>
			<p> You need to specify if it's either a facebook or a instagram feed.</p>
		</div>
	<?php }

	static function fetch_access_token() {
		include( '/php/instagram-api/access_token.php' );
		?><?php
	}

	//instagram-feed thats added in the system.
	static function DWWP_instagram_api() {

		// if the response code from instagram is accessable, start the method
		if ( ! empty( $_GET['code'] ) ) {

			// get the database table 'instagram settings'
			$instagram_settings = get_option( 'instagram_settings' );
			// get the returned client id.
			$get_client_id     = $instagram_settings['client_id'];
			// get the returned client secret.
			$get_client_secret = $instagram_settings['client_secret'];
			// code used in the code.php file.
			$get_code          = $_GET ['code'];

		// array that saves the values that are returned.
			$args     = array(
				'body' => array(
					'client_id'     => $get_client_id,
					'client_secret' => $get_client_secret,
					'code'          => $_GET['code'],
					//grant type responds with the instagram scope.
					'grant_type'    => 'authorization_code',
					'redirect_uri'  => 'http://tibor.dev/wp-admin/admin.php?page=social-media-instagram-feed'
				)
			);
			// specifies how to get the access-token.
			$url      = 'https://api.instagram.com/oauth/access_token';
			// takes the array and posts it in the url.
			$response = wp_remote_post( $url, $args );


			if ( wp_remote_retrieve_response_code( $response ) === 200 ) {
				$body = wp_remote_retrieve_body( $response );
				$body = json_decode( $body );
				if ( ! empty( $body->access_token ) ) {
					update_option( 'instagram-access-token', $body->access_token );
				}

			}

			?><p>Access-token fetched and saved in the db! <?php DWWP_social_media_plugin::get_json_IG() ?></p> <?php


		}


	}


	// hämtar hem jsontext från webbklienten

	static function get_json_IG() {
		$access_token             = get_option( 'instagram-access-token' );
		$get_json_text_ig         = 'https://api.instagram.com/v1/users/self/media/recent/?access_token=' . $access_token;
		$list_of_json_stuff       = file_get_contents( $get_json_text_ig );
		$get_json_text_ig_decoded = json_decode( $list_of_json_stuff, true );


		foreach ( $get_json_text_ig_decoded as $value ) {
			foreach ( $value as $instagramInfo ) {
				$caption   = $instagramInfo ['caption']['text'];
				$date      = $instagramInfo['created_time'];
				$realdate  = date( 'd/m/Y', $date );
				$checkup   = $instagramInfo['type'];
				$img_url   = $instagramInfo['images']['standard_resolution']['url'];
				$video_url = $instagramInfo['videos']['standard_resolution']['url'];


				 // instagram checkup on values.

				if ( $checkup === 'video' ) {
					if ( $caption != null ) {

						$videoSetting[] = array(
							'url'      => $video_url,
							'realdate' => $realdate,
							'caption'  => $caption,

						);
					} else {
						$videoSetting[] = array(
							'url'      => $video_url,
							'realdate' => $realdate,
						);
					}

				} elseif ( $checkup === 'image' ) {

					if ( $caption != null ) {


						$imageSetting[] = array(
							'url'      => $img_url,
							'realdate' => $realdate,
							'caption'  => $caption
						);
					} else {
						$imageSetting[] = array(
							'url'      => $img_url,
							'realdate' => $realdate,
						);
					}
				}


			}

		} //här slutar loop.


		$instagramResults = "Instagram_results";


		$igArrays = array_merge( $imageSetting, $videoSetting );

		usort( $igArrays, function ( $a, $b ) {
			return $b['realdate'] - $a['realdate'];
		} );


		update_option( $instagramResults, $igArrays );



		echo "<pre>" . print_r( json_encode( $igArrays ) ) . "</pre>";

	}


	// settings-area of the plugin.


	static function page_edit_feed() {
		?>

		<div class="wrap">
			<h2> Edit existing feed </h2>

			<br>
			<h4 id="rubrik"> Enter the feedname you wish to edit</h4>

			<?php
			$value = get_option( 'instagram_settings' );
			foreach ($value as $X)
			{
				?> <p> <?php echo $X ?> </p> <?php
			}



			?>

			<select id="long">
				<option> <?php echo $value['feed_name'] ?> </option>


			</select>
			<hr>
			<br>
			<!--Deklarerar KlientID't från developer-sidan-->

			<h4 id="rubrik"> If you wish to update the client-ID: </h4>


			<tr valign="top">
				<th scope="row"></th>
				<td><input name="client_id" id="client_id"/></td>
			</tr>

			<br>
			<br>


			<button id="button" onclick="update_access_token()">Update client-id</button>


			<br><br>
			<hr>







			<!--Ta bort en feed-->
			<h4 id="rubrik">Delete facebook-JSON-feed? </h4>

			<form action="" method="post">
				
			<button id="facebookdeletebutton">Delete facebook-feed</button>
		</form>
			<?php if(isset($_GET["facebookdeletebutton"]))
			{
				delete_option('page-id'); delete_option('facebook-json-result'); delete_option('facebook-access-token');

			echo 'deleted!';
			} ?>

			<br><br>


		</div>
		<br>
		<div class="information">

			<h2 id="info"> Information </h2>
			<hr>


			<h4>Feed-selection:</h4>
			<p>


				You need to choose the feed you want to manipulate <br>
				simply select the field you wish to manipulate in the drop-down menu.
			</p>
			<hr>



			<h4> Updating the client-id.</h4>
			<p> If your client id has run out, simply paste in the new client id in the field <br>
				and press the button.
			</p>
			<hr>





			<h4>Delete feed. </h4>
			<p> This will permanently delete the feed from the database. Only press this if you are sure to delete the
				feed.</p>

		</div>





	<?php }

	static function page_facebook_feed() {
		/**
		 * This is where facebook starts.
		 *
		 */
		?><br>

<div class="information2">
	<h2 id="info"> Information </h2>
	<hr>
	<h4> how the facebook JSON-feed works:</h4>
	<p>To use this and convert it to JSON-text, you need to be an administrator of a facebook-page to get the feed.
	first of all, you need to press the link that says "authenticate facebook".
	After you've authenticated your facebook to the plugin, a text-field should appear that requires you to enter a page-id which is found<br>
		in the page settings. Copy the ID and press the submit button. After that, the json-feed should be saved in the database. </p>

	<h4> Database tables:</h4>
	<p> The information specified and given is saved on a local database with the tablenames:
	<p id = "underline"> facebook-access-token:</p> in which the access token given from facebook is stored.
	<p id = "underline">page-id:</p> the id in which was specified in the text field.
	<p id= "underline">facebook-JSON-result:</p> the result and converted values from the facebook feed.



	</p>
</div>

	<div class="wrap">

		<?php if(isset($_POST['submit'])){

			update_option('page-id', $_POST['page-id']);
			wp_redirect(  'http://tibor.dev/wp-admin/admin.php?page=facebook-feed-login&page-id=' . get_option('page-id') );
			exit;
		} ?>

		<h2> Authenticate facebook and enter page id. </h2>

		<h4> After you authenticate your facebook, please enter the page id. </h4>

		<!-- some validation -->
		<b>Database status:</b>
		<p>Database status - JSON: <?php if( get_option('facebook-json-result') == true){
				echo 'The JSON-result is now in the database!';
			}
			else{
				echo 'JSON-data not inserted in the database...yet.';
			}?></p>
		<p> Database status - Access-token: <?php if(get_option('facebook-access-token') == true){
				echo 'the access-token is now in the database!';
			}
			else {
				echo 'The access-token is not in the database...yet.';
			}?> </p>
		<p> Database status - Page ID: <?php if(get_option('page-id') == true) {
			echo 'the Page-ID is now in the database!';
			}
			else{
				echo 'The Page-ID is not in the database..yet.';
			}
			 ?></p>

		<?php

			// works with the facebook-page.

		include_once __DIR__ . '/facebook-php-sdk-v4-master/src/Facebook/autoload.php';

		$facebook_app_id     = '991325357570427';
		$facebook_app_secret = 'bf44bc157329b55c4914f327498a1968';
		$url                 = ( ! empty( $_SERVER['HTTPS'] ) ) ? 'https://' . $_SERVER['HTTP_HOST'] : 'http://' . $_SERVER['HTTP_HOST'];

		$fb = new Facebook\Facebook( [
			'app_id'                => $facebook_app_id,
			'app_secret'            => $facebook_app_secret,
			'default_graph_version' => 'v2.5',
		] );

		if ( get_option('facebook-access-token') ) {

			if ( empty( $_GET['page-id'] ) ) {
				die();

			} elseif ( $_GET['page-id'] ) {


			// Facebook is authenticated, get required feed.


						$pageID      = $_GET['page-id'];
						$accessToken = get_option( 'facebook-access-token' );
						$response    = $fb->get( '/' . $pageID . '/feed?fields=message,created_time,attachments,link,comments.limit(1).summary(true),likes.limit(1).summary(true)', $accessToken );

						if ( 200 === $response->getHttpStatusCode() ) {
							$jsonfacebook = json_decode( $response->getBody(), true );
							foreach ( $jsonfacebook as $value ) {


								foreach ( $value as $item ) {
									$picturearray = null;
									$photopath    = $item['attachments']['data'][0];
									if ( isset( $photopath['subattachments'] ) ) {
										$photopath = $photopath['subattachments']['data'];
									} elseif ( isset( $photopath['media'] ) ) {
										$photopath = $item['attachments']['data'];
									} else {
										$photopath = null;
									}

									if ( $photopath != null ) {
										foreach ( $photopath as $picture ) {
											$picturearray[] = $picture['media']['image']['src'];
										}
									} else {
										$picturearray = null;
									}


									if ( $item['message'] == 'h' ) {
										break;
									}
									$FBarray[] = array(
										'message' => $item['message'],
										'date'    => $item['created_time'],
										'link'    => $item['link'],
										'photos'  => $picturearray


									);


								}
							}

							update_option( 'facebook-json-result', $FBarray );

							die();
						}
				}



		} elseif ( ! empty( $_GET['code'] ) ) {

			// Get Facebook Token
			$helper = $fb->getRedirectLoginHelper();
			try {
				$accessToken = $helper->getAccessToken();
				//update_option('facebook-access-token', $accessToken);
			} catch ( Facebook\Exceptions\FacebookResponseException $e ) {
				// When Graph returns an error
				echo '<p>Graph returned an error: ' . $e->getMessage() . '</p>';
				exit;
			} catch ( Facebook\Exceptions\FacebookSDKException $e ) {
				// When validation fails or other local issues
				echo '<p>Facebook SDK returned an error: ' . $e->getMessage() . '</p>';
				exit;
			}



			if ( isset( $accessToken ) && $_GET['status']=== 'approved' ) {
				// Logged in!
				update_option('facebook-access-token',  $accessToken->getValue());

				echo '<p>Facebook has responded!</p>';

				?>
				<form action="" method="post">
					please enter the Page-ID: <input type="text" name="page-id"><br>

					<input type="submit" name="submit">

				</form>
				<?php
			}


		} elseif ( !get_option('facebook-access-token') ) {
			$helper      = $fb->getRedirectLoginHelper();
			$permissions = [ ]; // optional
			$loginUrl    = $helper->getLoginUrl( $url . '/wp-admin/admin.php?page=facebook-feed-login&status=approved', $permissions );

			echo '<a href="' . $loginUrl . '">Authenticate Facebook</a>';

		}










	}


}
?> </div>  <?php


DWWP_social_media_plugin::init();
?>

