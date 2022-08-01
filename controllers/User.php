<?php

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Medoo\Medoo;


class User {
	/**
	 * @var array
	 */
	public array $user;
	/**
	 * @var
	 */
	public $name;
	/**
	 * @var
	 */
	public $session_id;
	/**
	 * @var
	 */
	public $quiz_id;
	/**
	 * @var Environment
	 */
	public $template;
	/**
	 * @var Medoo
	 */
	public $data;

	/**
	 *
	 */
	public function __construct()
	{
		if ( ! isset( $_SESSION ) ) {
			session_start();
		}
		$this->setSessionId();

		$this->data = new Medoo( include '../config/database.php' );

		$loader         = new FilesystemLoader( __DIR__ . '/../views' );
		$this->template = new Environment( $loader, [ 'debug' => true, 'cache' => '../cache' ] );
		$this->template->addExtension( new Twig\Extension\DebugExtension() );
	}


	/**
	 * @param mixed $quiz_id
	 */
	public function setQuizId( $quiz_id ): void
	{

		$this->quiz_id = $quiz_id;
		$this->setSession( 'quiz_id', $quiz_id );
	}

	/**
	 * @return mixed
	 */
	public function getName()
	{
		return $this->getSession( 'name' );
	}

	/**
	 * @return array|mixed
	 */
	public function readUser()
	{
		$read_from_db_user = $this->data->get( 'user', '*', [ 'session_id' => $this->getSessionId() ] );
		$this->setSession( 'user', $read_from_db_user );

		return $read_from_db_user;
	}

	/**
	 * @return mixed
	 */
	public function getUserId()
	{
		$user = $this->getSession( 'user' );

		return $user['id'];
	}

	/**
	 * @return mixed
	 */
	public function getQuizId()
	{
		$user = $this->getSession( 'user' );

		return $user['quiz_id'];
	}

	/**
	 * @return bool
	 */
	public function isUserReady()
	{
		$user = $this->readUser();

		return ! empty( $user );
	}

	/**
	 * @param $view
	 * @param $valuesArray
	 *
	 * @return void
	 * @throws \Twig\Error\LoaderError
	 * @throws \Twig\Error\RuntimeError
	 * @throws \Twig\Error\SyntaxError
	 */
	public function render( $view, $valuesArray = [] )
	{
		return $this->template->render( $view . '.twig', $valuesArray );
		die();
	}

	/**
	 *
	 * Cenšas nolasīt POST/GET datus, analizēt tos un veikt atbilsotšās darbības
	 * @return void
	 */
	public function checkRequest()
	{

		// ja ir izsaukts POST
		if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {

		// ja ir nepieciešamie POST dati jauna lietotāja izveidei "logins"
			if ( ! empty( $_POST['name'] ) && ! empty( $_POST['quiz_id'] ) ) {

				if ( $this->getCsrf() == $_POST['csrf'] ) {

					$this->setName( $_POST['name'] );
					$this->setQuizId( $_POST['quiz_id'] );
					// ievietot jauno lietotaju datubāzē
					$this->data->insert( 'user', [
						'name'       => $_POST['name'],
						'quiz_id'    => $_POST['quiz_id'],
						'session_id' => $this->getSessionId()
					] );
				}
				$this->setCsrf();
			}
			// ja nav ievadīts vārds
			if ( empty( $_POST['name']) &&  ! empty( $_POST['quiz_id'] )) {

				echo $this->render('error');
				die();
			}

			// testa formas datu nosūtīšana
			if ( ! empty( $_POST['question'] ) && ! empty( $_POST['answer'] ) ) {
				if ( $this->getCsrf() == $_POST['csrf'] ) {
					// saglabā izvēli datubāzē un noņem jautājumu no jautajumu masīva
					$result = new Results();
					$result->save( $this->getUserId(), $_POST['question'], $_POST['answer'] );
					$this->setCsrf();
				}
			}

		}

		// sesijas izdzēšana / testa sākšana no jauna
		if ( isset( $_GET['end_quiz'] ) ) {
			$this->endQuiz();
		}

	}

	/**
	 * @param mixed $name
	 */
	public function setName( $name ): void
	{
		$this->setSession( 'name', $name );
		$this->name = $name;
	}

	/**
	 * @return void
	 */
	public function endQuiz()
	{
		session_regenerate_id( true );

		session_destroy();

		header( 'Location: /' );

		die();
	}

	/**
	 * @return mixed
	 */

	public function setSession( $name, $value )
	{
		return $_SESSION[ $name ] = $value;
	}

	/**
	 * @param $name
	 *
	 * @return array|mixed
	 */
	public function getSession( $name = null )
	{
		return is_null( $name ) ? $_SESSION : $_SESSION[ $name ];

	}

	/**
	 * @return mixed
	 */
	public function getSessionId()
	{
		return $this->session_id;
	}

	/**
	 * @param mixed $session_id
	 */
	public function setSessionId(): void
	{
		$this->session_id = session_id();
	}

	/**
	 * @return array|mixed
	 */
	public function getCsrf()
	{
		return $this->getSession( 'csrf' );
	}

	/**
	 * @return string
	 * @throws Exception
	 */
	public function setCsrf()
	{
		$rand = bin2hex( random_bytes( 32 ) );

		$this->setSession( 'csrf', $rand );

		return $rand;
	}

	/**
	 * @return array|null
	 */
	public function getAnsweredQuestions()
	{
		return $this->data->select( 'results', '*', [
			'user_id' => $this->getUserId()
		] );
	}

}