<?php
	
class AllSpark{
	/**  @internal	**/
	private $version = 0.01;
	private $override_url_routing = false;
	
	/** 
	The __constuct method bootstraps the entire plugin. It should not be modified. It is possible to override it, but you probably don't want to
	
	@internal	**/
	public function __construct(){		
		add_action('init', array($this, 'init'));
		
		//if the main plugin file isn't called index.php, activation hooks will fail
		register_activation_hook( dirname(__FILE__) . '/index.php', array($this, 'pluginDidActivate'));
		register_deactivation_hook( dirname(__FILE__) . '/index.php', array($this, 'pluginDidDeactivate'));
	}
	
	/**
	Add the rewrite rules for APIs
	
	@internal	**/
	function pluginDidActivate(){
		flush_rewrite_rules();
	}
	
	/**
	Clean up the rewrite rules when deactivating the plugin

	@internal	**/
	function pluginDidDeactivate(){
		flush_rewrite_rules();
	}
	
	/**
	Attaches a method on the current object to a WordPress hook. By default, the method name is the same as the hook name. In some cases, this behavior may not be desirable and can be overridden.
	
	@param string $name The name of the action you wish to hook into
	@param string $callback [optional] The class method you wish to be called for this hook
	 
	*/
	protected function add_action($name, $callback = false){
	
		if(!$callback){
			$callback = $name;
		}
	
		if(method_exists($this, $callback)){
			add_action($name, array($this, $callback));
		}
	}
	
	/**
	Attaches a method on the current object to a WordPress ajax hook. The method name is ajax_[foo] where `foo` is the action name	*
	
	@param string $name The name of the action you wish to hook into
	 
	*/
	protected function listen_for_ajax_action($name, $must_be_logged_in = true){
		
		if($must_be_logged_in !== true){
			add_action( 'wp_ajax_nopriv_' . $name, array($this, 'handle_ajax_action'));
		}
		
		add_action( 'wp_ajax_' . $name, array($this, 'handle_ajax_action'));
	}
	
	/**
	Internal forwarding of AJAX requests from WordPress into this class
	
	@internal	**/
	function handle_ajax_action(){
		$this->call($_REQUEST['action'], $_REQUEST);
	}
			
	/*
	**
	**	WP Callbacks
	**
	*/
	
	/**
	Handles callbacks from the `init` action
	
	@internal **/
	public function init(){

		$this->add_action('admin_menu');
		$this->add_action('admin_init');
		$this->add_action('save_post');
		$this->add_action('add_meta_boxes');
		$this->add_action('load-themes.php', 'themeDidChange');
		
		$this->_set_up_forwarding_methods();
	}
	
	/**
	Handles callbacks from the `admin_menu` action. Fires off events to register scripts and styles for the admin area
	
	@internal	**/
	public function admin_menu(){
		$this->call('add_admin_pages');
		
		foreach(array(
			'register_scripts',
			'register_styles'
		) as $command){		
			$this->call($command);
		}
	}
	
	/**
	Internal command dispatching
	
	@internal	**/
	private function call($command, $params = null){
		if(method_exists($this, $command)){
			if(is_array($params)){
				return call_user_func_array(array($this, $command), $params);
			}
			else{
				return call_user_func(array($this, $command));
			}
		}
		else{
			return false;
		}
	}
	
	/** @internal	**/
	private function _set_up_forwarding_methods(){
		add_action( "admin_enqueue_scripts",  array($this, '_enqueue_items_for_url'));
	}
	
	/**
	Enables protected access to methods that 
	
	@internal	**/
	public function _enqueue_items_for_url($url){
		$this->call('enqueue_items_for_url', array($url));
	}
}
