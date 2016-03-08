<?php
$DB = get_option('instagram_settings');
$url = 'https://api.instagram.com/oauth/authorize/?client_id='.$DB['client_id'].'&redirect_uri=http://tibor.dev/wp-admin/admin.php?page=social-media-plugin%2Fsocial_media_plugin.php&response_type=code';