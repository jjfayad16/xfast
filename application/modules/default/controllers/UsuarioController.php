<?php

class UsuarioController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
		Zend_Loader::loadClass('UsuarioBO');
		//Zend_Loader::loadClass('AlbumForm');
		
    }
	


    public function sindecAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $dados = new DadosDAO();
        
        $diretorio = "/xampp/htdocs/painelouvid/public/dados/ocorrencias/"; 
        $ponteiro  = opendir($diretorio);
        while ($nome_itens = readdir($ponteiro)) {
                $itens[] = $nome_itens;
        }
        if (isset($itens[2])) {
            $link = "$diretorio$itens[2]";
            $a = fopen("$diretorio$itens[2]", "r");

            $valores = array();
            $i=0;
            while (!feof ($a)) {
                    $lines = fgets($a);
                    $res = (count(explode(';',($lines))));

                    $vars1 = explode(';',(rtrim($lines)));
                    //echo str_replace('.','',$vars1[0]) . " - " . $vars1[1] . '<br>';
                    $valores[$i] = $vars1;

                    $i++;
            } 
            //var_dump($valores);exit;
            $submit = $dados->gravaOcorrencias($valores);
            fclose($a);
            unlink($link);
            echo $submit;
        } else 
            echo 'Nenhum arquivo existente na pasta';
    }

}

