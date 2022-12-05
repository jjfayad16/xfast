<?php

class ExtracaoController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
		Zend_Loader::loadClass('ExtracaoDAO');
		//Zend_Loader::loadClass('AlbumForm');
    }
	
	function preDispatch() 
	{

	}
        
        public function anexoAction(){
            $this->_helper->layout->disableLayout();
            Zend_Loader::loadClass('UtilDAO');
            $util = new UtilDAO();
            $this->view->resultado = $util->listaExtracao();
        }
        
        public function indexAction(){
            
            
            $link = $_SERVER['DOCUMENT_ROOT'].EXT_PATH.'procon_c/';
            $link2 = $_SERVER['DOCUMENT_ROOT'].EXT_PATH.'procon_i/';
            
            $ponteiro  = opendir($link);
            //echo $link;exit;
            while ($nome_itens = readdir($ponteiro)) {
                 if ($nome_itens != '.' && $nome_itens != '..')
                    unlink("$link$nome_itens");
                //$itens[] = $nome_itens;
            }
            $ponteiro2  = opendir($link2);
            while ($nome_itens = readdir($ponteiro2)) {
                //echo $nome_itens.'<br>';
                 if ($nome_itens != '.' && $nome_itens != '..')
                    unlink("$link2$nome_itens");

            }
            //$this->_helper->layout->disableLayout();
            $session = new Zend_Session_Namespace('procon');
            unset($session->procon);
            
        }
        
        public function extrair2Action(){
            $this->_helper->layout->disableLayout();
            /*$session = new Zend_Session_Namespace('procon');
            unset($session->procon);*/
            $this->render('extrair');
            
        }
        
        public function buscasindecsAction()
        {
            $this->_helper->layout->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
            $util = new ExtracaoDAO();
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
            $util = new ExtracaoDAO();
            $maxid = $util->buscaMaxIDSindec();
            if (empty($session->procon)) {
                $procons = $util->buscaSindecs();
                $session->procon = $procons;
            } else
            $procons = $session->procon;
            //var_dump($procons);exit;
            $id = 1;
            $valida = false;
            while (!$valida) {
                $id++;
                if (isset($procons[$id]) && $procons[$id]['valida'] == '0') 
                    $valida=true;
                //echo $id.'<br>';
                
                //echo $id;
                //$id=65;
                if ($id > $maxid) {
                    $procons[$id]['finalizado'] = "1";
                    $valida=true;
                }
            }
            //exit;
            $this->_response->appendBody(Zend_Json::encode($procons[$id]));
        }

    public function processaAction()
    {
        $this->_helper->layout->disableLayout();
        //$this->_helper->viewRenderer->setNoRender(true);
	/** 
        * Nesta função iremos baixar a imagem do captcha
        *
        * Parametro $url: 
        *   Coloque a url que o captcha usa para reproduzir a imagem
        * Parametro $arquivo: 
        *   Coloque o arquivo para salvar a imagem. 
        *   IMPORTANTE que o arquivo j� exista e tenha permiss�o CHMOD 777
        */
        function recebe_imagem($url, $arquivo, $cookie_txt) {
              //echo $_SERVER['DOCUMENT_ROOT'];exit;
              $link = $_SERVER['DOCUMENT_ROOT'].EXT_PATH.'procon_c/'.$cookie_txt;
              $link2 = $_SERVER['DOCUMENT_ROOT'].EXT_PATH.'procon_i/'.$arquivo;
              $cookie = $link; //Importantissimo que o caminho esteja correto e com permiss�o CHMOD 777
              //curl_close($ch);
              
              $util = new ExtracaoDAO();
              
              $ch = curl_init();
              $data = $util->curl($ch, $url, $cookie);
              curl_close ($ch);

              //salva a imagem
              $fp = fopen($link2,'w');
              fwrite($fp, $data);
              fclose($fp);

              //retorna a imagem
              return $arquivo;
        }
        
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
       
       $time = time();
       $this->view->id = $id;
       $this->view->nome = $procons[$id]['nome'];
       $this->view->img = recebe_imagem($conteudo[0]."//".$conteudo[2]."/sindecconsulta/captcha/CAPTCHA.asp", 'procon'.$time.'.gif', 'procon'.$procons[$id]['id'].'.txt');
       //E criar o formul�rio que mostra a imagem + o campo de inser��o do CNPJ
       // <center><form action="'.BASE_URL.'/extracao/index2" method="POST" class="formulario">
       /* print 
             '<center><form action="#" method="POST" class="formulario">
                <div class="row">                
                    <p>Extração '.$procons[$id]['nome'].'</p><br><br><br>
                    <img src="'.EXT_PATH."procon_i/".$img.'" width="140px" />
                </div>
                <div class="row">
                    <label>captcha</label>
                    <input id="CaptchaStr" type="text" size="16" name="CaptchaStr">
                    <input id="idbase" type="hidden" name="idbase" value="'.$id.'">
                </div>
                <div class="btn">
                    <input id="consultar" type="button" value="Consultar" class="btnSalvar" style="font-size:9px;" onclick="javascript:submitSindec()" name="consultar"  /> 
                </div>
             </form></center>';
       /*$cgc = explode(',',$procons[$id]['cgc']);
       var_dump($cgc);*/
		
		
    }
	
    public function index2Action()
    {
       $util = new ExtracaoDAO();
        //echo 'ok';exit;
       $session = new Zend_Session_Namespace('procon');
       $procons = $session->procon;	
       
       $CaptchaStr = $this->_getParam('CaptchaStr');
       //$strProcon = $this->_getParam('strProcon');
       $idbase = $this->_getParam('idbase');
       $id = $procons[$this->_getParam('idbase')]['id'];
       $dtIni = $procons[$this->_getParam('idbase')]['dtIni'];
       
        //var_dump($id);exit;
       $fileBDHidden = $procons[$id]['id_login'];
       $codCNPJ = $procons[$id]['usuario'];
       $codPass = $procons[$id]['senha'];
       //echo $procons[$id]['url'];exit;
       $conteudo = explode("/",$procons[$id]['url']);
       //var_dump($conteudo);exit;
       $urlBase = $conteudo[0]."//".$conteudo[2];
      
       //
       //// Pega os valores dos campos que foram enviados pelo formul�rio
    
    $strProcon = "PROCON MUNICIPAL DE AVARÉ";
    //$letras = $_POST['idLetra'];

    #Coisas importantes para dizer ao $ch logo mais

    //IMPORTANTE que o caminho esteja correto e tenha permiss�o CHMOD 777
    $cookie = $_SERVER['DOCUMENT_ROOT'].EXT_PATH.'procon_c/procon'.$id.'.txt'; 

    // n�o sei.. coloquei pra garantir
    $reffer = "https://google.com"; 

    //sempre � bom ter para garantir a entrada do seu servi�o
    $agent = "Mozilla/5.0 (Windows; U; Windows NT 5.0; en-US; rv:1.4) Gecko/20030624 Netscape/7.1 (ax)"; 

    //url da receita que valida o formul�rio
    $url =$urlBase."/sindecconsulta/defaultfornecedor.asp"; 

    $post_fields = "strProcon={$strProcon}&fileBDHidden={$fileBDHidden}&codCNPJ={$codCNPJ}&codPass={$codPass}&CaptchaStr={$CaptchaStr}&tipoPessoa=1&uf_conn=SP&conn=sindecsp0004&intcodcredenciada=4&consultar=Consultar"; 
    
    $ch = curl_init();

    $resultado = $util->curl($ch, $url, $cookie, $post_fields);
    //var_dump($resultado);exit;
    preg_match_all('/conteudoErro(.+)input/s', $resultado, $trataErro);
    if (trim($trataErro[0][0]) != "") {
        $procons[$id]['erro'] = 'erro';

    } else {
        $url = $urlBase."/sindecconsulta/resultado.asp";
        $resultado = $util->curl($ch, $url, $cookie);
        
        //$util = new ExtracaoDAO();
        $cgcs = explode(',',$procons[$id]['cgc']);
        $soma=0;
        //$valor = array();
        foreach ($cgcs as $cgc) {
            
            empty($valor);
            if ($soma > 0) {
                $url = $urlBase."/sindecconsulta/resultado.asp";
                $resultado = $util->curl($ch, $url, $cookie);
            }
            $soma++;
            $url = $urlBase."/sindecconsulta/fa_forn_lista.asp?busca=2&codCNPJ=".$cgc;
            $resultado = $util->curl($ch, $url, $cookie);
            
            $dias2 = $util->verificaDados($urlBase, $cookie, $ch, 2, $cgc, $dtIni);
            $registra .= $util->geraDados($urlBase, $cookie, $ch, $dias2, 2, $cgc, $id, $dtIni);
            
            $url = $urlBase."/sindecconsulta/resultado.asp";
            $resultado = $util->curl($ch, $url, $cookie);
            
            // Acessa as CIPS do CGC recebido
            
            $url = $urlBase."/sindecconsulta/fa_forn_lista.asp?busca=1&codCNPJ=".$cgc;
            $resultado = $util->curl($ch, $url, $cookie);
            //var_dump($resultado);exit;
            // Verifica de quanto em quantos dias será extraído o resultado
            $dias = $util->verificaDados($urlBase, $cookie, $ch, 1, $cgc, $dtIni);
            $registra .= $util->geraDados($urlBase, $cookie, $ch, $dias, 1, $cgc, $id, $dtIni);
        } 
        
        $procons[$id]['valida'] = $registra;
        $procons[$id]['erro'] = '';
        $session->procon = $procons;

        
    }
     
        curl_close ($ch);
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_response->appendBody(Zend_Json::encode($procons[$id]));
    }
	
    public function concluidoAction() {
        $this->_helper->layout->disableLayout();
    }
}

