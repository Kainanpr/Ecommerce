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

	public static function listAll()
	{
		$sql = new Sql();

		$sql->connect();

		$results = $sql->select("SELECT * FROM tb_users AS u INNER JOIN tb_persons AS p ON u.idperson = p.idperson ORDER BY p.desperson");

		$sql->disconnect();

		return $results;
	}

	public function save()
	{
		$sql = new Sql();

		$sql->connect();

		/*
		pdesperson VARCHAR(64), 
		pdeslogin VARCHAR(64), 
		pdespassword VARCHAR(256), 
		pdesemail VARCHAR(128), 
		pnrphone BIGINT, 
		pinadmin TINYINT
		*/

		$results = $sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
			":desperson"=>$this->getdesperson(),
			":deslogin"=>$this->getdeslogin(),
			":despassword"=>$this->getdespassword(),
			":desemail"=>$this->getdesemail(),
			":nrphone"=>$this->getnrphone(),
			":inadmin"=>$this->getinadmin()
		));

		$sql->disconnect();

		$this->setData($results[0]);
	}

	public function get($iduser)
	{
		$sql = new Sql();

		$sql->connect();

		$results = $sql->select("SELECT * FROM tb_users AS u INNER JOIN tb_persons AS p ON u.idperson = p.idperson WHERE u.iduser = :iduser", array(
				":iduser"=>$iduser
		));

		$sql->disconnect();

		$this->setData($results[0]);
	}

	public function update()
	{
		$sql = new Sql();

		$sql->connect();

		$results = $sql->select("CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
			":iduser"=>$this->getiduser(),
			":desperson"=>$this->getdesperson(),
			":deslogin"=>$this->getdeslogin(),
			":despassword"=>$this->getdespassword(),
			":desemail"=>$this->getdesemail(),
			":nrphone"=>$this->getnrphone(),
			":inadmin"=>$this->getinadmin()
		));

		$sql->disconnect();

		$this->setData($results[0]);
	}

	public function delete()
	{
		$sql = new Sql();

		$sql->connect();

		$sql->query("CALL sp_users_delete(:iduser)", array(
			":iduser"=>$this->getiduser()
		));

		$sql->disconnect();
	}

}//Fim da classe

?>