<?php
$feed = $this->fetch_tweets();
$auth_error = get_transient( 'twitter-timeline-auth-error' );
delete_transient( 'twitter-timeline-auth-error' );
?>

<?php if($auth_error !== false): ?>
<div class="error">
	<p>
		<strong>Unable to Authorize App</strong><br />
		Unable to authorize with Twitter. Reference code: <?php echo $auth_error; ?>
	</p>
</div>
<?php endif; ?>

<div class="wrap">
	<h2>Twitter Timeline</h2>
	
	<?php 
	if(!get_option( $this->oauthTokenKey )): ?>

	<p>To get started, you must authorize this website with your twitter account</p>
	
	<form method="post" action="<?php echo admin_url('options-general.php?page=twitter-timeline&start_auth'); ?>">
	
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row"><label for="apiKey">App API Key</label></th>
					<td>
						<input name="apiKey" type="text" id="apiKey" value="<?php echo get_option($this->apiKeyKey); ?>" class="regular-text">
						<p class="description">This valid is automatically generated for you when you create a new Twitter app.</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="secretKey">App API Secret</label></th>
					<td>
						<input name="secretKey" type="text" id="secretKey" value="<?php echo get_option($this->apiSecretKey); ?>" class="regular-text">
						<p class="description">This valid is automatically generated for you when you create a new Twitter app.</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="clientID">Access Token</label></th>
					<td>
						<input name="clientID" type="text" id="clientID" value="<?php echo get_option($this->clientIDKey); ?>" class="regular-text">
						<p class="description">Once you've created your Twitter app, you'll generate an access token set. You'll find this value there.</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="clientSecret">Access Token Secret Secret</label></th>
					<td>
						<input name="clientSecret" type="text" id="clientSecret" value="<?php echo get_option($this->clientSecretKey); ?>" class="regular-text">
						<p class="description">Once you've created your Twitter app, you'll generate an access token set. You'll find this value there.</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="redirect_uri">Callback URL</label></th>
					<td>
						<input name="redirect_uri" type="text" id="redirect_uri" value="<?php echo $this->get_redirect_url(); ?>" disabled="disabled" class="regular-text">
						<p class="description">You'll need to enter this value when you're creating your Twitter app.</p>
					</td>
				</tr>
			</tbody>
		</table>
	
		<input type="submit" class="button button-primary" value="Authorize App"/>
	
	</form>
	<?php else: ?>
		<?php $client_details = get_transient( $this->authorizedUserKey ); ?>
			
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row" valign="middle"><label>Connected As</label>
					</th>
					<td style="width: 250px;">
						<img src="<?php echo str_replace('_normal', '_400x400', $client_details->profile_image_url_https); ?>" width="75" height="75" />
						<br /><strong><?php echo $client_details->name; ?></strong>
					</td>
					<td>
						<form method="post" action="<?php echo admin_url('options-general.php?page=twitter-timeline&disconnect_auth'); ?>">
							<input type="submit" class="button button-secondary" value="Disconnect" />
						</form>
					</td>
				</tr>
			</tbody>
		</table>	
		
		<div style="margin-top: 45px;">
		<?php if(!isset($_GET['timeline'])): 
			
			
		?>

		
		<h3>Feed Preview: </h3>
		<form method="post" action="<?php echo admin_url('options-general.php?page=twitter-timeline&viewtimeline'); ?>">
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row"><label for="hashtag">Handle</label></th>
					<td>
						<input name="handle" type="text" id="handle" value="" class="regular-text">
						<p class="description">Enter the timeline you'd like to preview</p>
					</td>
				</tr>
			</tbody>
		</table>
	
		<input type="submit" class="button button-primary" value="View Timeline" />
		</form>
		</div>
		
		<?php else: 
			
			$user = $this->connection('user')->get('users/show', array(
				'screen_name' => $_GET['timeline']
			));

		?>
		
		<h3>Currently viewing tweets for: <?php echo $user->name; ?><a class="button button-secondary" style="margin-left: 24px;" href="<?php echo admin_url('options-general.php?page=twitter-timeline'); ?>">Close</a>
</h3>
		

		
		<style type="text/css">
		/** Embedded Tweets */
 
		blockquote.twitter-tweet {
		  display: block;
		  padding: 16px;
		  margin: 10px 0;
		  max-width: 468px;
		 
		  border: #ddd 1px solid;
		  border-top-color: #eee;
		  border-bottom-color: #bbb;
		  border-radius: 5px;
		  box-shadow: 0 1px 3px rgba(0,0,0,0.15);
		 
		  font: bold 14px/18px Helvetica, Arial, sans-serif;
		  color: #000;
		  
		  background-color: white;
		}
		 
		blockquote.twitter-tweet p {
		  font: normal 18px/24px Georgia, "Times New Roman", Palatino, serif;
		  margin: 0 5px 10px 0;
		}
		 
		blockquote.twitter-tweet a[href^="https://twitter.com"] {
		  font-weight: normal;
		  color: #666;
		  font-size: 12px;
		}
		 
		/** Timeline */
		 
		a.twitter-timeline {
		 
		  /* Buttonish */
		  display: inline-block;
		  padding: 6px 12px 6px 30px;
		  margin: 10px 0;
		 
		  border: #ccc solid 1px;
		  border-radius: 3px;
		  background: #f8f8f8 url(//platform.twitter.com/images/bird.png) 8px 8px no-repeat;
		 
		  /* Text */
		  font: normal 12px/18px Helvetica, Arial, sans-serif;
		  color: #333;
		 
		  white-space: nowrap;
		}
		 
		a.twitter-timeline:hover,
		a.twitter-timeline:focus {
		  background-color: #dedede;
		}
		 
		/* Colour Highlight for keyboard navigation */
		a.twitter-timeline:focus {
		  outline: none;
		  border-color: #0089cb;
		}
		</style>
		<div style="height: 500px; overflow: scroll; width: 515px;">
		
		<?php
			$tweets = $this->fetch_tweets($_GET['timeline']);
			foreach($tweets as $tweet):
		?>

		<blockquote class="twitter-tweet">
			<p><?php echo $tweet->text; ?>
			</p>— <?php echo $tweet->user->name; ?> (@<?php echo $tweet->user->screen_name; ?>) <a href="https://twitter.com/<?php echo $tweet->user->screen_name; ?>/statuses/<?php echo $tweet->id; ?>" class="ext" target="_blank"><?php echo date('M d, Y', strtotime($tweet->created_at)); ?></a><span class="ext"></span>
		</blockquote>
		<?php
		endforeach; ?>
				</div>

		<?php endif; ?>
	<?php endif; ?>
	
</div>
