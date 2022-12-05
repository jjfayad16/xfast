<?php

class IndexController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
		Zend_Loader::loadClass('UtilDAO');
		//Zend_Loader::loadClass('AlbumForm');
    }
	
	function preDispatch() 
	{
		//phpinfo();exit;
        /* $session = new Zend_Session_Namespace('usuario');
		$user = substr($this->_getParam('tecnico'),8);
		$session->user = $user;
		
		$util = new UtilBO();
		$this->view->usuario = $util->dadosUser($user);
		//var_dump($usuario);exit; */
	}
        
        public function extrairAction(){
            /*$session = new Zend_Session_Namespace('procon');
            unset($session->procon);*/
        }
        
        public function buscasindecsAction()
        {
            $this->_helper->layout->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
            $util = new UtilDAO();
            $procons = $util->buscaSindecs();
            $session = new Zend_Session_Namespace('procon');
            $session->procon = $procons;

            $this->_response->appendBody(Zend_Json::encode($procons));
        }
        
        public function validaAction()
        {
            $this->_helper->layout->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
            $session = new Zend_Session_Namespace('procon');
            $util = new UtilDAO();
            $maxid = $util->buscaMaxIDSindec();
            if (empty($session->procon)) {
                $procons = $util->buscaSindecs();
                $session->procon = $procons;
            } else
            $procons = $session->procon;
            $id = 1;
            $valida = false;
            while (!$valida) {
                $id++;
                if (isset($procons[$id]) && $procons[$id]['valida'] == 0 && $id != '65')
                    $valida=true;
                //echo $id;
                //$id=65;
                if ($id > $maxid) {
                    echo "Extração Finalizada";exit;
                }
            }
            //exit;
            $this->_response->appendBody(Zend_Json::encode($procons[$id]));
        }

    public function indexAction()
    {
        
	/** 
        * Nesta fun��o iremos baixar a imagem do captcha
        *
        * Parametro $url: 
        *   Coloque a url que o captcha usa para reproduzir a imagem
        * Parametro $arquivo: 
        *   Coloque o arquivo para salvar a imagem. 
        *   IMPORTANTE que o arquivo j� exista e tenha permiss�o CHMOD 777
        */
        function recebe_imagem($url, $arquivo, $cookie_txt) {
              //echo $_SERVER['DOCUMENT_ROOT'];exit;
              $link = $_SERVER['DOCUMENT_ROOT'].'/procon/public/'.$cookie_txt;
              unlink($link);
              $cookie = $link; //Importantissimo que o caminho esteja correto e com permiss�o CHMOD 777

              $ch = curl_init ();

              curl_setopt_array($ch, array(
                    CURLOPT_URL => $url, //url que produz a imagem do captcha.
                    CURLOPT_COOKIEFILE => $cookie, //esse mais o debaixo fazem a m�gica do captcha
                    CURLOPT_COOKIEJAR => $cookie,  //esse mais o de cima fazem a m�gica do.. ah j� falei isso;
                    CURLOPT_FOLLOWLOCATION => 1, //n�o sei, mas funciona :D
                    CURLOPT_RETURNTRANSFER => 1, //retorna o conte�do.
                    CURLOPT_BINARYTRANSFER => 1, //essa tranferencia � bin�ria.
                    CURLOPT_HEADER => 0, //n�o imprime o header.
              ));    

              $data = curl_exec($ch);

              curl_close ($ch);

              //salva a imagem
              $fp = fopen($arquivo,'w');
              fwrite($fp, $data);
              fclose($fp);

              //retorna a imagem
              return $arquivo;
        }
        
       //Ent�o vamos pegar a imagem
       
       
       
       $session = new Zend_Session_Namespace('procon');
       $procons = $session->procon;
       
       $id = $this->_getParam('id');
       if (empty($id)) {
           $id = 0;
            while (empty($procons[$id])) {
                 $id++;
                 //echo $id;exit;
             }
       }
       
       
       $conteudo = explode("/",$procons[$id]['url']);
       
       
       $img = recebe_imagem("http://".$conteudo[2]."/sindecconsulta/captcha/CAPTCHA.asp", 'procon'.$procons[$id]['id'].'.gif', 'procon'.$procons[$id]['id'].'.txt');
       //E criar o formul�rio que mostra a imagem + o campo de inser��o do CNPJ
       print "<img src='".EXT_PATH.$img."' width='140px' />" . 
             '<form action="#" method="POST">
                captcha
               <input id="CaptchaStr" type="text" size="16" name="CaptchaStr">
               <input id="idbase" type="hidden" name="idbase" value="'.$id.'">
               <input id="consultar" type="button" value="Consultar" style="font-size:9px;" onClick="javascript:submitSindec();" name="consultar">
             </form>';
		
		
    }
	
	public function index2Action()
    {
       $session = new Zend_Session_Namespace('procon');
       $procons = $session->procon;	
       
       $CaptchaStr = $this->_getParam('CaptchaStr');
       //$strProcon = $this->_getParam('strProcon');
       $idbase = $this->_getParam('idbase');
       $id = $procons[$this->_getParam('idbase')]['id'];
        
        //var_dump($id);exit;
       $fileBDHidden = $procons[$id]['id_login'];
       $codCNPJ = $procons[$id]['usuario'];
       $codPass = $procons[$id]['senha'];
       //echo $procons[$id]['url'];exit;
       $conteudo = explode("/",$procons[$id]['url']);
       //var_dump($conteudo);exit;
       $urlBase = $conteudo[2];
      
       //
       //// Pega os valores dos campos que foram enviados pelo formul�rio
//*sem valida��o mesmo, � s� pra exemplo t�?
//$fileBDHidden = $this->_getParam('fileBDHidden');
//$codCNPJ = $this->_getParam('codCNPJ');
//$codPass = $this->_getParam('codPass');
//$CaptchaStr = $this->_getParam('CaptchaStr');
//$strProcon = $this->_getParam('strProcon');
//echo $strProcon;exit;
//$fileBDHidden = "sindecsp0001";
//$codCNPJ = "00360305000104";
//$codPass = "529109";
//$CaptchaStr = "";
$strProcon = "PROCON MUNICIPAL DE AVARÉ";
//$letras = $_POST['idLetra'];

#Coisas importantes para dizer ao $ch logo mais

//IMPORTANTE que o caminho esteja correto e tenha permiss�o CHMOD 777
$cookie = $_SERVER['DOCUMENT_ROOT'].'/procon/public/procon'.$id.'.txt'; 

// n�o sei.. coloquei pra garantir
$reffer = "http://google.com"; 

//sempre � bom ter para garantir a entrada do seu servi�o
$agent = "Mozilla/5.0 (Windows; U; Windows NT 5.0; en-US; rv:1.4) Gecko/20030624 Netscape/7.1 (ax)"; 

//url da receita que valida o formul�rio
$url ="http://".$urlBase."/sindecconsulta/defaultfornecedor.asp"; 
//echo $url;exit;
//dados do POST do formul�rio da receita. 
//** Muito importante entender os formul�rios que voc� esteja trabalhando **
//os campos NESTA EXATA ordem funcionaram legal ;)
$post_fields = "strProcon={$strProcon}&fileBDHidden={$fileBDHidden}&codCNPJ={$codCNPJ}&codPass={$codPass}&CaptchaStr={$CaptchaStr}&tipoPessoa=1&uf_conn=SP&conn=sindecsp0004&intcodcredenciada=4&consultar=Consultar"; 
//echo $post_fields;exit;
//$post_fields = "fileBDHidden={$fileBDHidden}&codCNPJ={$codCNPJ}&codPass={$codPass}&CaptchaStr={$CaptchaStr}&tipoPessoa=1&uf_conn=SP&conn=sindecsp0004&intcodcredenciada=4&consultar=Consultar"; 
//echo $post_fields;exit;
//agora sim.. 1, 2, 3 VALENDO! 
$ch = curl_init();

curl_setopt_array($ch, array(
  CURLOPT_URL => $url, //sem isso, seu cURL � imprest�vel
  CURLOPT_POST => 1, //afirmo que ele ir� fazer um POST
  CURLOPT_POSTFIELDS => $post_fields, //quais s�o os campos que estarei enviando ao valida.asp?
  CURLOPT_USERAGENT => $agent, //ahh � importante sempre ter n� =D
  CURLOPT_REFERER => $reffer, //n�o sei.. coloquei pra garantir
  CURLOPT_COOKIEFILE => $cookie, //lembra dos cookies que guardamos qndo digitamos o captcha? 
  CURLOPT_COOKIEJAR => $cookie,  //ent�o, precisamos deles :)
  CURLOPT_FOLLOWLOCATION => 1, // n�o quero explicar, mas � importante. pesquisa ae depois ;)
  CURLOPT_RETURNTRANSFER => 1, // quer ver os dados? ent�o sempre ative esta op��o no seu script
  CURLOPT_HEADER => 0, // sem header
));

$url = "http://".$urlBase."/sindecconsulta/fa_forn_lista.asp?busca=1&codCNPJ=00360305000104";
//$url2 ="http://sindecmunicipal.procon.sp.gov.br/sindecconsulta/fa_forn_lista.asp?busca=1&codCNPJ=00360305000104"

$teste = curl_exec($ch);
//var_dump($teste);exit;

curl_setopt_array($ch, array(
  CURLOPT_URL => $url, //sem isso, seu cURL � imprest�vel
  CURLOPT_POST => 0, //afirmo que ele ir� fazer um POST
  CURLOPT_USERAGENT => $agent, //ahh � importante sempre ter n� =D
  CURLOPT_REFERER => $reffer, //n�o sei.. coloquei pra garantir
  CURLOPT_COOKIEFILE => $cookie, //lembra dos cookies que guardamos qndo digitamos o captcha? 
  CURLOPT_COOKIEJAR => $cookie,  //ent�o, precisamos deles :)
  CURLOPT_FOLLOWLOCATION => 1, // n�o quero explicar, mas � importante. pesquisa ae depois ;)
  CURLOPT_RETURNTRANSFER => 1, // quer ver os dados? ent�o sempre ative esta op��o no seu script
  CURLOPT_HEADER => 0, // sem header
));

//$url = "http://sindecmunicipal.procon.sp.gov.br/sindecconsulta/fa_forn_lista.asp?dtInicio=01/01/2013&dtFim=31/01/2013&codCNPJ=00360305000104&strCodfa=&orderby=&orderbytp_old=&situacaoresposta=9&intcodseq=0&tipocip=0&codtermo=&busca_avancada=false";

//echo $url;exit;
$teste = curl_exec($ch);
//var_dump($teste);exit;

/* $url = "http://".$urlBase."/sindecconsulta/fa_forn_lista.asp?dtInicio=01/01/2013&dtFim=31/12/2013&codCNPJ=00360305000104";

curl_setopt_array($ch, array(
  CURLOPT_URL => $url, //sem isso, seu cURL � imprest�vel
  CURLOPT_POST => 0, //afirmo que ele ir� fazer um POST
  CURLOPT_USERAGENT => $agent, //ahh � importante sempre ter n� =D
  CURLOPT_REFERER => $reffer, //n�o sei.. coloquei pra garantir
  CURLOPT_COOKIEFILE => $cookie, //lembra dos cookies que guardamos qndo digitamos o captcha? 
  CURLOPT_COOKIEJAR => $cookie,  //ent�o, precisamos deles :)
  CURLOPT_FOLLOWLOCATION => 1, // n�o quero explicar, mas � importante. pesquisa ae depois ;)
  CURLOPT_RETURNTRANSFER => 1, // quer ver os dados? ent�o sempre ative esta op��o no seu script
  CURLOPT_HEADER => 0, // sem header
));

$result = curl_exec($ch);
//var_dump($result);exit;
curl_close($ch);
//var_dump($result);exit;

preg_match_all('/Resultado<(.+)table>/s', $result, $conteudo);

$util = new UtilDAO();
$valor = $util->getdata($conteudo[0][0]); */
$util = new UtilDAO();
$dias = $util->verificaDados($urlBase, $ch);

$valor = $util->geraDados($urlBase, $ch, $dias);
curl_close($ch);
//var_dump($valor);exit;
//echo $id;exit;
$grava = $util->gravaExtracao($valor, $id);
$procons[$id]['valida'] = $valor;
$session->procon = $procons;
//$procons = $session->procon;
//var_dump($session->procon);
//$this->view->resultado = $valor;
//var_dump($valor);exit
$this->_helper->layout->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
$this->_response->appendBody(Zend_Json::encode($procons[$id]));
    }
	

}

