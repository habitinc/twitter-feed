<?php

if(!class_exists('AllSpark')){
	require_once('AllSpark.class.php');
}

if(!class_exists('TwitterOAuth')){
	require_once('lib/twitteroauth.php');
}

if(!class_exists('TwitterEntitiesLinker')){
	require_once('lib/twitter_entities_linker.php');
}

class TwitterTimelinePlugin extends AllSpark
{
	var $redirect_url_path = 'twitter_auth';
	
	public function __construct(){
		parent::__construct();
		$this->listen_for_ajax_action('twitter_auth');
		
		$this->clientID		= get_option($this->clientIDKey);
		$this->clientSecret	= get_option($this->clientSecretKey);
		$this->client_auth	= get_option($this->clientAuthKey);
		
		$this->authkey 		= get_option($this->clientAuthKey);
		$this->handle		= get_option($this->handle);
	}
		
	public function init(){
		parent::init();
				
		if(isset($_GET['start_auth'])){
		
			// Populate the API Key
			$apiKey = $_POST['apiKey'];
			$secretKey = $_POST['secretKey'];
			update_option($this->apiKeyKey, $apiKey);
			update_option($this->apiSecretKey, $secretKey);
			
			// Populate the Client Info
			$clientID = $_POST['clientID'];
			$clientSecret = $_POST['clientSecret'];
			update_option($this->clientIDKey, $clientID);
			update_option($this->clientSecretKey, $clientSecret);
			
			/* Get temporary credentials. */

			$request_token = $this->connection('machine')->getRequestToken($this->get_redirect_url());
			update_option( $this->oauthTokenKey, $request_token['oauth_token'] );
			update_option( $this->oauthTokenSecretKey, $request_token['oauth_token_secret'] );
			
			$url = $this->connection('machine')->getAuthorizeURL($request_token['oauth_token']);
			
			wp_redirect( $url );
			exit;
		}
				
		if(isset($_GET['viewtimeline'])){
			wp_redirect( admin_url( 'options-general.php?page=twitter-timeline&timeline=' . $_POST['handle'] ) );
		}
		
		if(isset($_GET['disconnect_auth'])){
			$this->reset();
			wp_redirect( admin_url( 'options-general.php?page=twitter-timeline' ) );
		}
		
	}
	
	private $apiKeyKey			= 'hbt_TwitterTimelinePlugin_apiKey';
	private $apiSecretKey		= 'hbt_TwitterTimelinePlugin_apiSecretKey';
	private $clientIDKey		= 'hbt_TwitterTimelinePlugin_accessToken';
	private $clientSecretKey	= 'hbt_TwitterTimelinePlugin_accessTokenSecret';
	
	private $oauthTokenKey		= 'hbt_TwitterTimelinePlugin_oauth_token';
	private $oauthTokenSecretKey= 'hbt_TwitterTimelinePlugin_oauth_secret';
	private $handleKey			= 'hbt_TwitterTimelinePlugin_handle';
	
	private $authorizedUserKey = 'hbt_TwitterTimelinePlugin_authorized_user';

	
	public function pluginDidActivate(){
		parent::pluginDidActivate();
		
		add_option( $this->apiKeyKey, '', '', 'yes' );
		add_option( $this->apiSecretKey, '', '', 'yes' );
		add_option( $this->clientIDKey, '', '', 'yes' );
		add_option( $this->clientSecretKey, '', '', 'yes' );

		add_option( $this->oauthTokenKey, '', '', 'yes' );
		add_option( $this->oauthTokenSecretKey, '', '', 'yes' );
		add_option( $this->handleKey, '', '', 'yes' );
	}
	
	public function pluginDidDeactivate(){
		parent::pluginDidDeactivate();
		$this->reset();	
	}
	
	private function reset(){
		delete_option( $this->apiKeyKey );
		delete_option( $this->apiSecretKey );
		delete_option( $this->clientIDKey );
		delete_option( $this->clientSecretKey );
		
		delete_option( $this->oauthTokenKey );
		delete_option( $this->oauthTokenSecretKey );
		delete_option( $this->handleKey );
		
		delete_transient( $this->authorizedUserKey );
	}
	
	public function get_redirect_url(){
		return admin_url('/admin-ajax.php?action=' . $this->redirect_url_path);
	}
	
	public function admin_menu(){
		add_options_page( 'Twitter Timeline', 'Twitter Timeline', 'moderate_comments', 'twitter-timeline', array(&$this, 'do_admin_ui'));
	}
		
	public function fetch_tweets($screen_name = 'twitter', $skipcache = false){
		
		if( false === get_option( $this->oauthTokenKey ) ){
			return array();
		}
		
		if(!get_transient( __CLASS__ . __FUNCTION__ . $screen_name ) || $cacheFor !== false){
			$tweets = $this->connection('user')->get('statuses/user_timeline', array(
				'screen_name'		=> $screen_name,
				'include_rts'		=> false,
				'exclude_replies'	=> true,
				'count'				=> 200
			));
			
			set_transient( __CLASS__ . __FUNCTION__ . $screen_name, $tweets, $cacheFor ); 		
		}
		
		return get_transient( __CLASS__ . __FUNCTION__ . $screen_name );
	}
	
	public function do_admin_ui(){
		require_once('ui/main.ui.php');
	}
	
	protected function get_client_details(){
		return get_option($this->clientDetailsKey);
	}
	
	private function connection($user_or_machine = false){
	
		if(!$user_or_machine){
			throw new Exception("You must specify user key or machine key");
		}
	
		if($user_or_machine == 'machine'){
			return new TwitterOAuth(get_option($this->apiKeyKey), get_option($this->apiSecretKey));
		}
		
		if($user_or_machine == 'user'){
			$conn = new TwitterOAuth(
				get_option( $this->apiKeyKey ),
				get_option( $this->apiSecretKey ),
				get_option( $this->oauthTokenKey ),
				get_option( $this->oauthTokenSecretKey )
			);
			
			if(!get_transient( $this->authorizedUserKey )){
				sleep(2);
				set_transient( $this->authorizedUserKey, $conn->get('account/verify_credentials'), 86400 ); 
			}
			
			return $conn;
		}
	}
	
	public function twitter_auth(){

		if(isset($_REQUEST['denied'])){
			set_transient( 'twitter-timeline-auth-error', $_REQUEST['denied'], YEAR_IN_SECONDS );
		}
		
		if(isset($_REQUEST['oauth_verifier'])){
			$tokens = $this->connection('user')->getAccessToken($_REQUEST['oauth_verifier']);
			
			update_option( $this->oauthTokenKey, $tokens['oauth_token'] );
			update_option( $this->oauthTokenSecretKey, $tokens['oauth_token_secret'] );
		}
		
		wp_redirect( admin_url( 'options-general.php?page=twitter-timeline' ) );
	}
}
?>