<?php
/*
Plugin Name: Twitter Timeline
Plugin URI: https://github.com/habitinc/twitter-feed
Description: Allows authenticating for and retrieving a Twitter timeline
Version: 1.0
Author: Habit
Author URI: http://habithq.ca
*/

if ( ! defined('ABSPATH') ) {
	die('Please do not load this file directly.');
}

require_once 'TwitterTimelinePlugin.class.php';

$twitter_plugin = new TwitterTimelinePlugin();

function fetch_tweets($screen_name = 'twitter', $skipcache = false){
	global $twitter_plugin;
	return $twitter_plugin->fetch_tweets($screen_name, $skipcache);
}
