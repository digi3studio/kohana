<?php defined('SYSPATH') OR die('No direct access allowed.');

class Controller_Page extends Controller_Common {
	public function action_static(){
		$campaign_shortname	= $this->request->param('campaign');
		$page_shortname 	= $this->request->param('id');
		$city	 			= $this->request->param('city');
		$language			= $this->request->param('language');

	    //@FIXME(franfran): hard coded skip age!
	    switch($campaign_shortname){
	      case 'arthk12':
	        setcookie('age_verified', 'verified', 0, '/');
	        break;
	      default:
	        if(parent::age_verified()==FALSE)return;
	        break;
	    }

		$campaign 			= Helper_page::get_campaign($campaign_shortname,$city);

		//try find the default campaign page.
		$page = ($page_shortname=='')?
				$campaign->pages->order_by('order','ASC')->find():
				$campaign->pages->where('shortname','=',$page_shortname)->find();			

		if(empty($page->id)){
			//page is not in database, use the page in filesystem as template
			$this->template->admin_menu	= '';
			$this->template->body 		= $this->get_page_view($city,$language,$campaign_shortname,$page_shortname)->render();
			return;
		}else{
			if($page_shortname==''){
				$page_shortname = $page->shortname;
			}

			//member only?
			if($page->member == 1){
				$member = new Member();
				if(!$member->logged_in()){
					$destination = urlencode(Url::base(true, true).dirname(Request::instance()->uri()));
					Request::instance()->redirect('member/restricted?destination='.$destination);
					exit();
				}
			}
		}

		$page_type			= $page->pagetype_id;
		$page_layout		= $page->layout_id;
		$pagevalues 		= $page->pagefieldvalues->find_all();

		$this->template->admin_menu	= '';
		$this->template->suggest_content 		= View::factory($this->request->param('city').'/'.$language.'/content_row');
		$this->template->suggest_content->city  = $this->request->param('city');
		$this->template->show_tick_row = TRUE;
		$tpl = $this->get_page_view($city,$language,$campaign_shortname,$page_shortname,$page_type,$page_layout, $pagevalues);
		$tpl->page_id = $page->id;
		$this->template->body 		= $tpl->render();
	}

	public function get_page_view($city_shortname,$language,$campaign_shortname,$page_shortname='', $page_type=0, $page_layout=0, $pagevalues=array()){
		//make the page suggestion
	
		$tpl = View::factory(
			Helper_Page::suggestion(
				$page_shortname, 
				$campaign_shortname,
				$page_type,
				$page_layout,
				$city_shortname,
				$language
			));

		$values = array();
		foreach($pagevalues as $pagevalue){
			$values[$pagevalue->key] = $pagevalue->value;
		}

		//read the template and pass the keys to the page.
		$tpl->values 					= $values;
		$tpl->campaign_shortname 		= $campaign_shortname;
		$tpl->page_shortname			= $page_shortname;
		$tpl->city						= $city_shortname;
		$tpl->language					= $language;

		return $tpl;
	}
}
