<?php
require '../vendor/autoload.php';


$user = new User();

$user->checkRequest();


if ( $user->isUserReady() ) {

	$quiz =  new Quiz($user);

	$question = $quiz->loadQuestion();

	$answers = $quiz->loadAnswers();

	$csrf = $user->setCsrf();

	// dati frontendam
	$count =  $user->getSession('total_count') - $user->getSession('count') ;
	$total_count =  $user->getSession('total_count');

	echo $user->render( 'quiz', compact( 'question', 'answers', 'csrf', 'total_count', 'count'  ) );

} else {

	$quizzes = $user->data->select( 'quiz', '*' );

	$csrf = $user->setCsrf();

	echo $user->render( 'welcome', compact( 'quizzes', 'csrf' ) );

}

//dd($_SESSION);

?>