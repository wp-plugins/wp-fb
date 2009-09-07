<?php
/*
Plugin Name: Wp-Feedburner
Plugin URI: http://sparun.in/wp-feedburner/
Description: Wp-Feedburner is a plugin which deals with everything Feedburner.It works on Both old and new feedburner URL.
Version: 0.2
Author: S.P.Arun
Author URI: http://sparun.in/

==== VERSION HISTROY ====
V0.1 	- Release Version
V0.2 	- Bug Fixes

==== COPYRIGHT ====
Copyright 2009  S.P.Arun  (email : sparun.in@gmail.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
====================
*/

add_action('admin_menu', 'wp_fb' );
add_action('admin_head', 'wp_fb_show_stats' );
register_activation_hook(__FILE__, 'wp_fb_activate' );

function wp_fb_activate()
{
	add_option('wp_fb_uri', '');
	add_option('wp_fb_old', 0);
}
	
function wp_fb() {	
	//Add options page
	add_options_page('Wp Feedburner Settings', 'WP-Feed Burner', 8, __FILE__, 'wp_fb_settings');
}

function wp_fb_show_stats(){
	$feeduri = get_option('wp_fb_uri');
	$old = get_option('wp_fb_old');
	
	if($old == 'on'){
		//This is the old Feedburner url
		$url = "http://api.feedburner.com/awareness/1.0/GetFeedData?uri=". $feeduri;
	} else {
		//This is the new Google Feedburner url
		$url="https://feedburner.google.com/api/awareness/1.0/GetFeedData?uri=". $feeduri;
	}
	
	if(phpversion() >= '5.2.0') {
		try {
			$ch = curl_init(); //Use cURL
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_URL, $url);
			$data = curl_exec($ch);
			curl_close($ch);
			$xml = new SimpleXMLElement($data); //Read the returned XML
			$count = $xml->feed->entry['circulation']; //Get our subscriber count
			
			if($count > 0){
				?>
				<script type="text/javascript">
				jQuery(document).ready(function(){
					jQuery('#dashboard_right_now table tbody').append('<tr><td class="first b b-feed"><a href="http://feeds2.feedburner.com/<? echo $feeduri; ?>"><? echo $count; ?></a></td><td class="t feed"><a href="http://feeds2.feedburner.com/<? echo $feeduri; ?>">FeedBurner Subscribers</a></td></tr>');
				});
				</script>
				<?
			} else {
				?>
				<script type="text/javascript">
				jQuery(document).ready(function(){
					jQuery('#dashboard_right_now table tbody').append('<tr><td class="first b b-feed"><a href="http://feeds2.feedburner.com/<? echo $feeduri; ?>">0</a></td><td class="t feed"><a href="http://feeds2.feedburner.com/<? echo $feeduri; ?>">FeedBurner Subscribers</a></td></tr>');
				});
				</script>
				<?
			}
		}
		catch(Exception $e) {}
	}
}

function wp_fb_settings(){
	global $wpdb;
	
	if(isset($_POST['feeduri'])){
		update_option('wp_fb_uri', $_POST['feeduri']);
		update_option('wp_fb_old', $_POST['old']);
		?>
		<script type="text/javascript">
		window.location = "<?=$_SERVER['PHP_SELF'].'?page=wp-fb/wp-fb.php&update=true'?>";
		</script>
		<?
	}

	if($_GET['update']) echo '<div class="updated"><p><strong>'.__('Settings saved').'</strong></p></div>';
	
	$feeduri = get_option('wp_fb_uri');
	$old = get_option('wp_fb_old');
	?>
	<div class="wrap">
		<div id="icon-options-general" class="icon32"><br/></div>
		<h2>Wp-FeedBurner Settings</h2>
		<form method="post" action="<?=$_SERVER['PHP_SELF'].'?page=wp-fb/wp-fb.php'?>">
		<table class="form-table">
			<tbody>
				<tr>
					<th><label for="feeduri">Enter your Feedburner Id</label></th>
					<td>
						<input id="feeduri" type="text" name="feeduri" value="<?php echo $feeduri; ?>"  />
						<span class="setting-description">The part that comes after the Feeburner URL.(If your Feed is http://feeds2.feedburner.com/s_p_arun or http://feeds.feedburner.com/s_p_arun , then "s_p_arun" is your feedburner ID) </span>
					</td>
				</tr>
				<tr>
					<th><label for="old">Check this Box If you are using old feedburner URL</label></th>
					<td>
						<input id="old" type="checkbox" name="old" <? if($old == 'on'){ echo 'checked="checked"'; } ?>  />
					</td>
				</tr>
			</tbody>
		</table>
		<p class="submit"><input type="submit" name="Submit" value="Save Changes" class="button-primary" /></p>
		</form>
	</div>
	<?
}
?>