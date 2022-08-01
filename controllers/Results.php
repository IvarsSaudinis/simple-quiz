<?php
use Medoo\Medoo;

class Results
{
	public $user_id;
	public $question_id;
	public $answer_id;

	public function __construct() {
		$this->data = new Medoo( include '../config/database.php' );
	}

	public function save($user_id, $question_id, $answer_id)
	{
		return $this->data->insert('results',
			[
				'user_id' => $user_id,
				'question_id' => $question_id,
				'answer_id' => $answer_id
			]);
	}

}