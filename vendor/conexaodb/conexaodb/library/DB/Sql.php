<?php 

namespace DB;

class Sql {

	const HOSTNAME = "localhost";
	const USERNAME = "root";
	const PASSWORD = "";
	const DBNAME = "nomeDoBD";

	private $conn;

	//Metodo de conexão
	public function connect() {

		try{
			$this->conn = new \PDO(
				"mysql:dbname=".Sql::DBNAME.";host=".Sql::HOSTNAME, 
				Sql::USERNAME,
				Sql::PASSWORD
			);

			$this->conn->exec("set names utf8");

		}catch(PDOException $e) {

			echo "<br> Erro ao se conectar com o banco de dados: " . $e->getMessage();

		}
	}

	//Metodo de desconexão
	public function disconnect() {
		if($this->conn !== NULL)
			$this->conn = NULL;
		else
			echo "<br> Erro ao fechar a conexão com o banco de dados";
	}

	//Metodo auxiliar para pesquisa
	private function setParams($statement, $parameters = array())
	{

		foreach ($parameters as $key => $value) {
			
			$this->bindParam($statement, $key, $value);

		}

	}

	//Metodo auxiliar para pesquisa
	private function bindParam($statement, $key, $value)
	{

		$statement->bindParam($key, $value);

	}

	public function query($rawQuery, $params = array())
	{

		$stmt = $this->conn->prepare($rawQuery);

		$this->setParams($stmt, $params);

		$stmt->execute();

	}

	public function select($rawQuery, $params = array()):array
	{

		$stmt = $this->conn->prepare($rawQuery);

		$this->setParams($stmt, $params);

		$stmt->execute();

		return $stmt->fetchAll(\PDO::FETCH_ASSOC);

	}

}

 ?>