<?php

namespace Kainanpr\Model;

use \Kainanpr\DB\Sql;
use \Kainanpr\Model;

class User extends Model
{
	const SESSION = "User";

	public static function login($login, $password)
	{
		$sql = new Sql();

		$sql->connect();

		$results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN", array(
			":LOGIN"=>$login
		));

		$sql->disconnect();

		if(count($results) === 0) {
			throw new \Exception("Usuário inexistente ou senha inválida."); 
		}

		$dataUsuario = $results[0];

		if(password_verify($password, $dataUsuario["despassword"]) === true) {
			
			$user = new User();

			$user->setData($dataUsuario);

			$_SESSION[User::SESSION] = $user->getValues();

			return $user;
		} 

		else {
			throw new \Exception("Usuário inexistente ou senha inválida."); 
		}

	}//Fim do metodo login

	//Sabe verificar se estou logado ou nao
	public static function verifyLogin($inAdmin = true)
	{
		if(
			//Verifica se a sessao nao esta definida
			!isset($_SESSION[User::SESSION]) 

			|| // Ou
			
			!$_SESSION[User::SESSION]        

			|| // Ou
		
			!(int)$_SESSION[User::SESSION]["iduser"] > 0 

			|| // Ou

			//Verifica se é um usuario da administração
			(bool)$_SESSION[User::SESSION]["inadmin"] !== $inAdmin
		) {

			header("Location: /admin/login");
			exit;

		}
	}

	public static function logout()
	{
		unset($_SESSION[User::SESSION]);
	}

}//Fim da classe

?>