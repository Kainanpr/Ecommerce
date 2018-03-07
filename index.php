<?php 

session_start();

require_once("vendor/autoload.php");

use \Slim\Slim;
use \Kainanpr\DB\Sql;
use \Kainanpr\Page;
use \Kainanpr\PageAdmin;
use \Kainanpr\Model\User;

$app = new Slim();

$app->config('debug', true);


$app->get('/', function() {
    
	$page = new Page();

	$page->setTpl("index");

});

$app->get('/admin', function() {

	User::verifyLogin();
    
	$page = new PageAdmin();

	$page->setTpl("index");

});

$app->get('/admin/login', function() {

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("login");

});

$app->post('/admin/login', function() {
    
    try {

    	User::login($_POST["login"], $_POST["password"]);

		header("Location: /admin");
		exit;

    } catch(Exception $exec) {
    	
    	echo "$exec";
    }
	

});

$app->get('/admin/logout', function() {
    
	User::logout();

	header("Location: /admin/login");
	exit;

});

$app->run();

 ?>