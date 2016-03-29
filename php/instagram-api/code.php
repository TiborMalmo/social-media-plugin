<?php
$DB = get_option('instagram_settings');
$url = 'https://api.instagram.com/oauth/authorize/?client_id='.$DB['client_id'].'&redirect_uri=http://tibor.dev/wp-admin/admin.php?page=social-media-instagram-feed&response_type=code';