<?php

namespace Kainanpr\Model;

use \Kainanpr\DB\Sql;
use \Kainanpr\Model;
use \Kainanpr\Mailer;

class Product extends Model
{

	public static function listAll()
	{
		$sql = new Sql();

		$sql->connect();

		$results = $sql->select("SELECT * FROM tb_products ORDER BY desproduct");

		$sql->disconnect();

		return $results;
	}

	public static function checkList($list)
	{
		foreach ($list as &$row) {

			$p = new Product();
			$p->setData($row);
			$row = $p->getValues();
		}

		return $list;
	}


	public function save()
	{
		$sql = new Sql();

		$sql->connect();

		$results = $sql->select("CALL sp_products_save(:idproduct, :desproduct, :vlprice, :vlwidth, :vlheight, :vllength, :vlweight, :desurl)", array(
			":idproduct"=>$this->getidproduct(),
			":desproduct"=>$this->getdesproduct(),
			":vlprice"=>$this->getvlprice(),
			":vlwidth"=>$this->getvlwidth(),
			":vlheight"=>$this->getvlheight(),
			":vllength"=>$this->getvllength(),
			":vlweight"=>$this->getvlweight(),
			":desurl"=>$this->getdesurl()
		));

		$sql->disconnect();

		$this->setData($results[0]);

	}

	public function get($idproduct)
	{
		$sql = new Sql();

		$sql->connect();

		$results = $sql->select("SELECT * FROM tb_products WHERE idproduct = :idproduct", array(
			":idproduct"=>$idproduct
		));

		$sql->disconnect();

		$this->setData($results[0]);
	}

	public function delete()
	{
		$sql = new Sql();

		$sql->connect();

		$results = $sql->query("DELETE FROM tb_products WHERE idproduct = :idproduct", array(
			":idproduct"=>$this->getidproduct()
		));

		$sql->disconnect();

	}

	public function checkPhoto()
	{
		if(file_exists($_SERVER["DOCUMENT_ROOT"] . DIRECTORY_SEPARATOR . 
			"resource" . DIRECTORY_SEPARATOR . 
			"site" . DIRECTORY_SEPARATOR .
			"img" . DIRECTORY_SEPARATOR .
			"products" . DIRECTORY_SEPARATOR .
			$this->getidproduct() . ".jpg"
		)) {

			$url = "/resource/site/img/products/" . $this->getidproduct() . ".jpg";

		//Caso nao exista retorna foto padrao
		} else {

			$url = "/resource/site/img/product.jpg";

		}

		return $this->setdesphoto($url);
	}

	//Reescreveer o metodo da classe model
	public function getValues()
	{
		$this->checkPhoto();

		$values = parent::getValues();

		return $values;
	}

	public function setPhoto($file)
	{
		$extension = explode('.', $file['name']);
		$extension = end($extension);

		switch ($extension) {
			case 'jpg':
			case 'jpeg':
			$image = imagecreatefromjpeg($file['tmp_name']);
				break;
			
			case "gif":
			$image = imagecreatefromgif($file['tmp_name']);	
				break;

			case "png":
			$image = imagecreatefrompng($file['tmp_name']);	
				break;
		}

		$destino = $_SERVER["DOCUMENT_ROOT"] . DIRECTORY_SEPARATOR . 
			"resource" . DIRECTORY_SEPARATOR . 
			"site" . DIRECTORY_SEPARATOR .
			"img" . DIRECTORY_SEPARATOR .
			"products" . DIRECTORY_SEPARATOR .
			$this->getidproduct() . ".jpg";

		imagejpeg($image, $destino);

		imagedestroy($image);

		$this->checkPhoto();
	}


}//Fim da classe

?>