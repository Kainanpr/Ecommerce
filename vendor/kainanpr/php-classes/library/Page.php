<?php

namespace Kainanpr;

use Rain\Tpl;

class Page 
{
	//Atributos
	private $tpl;
	private $options = [];
	private $defaults = [
		"data"=>[]
	];

	//Metodo construtor
	public function __construct($opts = array()) 
	{

		//Mescla dois arrays, o ultimo sobrescreve os anteriores
		$this->options = array_merge($this->defaults, $opts);

		//Configuração do RainTPL
		$config = array(
			//$_SERVER["DOCUMENT_ROOT"] traz o diretorio root do servidor
	        "tpl_dir"       => $_SERVER["DOCUMENT_ROOT"]. "/views/", 
	        "cache_dir"     => $_SERVER["DOCUMENT_ROOT"]. "/views-cache/",
	        //"debug"         => true //Set to falso to improve the speed
	    );

	    Tpl::configure( $config );

	    $this->tpl = new Tpl;

	    $this->setData($this->options["data"]);

	    $this->tpl->draw("header");

	}

	private function setData($data = array()) 
	{
		foreach ($data as $key => $value) {
	    	$this->tpl->assign($key, $value);
	    }
	}

	public function setTpl($name, $data = array(), $returnHTML = false)
	{
		$this->setData($data);

		return $this->tpl->draw($name, $returnHTML);

	}

	//Metodo destrutor
	public function __destruct() 
	{

		$this->tpl->draw("footer");
	}

}//Fim da classe


?>