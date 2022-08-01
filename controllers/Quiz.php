<?php


class Quiz
{
	/**
	 * @var
	 */
	public $questions;
	/**
	 * @var
	 */
	public $answers;
	/**
	 * @var
	 */
	public $user;

	/**
	 * @param $user
	 */
	public function __construct($user) {
		$this->user = $user;
	}
	/**
	 * @return array
	 */
	public function getQuestions():array
	{
		// select * from  question q LEFT JOIN results r on q.id = r.question_id  AND r.user_id = 150 WHERE r.id IS NULL  AND q.quiz_id=1
		return $this->questions =  $this->user->data->query('select q.id, q.text from <question> as q LEFT JOIN results as r on q.id = r.question_id  AND r.user_id = :user_id WHERE r.id IS NULL  AND <q.quiz_id>=:quiz_id ORDER BY RAND()',
			[
				':user_id' => $this->user->getUserId(),
				':quiz_id' => $this->user->getQuizId()
			]
		)->fetchAll();
	}

	/**
	 * @return array
	 */
	public function getQuestionsCount():array
	{
		// select * from  question q LEFT JOIN results r on q.id = r.question_id  AND r.user_id = 150 WHERE r.id IS NULL  AND q.quiz_id=1
		return $this->questions =  $this->user->data->query('select q.id, q.text from <question> as q  WHERE <q.quiz_id>=:quiz_id',
			[
				':quiz_id' => $this->user->getQuizId()
			]
		)->fetchAll();
	}

	/**
	 * @param $question_id
	 *
	 * @return array
	 */
	public function getAnswers($question_id): array
	{
		return $this->answers = $this->user->data->select('answer','*', ['question_id' => $question_id]);
	}

	/**
	 * @return mixed
	 */
	public function getResults():array
	{
		return  $this->user->data->query('SELECT r.id, r.answer_id, a.id,  a.correct FROM results as r LEFT JOIN answer a on a.id = r.answer_id WHERE r.user_id = :user_id AND a.correct = 1',
			[
				':user_id' => $this->user->getUserId(),
			]
		)->fetchAll();
	}

	/**
	 * @return void
	 */
	public function showResults()
	{

		// SELECT r.id, r.answer_id, a.id, a.correct
		// FROM results as r
		//    LEFT JOIN answer a on a.id = r.answer_id
		//WHERE r.user_id = 157

		$count = count($this->getQuestionsCount());

		$correct_count = count($this->getResults());

		$user = $this->user->getName();

		echo $this->user->render( 'results', compact('correct_count', 'count', 'user'));
		die();
	}
	// nosaka visu jēgu

	/**
	 * @return mixed
	 */
	public function loadQuestion()
	{

		// ielādēti neatbildētie jautājumi no datubāzes
		$questions = $this->getQuestions(); // + query

		// ja vairāk jautajumi nav
		if(count($questions)==0)
		{

			$this->showResults();

			//return dd("paldies! Visi jautājumi atbildēti");
		}

		// bet tā, ja ir, tad ielikti tie sesijā
		$this->user->setSession('questions', $questions);

		// pirmais random jautajums aktualizēts
		$this->user->setSession('actual_question', $questions[0]);

		// ielasītas atbildes no datubāzes sesijā
		$this->user->setSession('answers', $this->getAnswers($questions[0]['id']));

		$this->user->setSession('count', count($questions));
		$this->user->setSession('total_count', count($this->getQuestionsCount()));

		return $this->user->getSession('actual_question');
	}

	/**
	 * @return mixed
	 */
	public function loadAnswers()
	{
		return $this->user->getSession('answers');
	}
}