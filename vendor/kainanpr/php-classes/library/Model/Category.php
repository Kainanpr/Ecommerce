<?php

namespace Kainanpr\Model;

use \Kainanpr\DB\Sql;
use \Kainanpr\Model;
use \Kainanpr\Mailer;

class Category extends Model
{

	public static function listAll()
	{
		$sql = new Sql();

		$sql->connect();

		$results = $sql->select("SELECT * FROM tb_categories ORDER BY descategory");

		$sql->disconnect();

		return $results;
	}

	public function save()
	{
		$sql = new Sql();

		$sql->connect();

		$results = $sql->select("CALL sp_categories_save(:idcategory, :descategory)", array(
			":idcategory"=>$this->getidcategory(),
			":descategory"=>$this->getdescategory()
		));

		$sql->disconnect();

		$this->setData($results[0]);

		Category::updateFile();
	}

	public function get($idcategory)
	{
		$sql = new Sql();

		$sql->connect();

		$results = $sql->select("SELECT * FROM tb_categories WHERE idcategory = :idcategory", array(
			":idcategory"=>$idcategory
		));

		$sql->disconnect();

		$this->setData($results[0]);
	}

	public function delete()
	{
		$sql = new Sql();

		$sql->connect();

		$results = $sql->query("DELETE FROM tb_categories WHERE idcategory = :idcategory", array(
			":idcategory"=>$this->getidcategory()
		));

		$sql->disconnect();

		Category::updateFile();
	}

	public static function updateFile()
	{
		$categories = Category::listAll();

		$html = [];

		foreach ($categories as $row) {
			array_push($html, '<li><a href="/categories/' . $row['idcategory'] . '">' . $row['descategory'] . '</a></li>');
		}

		file_put_contents($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "views" . DIRECTORY_SEPARATOR . "categories-menu.html", implode('', $html));
	}

	public function getProducts($related = true)
	{
		$sql = new Sql();

		$sql->connect();

		if($related == true)
		{

			$results = $sql->select("SELECT * FROM tb_products WHERE idproduct IN(
				SELECT p.idproduct FROM tb_products AS p 
				INNER JOIN tb_productscategories AS pc ON p.idproduct = pc.idproduct
				WHERE pc.idcategory = :idcategory)", array(
				":idcategory"=>$this->getidcategory()
			));
			

		} else {

			$results = $sql->select("SELECT * FROM tb_products WHERE idproduct NOT IN(
				SELECT p.idproduct FROM tb_products AS p 
				INNER JOIN tb_productscategories AS pc ON p.idproduct = pc.idproduct
				WHERE pc.idcategory = :idcategory)", array(
				":idcategory"=>$this->getidcategory()
			));

		}

		$sql->disconnect();

		return $results;
	}

	public function addProduct(Product $product)
	{
		$sql = new Sql();

		$sql->connect();

		$sql->query("INSERT INTO tb_productscategories (idcategory, idproduct) VALUES(:idcategory, :idproduct)", [
			':idcategory'=>$this->getidcategory(),
			':idproduct'=>$product->getidproduct()
		]);

		$sql->disconnect();
	}

	public function removeProduct(Product $product)
	{
		$sql = new Sql();

		$sql->connect();

		$sql->query("DELETE FROM tb_productscategories WHERE idcategory = :idcategory AND idproduct = :idproduct", [
			':idcategory'=>$this->getidcategory(),
			':idproduct'=>$product->getidproduct()
		]);

		$sql->disconnect();
	}


}//Fim da classe

?>