<?php
/**
  * контроллер обработки опросов
  * 
  * @package    Polls
  * @subpackage Template
  * @since      04.05.2011 10:10:11
  * @author     enesterov
  * @category   controller
  */

	namespace Controllers;
	use Models;
	use Template;
	
class ControllerPolls extends Template\Template
{
	
	/**
	 * номер опроса
	 * @var unknown_type
	 */
	private $poll_id;
	
	private $poll_name;
	
	private $poll_url;
	
	private $error = 0;
	
	private $error_message;
	
	private static $answer = array(2 => "Да", 1 => "Нет");
	
	public function index( $array )
	{
		if($array)
			$this->poll_id = get_key('polls', 0);
		
		if(preg_match("/\d+?/", $this->poll_id))
			$this->poll_id = (int)$this->poll_id;
			
		$this->polls = "";
			
		if(is_string($this->poll_id) || $this->poll_id > 0)
			return $this->getPoll();
			
		return $this->listPolls();
	}
	
	/**
	 * список опросов
	 */
	private function listPolls()
	{
		$this->addListFedback();
	}
	
	private function getPoll()
	{
		switch ($this->poll_id) {
			case 'feedback':
				return $this->getFeedback();
				break;
			case 'dostavka':
				return $this->getDostavka();
				break;
			default:
				return false;
			break;
		}
	}
	/**
	 * создает на странице контейнер опроса
	 */
	private function displayPoll($id, $name, $url, $start = 0, $end = "")
	{
		$poll = $this->createPollNode($start, $end);
		
		$poll->addChild('poll_id', $id);
		$poll->addChild('poll_name', $name);
		$poll->addChild('poll_url', $url);
		
		return $poll;
	}
	
	private function createPollNode($start = 0, $end = "")
	{
		$poll = $this->polls->addChild('poll');
		
		$poll->addAttribute("start", date('c', $start));
		
		if(!$end)
			$end = date("U")*100;
		$poll->addAttribute("end", date('c', $end));
		
		return $poll;
	}
	
	private function displayQestion($array, $start = 0, $end = "")
	{
		$poll = $this->displayPoll($this->poll_id, $this->poll_name, $this->poll_url, $start, $end);
		$this->displayError($poll);
		if($array)
			return $this->displayAnswers($poll, $array);
	}
	
	/**
	 * добавляет на страницу специальное сообщение
	 * @param obj $node
	 */
	private function displayError( $node )
	{
		if($this->error_message)
		{
			$node->addChild('error', $this->error);
			$node->addChild('message', $this->error_message);
		}
	}
	
	/**
	 * выводит на экран список вопросов с вариантави ответа
	 * @param obj $node
	 * @param array $array - массив с объектами (<pre>
	 * 		<b>question</b> - обязательный, вопрос
	 * 		<b>answer</b> - обязательный, массив ответов на вопрос
	 * 		<b>answer_name</b> - не обязательный, имя объекта
	 * 		<b>_pre</b> - не обязательный, поствикс имя объекта т.е. имя объекта будет состоять из ключа + это имя
	 * 		<b>comment</b> - не обязательный, разрешения поле коментариев
	 * 		</pre>)
	 */
	private function displayAnswers($node, $array)
	{
		$poll_quesion = $node->addChild('poll_question');
		foreach ($array as $key => $value)
		{
			$quesion = $poll_quesion->addChild('question');
			$qest = $quesion->addChild('title', $value->question);
			
			//имя объекта
			$name = $key;
			if(array_key_exists('answer_name', get_object_vars($value)))
				$name = $value->answer_name;
			if(array_key_exists('_pre', get_object_vars($value)))
				$name = $key.$value->_pre;
			$qest->addAttribute("name", $name);
			
			//возможность оставить коментарий
			$comment = 0;
			$comment_name = "";
			if(array_key_exists('comment', get_object_vars($value)))
				if($value->comment)
				{
					$comment = 1;
					$comment_name = $key."_comment";
				}
			$comm = $quesion->addChild('comment', $comment);
			if($comment_name)
				$comm->addAttribute("name", $comment_name);
			
			$answer = $quesion->addChild('answers');
			foreach ($value->answer as $k => $v)
			{
				$answ = $answer->addChild('answer', $v);
				$answ->addAttribute("value", $k);
			}
		}
	}
	
	/**
	 * хардкод добавление в список feedback и dostavka
	 */
	private function addListFedback()
	{
		$this->poll_name =  "Ваши впечатления о покупке в магазине";
		$this->poll_url = "http://www.mvideo.ru/feedback/";
		$this->displayPoll('feedback', $this->poll_name, $this->poll_url);
		
		$this->poll_name =  "Ваши впечатления о доставке товара";
		$this->poll_url = "http://www.mvideo.ru/dostasvka/";
		$this->displayPoll('dostavka', $this->poll_name, $this->poll_url);
	}
	
	/**
	 * создает массив для валидатора
	 * @param object $post
	 * @param array $array
	 */
	private function createValidateObject(&$post, $array)
	{
		foreach ($array as $key => $value)
		{
			$keys = $key;
			if(array_key_exists('_pre', get_object_vars($value)))
				$keys = $key.$value->_pre;
			if(array_key_exists('comment', get_object_vars($value)))
			{
				$post[$key."_comment"]->required = false;
			}
			$post[$keys] = $value;
		}
	}
	
	/**
	 * хардкод опрос покупка в магазине
	 */
	private function getFeedback()
	{
		global $_POST;
		
		$this->poll_name =  "Ваши впечатления о покупке в магазине";
		$this->poll_url = "http://www.mvideo.ru/feedback/";
		$answer3 = array(3 => "Отличное", 2 => "Хорошее", 1 => "Не понравилось");
		$array = array('sales' =>(object)array('question' => "Качество работы продавца", 'answer' => $answer3, "_pre" => "_rating", "comment" => true),
					'cashier' =>(object)array('question' => "Качество работы кассира", 'answer' => $answer3, "_pre" => "_rating", "comment" => true),
					'inspector' =>(object)array('question' => "Качество работы сотрудников зоны выдачи товара", 'answer' => $answer3, "_pre" => "_rating", "comment" => true),
					'recommend' =>(object)array('question' => "Рекомeндуете ли вы М.Видео своим друзьям?", 'answer' => self::$answer)
						);
						
		if($_POST)
		{
			$post = array();
			$this->createValidateObject($post, $array);
			$post['region_id']->required = true;
			$post['shop_id']->required = true;
			$post['order_date']->required = true;
			$post['name']->required = false;
			$post['email']->required = false;
			
			if(!validatePost($post, $_POST))
				return true;
				
			$feedback = new Models\Feedback();
			$this->createDatabaseObject($feedback, $post, $_POST);
			$feedback->save();
			
			
		}
		$this->displayQestion($array);
	}
	
	
	/**
	 * хардкод опрос доставки
	 */
	private function getDostavka()
	{
		global $_POST;
		
		$this->poll_name =  "Ваши впечатления о доставке товара";
		$this->poll_url = "http://www.mvideo.ru/dostasvka/";
		$answer3 = array(3 => "Доставка вовремя", 2 => "Небольшое опоздание", 1 => "Значительное опоздание");
		$answer3_1 = array(3 => "Хорошее качество", 2 => "Среднее качество", 1 => "Качество не устраивает");
		$array = array('time' =>(object)array('question' => "Вовремя ли осуществлена доставка?", 'answer' => $answer3, "_pre" => "_rating", "comment" => true, 'required' => true),
					'man' =>(object)array('question' => "Качество работы курьера", 'answer' => $answer3_1, "_pre" => "_rating", "comment" => true, 'required' => true),
					'product' =>(object)array('question' => "Качество доставленного товара", 'answer' => $answer3_1, "_pre" => "_rating", "comment" => true, 'required' => true),
					'recommend' =>(object)array('question' => "Рекомeндуете ли вы М.Видео своим друзьям?", 'answer' => self::$answer, 'required' => true)
						);
						
		if($_POST)
		{
			$post = array();
			$this->createValidateObject($post, $array);
			$post['order_num']->required = true;
			$post['check_email']->required = true;
			$post['late_hrs']->required = false;
			if($_POST['time_rating'] < 3)
			{
				$post['late_hrs']->required = true;
				$_POST['late_hrs'] = $_POST['late_hrs'][$_POST['time_rating']];
			}
			
			if(!validatePost($post, $_POST))
				return true;
				
			$delivery = new Models\DeliveryAnswers();
			$this->createDatabaseObject($delivery, $post, $_POST);
			$delivery->save();
		}
		$this->displayQestion($array);
	}
}