<?php
/**  
 * ыводит список акций на экран, для данного региона
 * 
 * @package    controller
 * @subpackage Actions
 * @since      13.10.2010 13:24:53
 * @author     enesterov
 * @category   controller
 */

	namespace Controllers;
	use Models;
	
	
class ControllerActions extends InterfaceTemplate{
	
	/**
	 * время хранения кеша
	 * @var int
	 */
	protected $mtime;
	
	/**
	 * получаем гет запрос и просматриваем значения
	 * точка входа
	 * @param array $array
	 */
	public function index( $array )
	{
		$this->mem_key = 'actions';
		if($array)
			$this->setVar();
			
		$this->actions = "";
		$actions = $this->createActionsList();
		if($actions)
			return $this->displayListActions($actions);
	}
	
	/**
	 * выбираем все акции в регионе
	 * @example sql <pre>
	 * SELECT r.*
	 * FROM reklama
	 * LEFT JOIN reklama_regions rr ON rr.reklama_id=id
	 * WHERE skidki=1 AND rr.region_id=1 AND hidden=0
	 * AND start_date<=now() AND end_date>=now() ORDER BY sort DESC, start_date DESC
	 * </pre>
	 * если задан id то добавляется еще
	 * <pre>...AND end_date>=now() <b>AND id=61</b> ORDER BY ...</pre>
	 */
	private function createActionsList()
	{
		$join = "left join reklama_regions rr on rr.reklama_id=r.id";
		$options = array('select' => "r.*",
						'from' => "reklama as r",
						'joins' => $join,
						'order' => 'sort DESC, start_date DESC',
						'conditions' => array('skidki=1 
											AND rr.region_id= ? 
											AND hidden=0 
											AND start_date<=now() 
											AND end_date>=now()', 
											$this->region_id));

		return $actions = Models\Reklama::find('all', $options);
	}
	
	/**
	 * создаем список акций
	 * @param obj $actions
	 */
	private function displayListActions( $actions )
	{
		//var_dump($actions);
		foreach ($actions as $value)
		{
			$a_time = $value->end_date->format('U');
			if(!$this->mtime || $this->mtime > $a_time)
				$this->mtime = $value->end_date->format('U');
				
			$act = $this->actions->addChild('action');
			$act->addAttribute("start", $value->start_date->format('c'));
			$act->addAttribute("end", $value->end_date->format('c'));
			
			//картинки
			$url = $this->crealeActionImageList($value);
			
			$this->action_id = "";
			//ID акции
			$this->setActionId($url);
			
			$imgs = sprintf('imgs/reklama/ico/%s.jpg', $value->id);
			$act->addChild("action_description", StripTags($value->description));
			$this->displayCategoryAction($act, $url, $value->name, $imgs);
		}
		$this->mem_time = ($this->mtime - time());
	}
	
	/**
	 * собираем ссылку на картинку
	 * @param obj $value
	 */
	private function crealeActionImageList($value)
	{
		preg_match("@^/.*?/@i", $value->url, $urls);
		$url="";
		if($urls)
			$url = $urls[0];
		return $url;
	}
	
	/**
	 * узнаем ИД акции
	 * @param string $url
	 */
	private function setActionId($url)
	{
		$url = str_replace("/", "", $url);
		$id = Models\Actions::find('first', array('select' => 'segment_id', 'conditions' => "segment_name = '$url' "));
		if($id)
			$this->action_id = $id->segment_id;
	}
}