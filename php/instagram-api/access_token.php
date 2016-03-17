<?php
/**
 * Created by PhpStorm.
 * User: Tibor
 * Date: 2016-03-11
 * Time: 11:05
 */

$DB = get_option('instagram_settings');
$access_token = 'https://api.instagram.com/oauth/access_token?client_id='.$DB['client_id'].'&client_secret='.$DB['client_secret'].'&grant_type=authorization_code&code='.$DB['code'];
