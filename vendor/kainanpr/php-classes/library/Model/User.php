<?php

namespace Kainanpr\Model;

use \Kainanpr\DB\Sql;
use \Kainanpr\Model;
use \Kainanpr\Mailer;

class User extends Model
{
	const SESSION = "User";
	const SECRET = "HcodePhp7_Secret";

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

		//password criptografado
		$password = password_hash($this->getdespassword(), PASSWORD_DEFAULT, [
	    	"cost"=>12
	    ]);

		$results = $sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
			":desperson"=>$this->getdesperson(),
			":deslogin"=>$this->getdeslogin(),
			":despassword"=>$password,
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

	public static function getForgot($email)
	{

		$sql = new Sql();

		$sql->connect();

		$results = $sql->select("
			SELECT * FROM tb_persons AS p 
			INNER JOIN tb_users AS u ON p.idperson = u.idperson
			WHERE p.desemail = :EMAIL", array(
			":EMAIL"=>$email
		));

		

		if(count($results) === 0)
		{
			throw new \Exception("Não foi possível recuperar a senha.");
		}
		
		else 
		{
			$dadosUsuario = $results[0];

			$resultsRecovery = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser, :desip)", array(
				":iduser"=>$dadosUsuario['iduser'],
				//Pega o ip do usuario
				":desip"=>$_SERVER["REMOTE_ADDR"]
			));

			if(count($resultsRecovery) === 0)
			{
				throw new \Exception("Não foi possível recuperar a senha.");
			}

			else
			{
				$dadosRecovery = $resultsRecovery[0];

				$code = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, USER::SECRET, $dadosRecovery["idrecovery"], MCRYPT_MODE_ECB));

				$link = "http://www.ecommerce.com.br/admin/forgot/reset?code=$code";

				$mailer = new Mailer($dadosUsuario["desemail"], $dadosUsuario["desperson"], "Redefinir Senha da Hcode Store", "forgot", array(
						"name"=>$dadosUsuario["desperson"],
						"link"=>$link
				));

				$mailer->send();

				return $dadosUsuario;
			}

		}


		$sql->disconnect();

	}//Fim do metodo

	public static function validForgotDecrypt($code)
	{

		$idRecovery = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, User::SECRET, base64_decode($code), MCRYPT_MODE_ECB);

		$sql = new Sql();

		$sql->connect();

		$results = $sql->select("SELECT * FROM tb_userspasswordsrecoveries AS up 
			INNER JOIN tb_users AS u ON up.iduser = u.iduser
			INNER JOIN tb_persons AS p ON u.idperson = p.idperson
			WHERE up.idrecovery = :idrecovery AND up.dtrecovery IS NULL AND DATE_ADD(up.dtregister, INTERVAL 1 HOUR) >= NOW();", array(
				":idrecovery"=>$idRecovery
			));

		$sql->disconnect();

		if(count($results) === 0)
		{
			throw new \Exception("Não foi possível recuperar a senha.");
			
		}
		else
		{
			return $results[0];
		}

	} 

	public static function setForgotUsed($idRecovery)
	{
		$sql = new Sql();

		$sql->connect();

		$sql->query("UPDATE tb_userspasswordsrecoveries SET dtrecovery = NOW() WHERE idrecovery = :idrecovery", array(
			":idrecovery"=>$idRecovery
		));

		$sql->disconnect();
	}

	public function setPassword($password)
	{
		$sql = new Sql();

		$sql->connect();

		$sql->query("UPDATE tb_users SET despassword = :password WHERE iduser = :iduser", array(
			":password"=>$password,
			":iduser"=>$this->getiduser()
		));

		$sql->disconnect();
	}

}//Fim da classe

?>