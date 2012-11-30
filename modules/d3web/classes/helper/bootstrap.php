<?php defined('SYSPATH') OR die('No direct access allowed.');

interface IDefaultAction {
	public function defaultAction();
}

class Helper_Bootstrap implements IDefaultAction{
	/*singleton*/
	private static $ins;
	public static function instance()
	{
		if (!self::$ins){
			self::$ins = new Helper_Bootstrap();
		}
		return self::$ins;
	}

	private $imp;
	private function __construct(){
		$this->setDefaultController($this);
	}

	public function setDefaultController(IDefaultAction $imp){
		$this->imp = $imp;
	}

	public function use_default_controller(){
		try{
			$this->imp->defaultAction();
		}catch(ReflectionException $e){
			//nothing found, 404
			$this->no_default_controller_found();
		}
	}

	//split Controller_Exception into useControllerPage
	public function defaultAction(){
		$this->no_default_controller_found();
	}

	private function no_default_controller_found(){
		echo('<!--- default controller not found. -->');
		$this->handle_404();
	}

  /*
   * This function will be called when a file is not found
   * In our system, a media file may have a default copy for a specific language
   * e.g. media/hk/en/site/swf/0.swf return 404, but "en" file could be retrieve from media/asia/en/site/swf/0.swf
   */
	public function handle_404(){
		//fetch the best language version by Helper_URL::get_default_city_uri

		$path = isset($_SERVER['PATH_INFO'])?$_SERVER['PATH_INFO']:'';
		$referral = @$_REQUEST['referral'];

		if(($referral != 'handle_404') && ((stripos($path, '/swf/') || stripos($path, '/images/'))|| stripos($path, '/css/'))){
		  $path = Helper_URL::get_default_city_uri($path);
		  $path = ltrim($path,'/');

		  $query_string = $_SERVER['QUERY_STRING'];
		  parse_str($query_string, $query_string);
		  $query_string['referral'] = 'handle_404';
		  $url = URL::base(FALSE, TRUE).$path;
		  foreach($query_string as $i => $j){
			  $url = Helper_String::append_query_string($url, $i, $j);
		  }

		  header('Location: '.$url);
		  exit();
		}

		header('HTTP/1.0 404 Not Found');
		echo('404');
		exit();
	}

	public function handle_static_content(Request &$request){
		$city   = $request->param('city'		,Kohana::config(Kohana::$environment.'.city.default'));
		$lang   = $request->param('language'	,Kohana::config(Kohana::$environment.'.language.default'));
		$format = $request->param('format');
		$id		= $request->param('id');

		$controller = $request->controller;
		$action 	= $request->action;

		/*depends on the format, search file use different suggestions */
		$template_suggestions 	= array();
		switch($format){
			case 'php':
				//search the static view
				$template_suggestions[] = "application/views/$city/$lang/$controller/$action.php";
				$template_suggestions[] = "media/$city/$lang/$controller/$action.php";
				$template_suggestions[] = "media/$controller/$action.php";
				$template_suggestions[] = "media/$city/$lang/$controller/$action.html";
				$template_suggestions[] = "media/$controller/$action.html";

				break;
			case 'htm':
			case 'html':
				$template_suggestions[] = "media/$city/$lang/$controller/$action.$format";
				$template_suggestions[] = "media/$controller/$action.$format";
				break;
			default:
				$template_suggestions[] = "media/$city/$lang/$controller/$action/$id.$format";
				$template_suggestions[] = "media/$controller/$action/$id.$format";
		}

		var_dump($template_suggestions);

		foreach($template_suggestions as $suggest){
			if(file_exists(DOCROOT.$suggest)){
				//if php use view
				if(preg_match('/.php$/i',$suggest)){
					echo('static view found');
				}else{
					//other media, redirect
					header('Location: '.URL::site().$suggest.(empty($_SERVER['QUERY_STRING'])?'':'?').$_SERVER['QUERY_STRING']);
				}
				return TRUE;
			}
		}
		return FALSE;
	}
}