<?php

defined('SYSPATH') or die('No direct script access.');

//Common class for displaying template
class Controller_Common extends Controller_Template {
    public $template = 'template';

    public function __construct(Kohana_Request $request, $response) {
        parent::__construct($request);
		//default title
    }

    public function before() {
        parent::before();
        if ($this->auto_render){
            // Initialize empty values
            $this->template->body = '';

			// css reset
			Helper_Template::instance()->add_style('media/css/cssreset.css');
			// css text only
			Helper_Template::instance()->add_style('media/css/text.css');

/*			if($this->request->user_agent('wap')){
				//not using modern browser.
				//do not render css layout/
				return;
			}*/

			// css layout
			//share style for mobile and desktop;
			Helper_Template::instance()->add_style('media/css/s.css');
			//mobile version setup
			if($this->request->user_agent('mobile')){
				$agent = $this->request->user_agent('mobile');
				// mobile style

				switch ($agent){
					case ('iPad'):
						Helper_Crossbrowser::load_template_style('media/css/d.css');
					case ('BlackBerry'):
					case ('Android'):
					case ('iPhone'):
					case ('iPod'):
						Helper_Template::instance()->add_meta('<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=NO" />');
						Helper_Template::instance()->add_meta('<meta name="apple-mobile-web-app-capable" content="yes"/>');
					default:
						Helper_Template::instance()->add_style('media/css/m.css');
				}
			}else{
				// 960 grid system for desktop
				Helper_Crossbrowser::load_template_style('css/grid.css');
				Helper_Crossbrowser::load_template_style('css/d.css');
			}

			Helper_Crossbrowser::load_template_script('js/jquery-1.4.2.min.js');
		}
    }

    public function after() {
		if ($this->auto_render){
			//run the template body, add css, js, title.. etc
			if(gettype($this->template->body)!='string')$this->template->body->render();

			$template_helper = Helper_Template::instance();
			if(!empty($template_helper->title))$this->template->title = $this->template->title.': '.$template_helper->title;
		}
		parent::after();
    }

	protected function age_verified(){
		//configurable age verify.
		//if disabled age verification, always return true;
		if(Kohana::config(Kohana::$environment.'.age_verify')==FALSE){
			return TRUE;
		}

		$member = new Member();
		//member menu
		if($member->logged_in()){
			return TRUE;
		}
		
		if(empty($_COOKIE['age_verified'])){
			$tpl_age_verify = View::factory('age');
			
			$this->template->body = $tpl_age_verify->render();
			return FALSE;
		}
		return TRUE;
	}
}