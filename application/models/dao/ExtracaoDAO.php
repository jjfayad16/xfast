<?php
/**
 * Classe que realiza interações com dados para contratos
 *
 */
class ExtracaoDAO extends Extra_Model_DAO {
	//private $recurso = 3;
	public static function build() {
		return new self();
	}
        
        public function curl($ch, $url, $cookie, $post_fields = null){
            
              curl_setopt_array($ch, array(
                    CURLOPT_URL => $url, //sem isso, seu cURL � imprest�vel
                    CURLOPT_POST => 1, //afirmo que ele ir� fazer um POST
                    CURLOPT_POSTFIELDS => $post_fields, //quais s�o os campos que estarei enviando ao valida.asp?
                    CURLOPT_USERAGENT => "Mozilla/5.0 (Windows; U; Windows NT 5.0; en-US; rv:1.4) Gecko/20030624 Netscape/7.1 (ax)", //ahh � importante sempre ter n� =D
                    CURLOPT_COOKIEFILE => $cookie, //lembra dos cookies que guardamos qndo digitamos o captcha? 
                    CURLOPT_COOKIEJAR => $cookie,  //ent�o, precisamos deles :)
                    CURLOPT_FOLLOWLOCATION => 1, // n�o quero explicar, mas � importante. pesquisa ae depois ;)
                    CURLOPT_RETURNTRANSFER => 1, // quer ver os dados? ent�o sempre ative esta op��o no seu script
                    CURLOPT_SSL_VERIFYPEER => 0,
                    CURLOPT_SSL_VERIFYHOST => 0,
                    CURLOPT_HEADER => 0, // sem header
              ));

              $data = curl_exec($ch);
              
              return $data;
        }
        
        public function curl2($ch, $url, $cookie, $post_fields = null){
            
              curl_setopt_array($ch, array(
                    CURLOPT_URL => $url, //sem isso, seu cURL � imprest�vel
                    CURLOPT_POST => 1, //afirmo que ele ir� fazer um POST
                    CURLOPT_POSTFIELDS => $post_fields, //quais s�o os campos que estarei enviando ao valida.asp?
                    CURLOPT_USERAGENT => "Mozilla/5.0 (Windows; U; Windows NT 5.0; en-US; rv:1.4) Gecko/20030624 Netscape/7.1 (ax)", //ahh � importante sempre ter n� =D
                    CURLOPT_COOKIEFILE => $cookie, //lembra dos cookies que guardamos qndo digitamos o captcha? 
                    CURLOPT_COOKIEJAR => $cookie,  //ent�o, precisamos deles :)
                    CURLOPT_FOLLOWLOCATION => 1, // n�o quero explicar, mas � importante. pesquisa ae depois ;)
                    CURLOPT_RETURNTRANSFER => 0, // quer ver os dados? ent�o sempre ative esta op��o no seu script
                    CURLOPT_SSL_VERIFYPEER => 0,
                    CURLOPT_SSL_VERIFYHOST => 0,
                    CURLOPT_HEADER => 0, // sem header
              ));

              $data = curl_exec($ch);
              
              return $data;
        }
        
        function tdrows($elements)
        {
            $str = "";
            foreach ($elements as $element)
            {
                $str .= $element->nodeValue . ", ";
            }
            //echo $str;exit;
            return $str;
        }
        
        public function getManifestos($conteudo) {
            $Manif = new DOMDocument('1.0', 'iso-8859-1');
            $Manif->preserveWhiteSpace = FALSE;
            //$retira = array('<img','&nbsp',';','-','.','"',"'",',','onClick=','javascript:window.open','imprimir','(',')');
            //var_dump($conteudo);exit;
            $Manif->loadHTML($conteudo);
            
            $searchNode = $Manif->getElementsByTagName('input'); 
            $manifesto = $searchNode->item(0)->getAttribute('value');
            
            return $manifesto;
            
        }
        
        public function getForm($conteudo) {
            $Manif = new DOMDocument('1.0', 'iso-8859-1');
            $Manif->preserveWhiteSpace = FALSE;
            //$retira = array('<img','&nbsp',';','-','.','"',"'",',','onClick=','javascript:window.open','imprimir','(',')');
            //var_dump($conteudo);exit;
            $Manif->loadHTML($conteudo);
            
            $searchNode = $Manif->getElementsByTagName('input'); 
            $postfields='';
            $i=0;
            foreach ($searchNode as $post) {
                if ($i > 0) $postfields .= "&";
                $postfields .= $post->getAttribute('name').'='.$post->getAttribute('value');
                $i++;
            }
            
            return $postfields;
            
        }
        
        public function getdata($contents, $urlBase, $cgc, $ch, $cookie)
        {
            $DOM = new DOMDocument('1.0', 'iso-8859-1');
            $DOM->preserveWhiteSpace = FALSE;
            $retira = array('<img','&nbsp',';','-','.','"',"'",',','onClick=','javascript:window.open','imprimir','(',')');
            $DOM->loadHTML(str_replace($retira," ",$contents));
            
            $items = $DOM->getElementsByTagName('tr');
            
            $array = array();
            $i=0;
            
            foreach ($items as $node)
            {
                //var_dump($node->attributes);exit;
                $valor = explode(',',$this->tdrows($node->childNodes));
                //var_dump($valor);exit;
                
                $indice = explode(' ',trim($valor[4]));
                
                $anexo = strpos($valor[6],'Anexo'); 
                if (is_numeric($anexo)) {
                    $array[$i]['ic_anexo']      = 'S';
                } else {
                    $array[$i]['ic_anexo']      = 'N';
                }
                    
                    $array[$i]['dt_procon']     = trim($valor[8]);
                    $array[$i]['dt_aviso']      = trim($valor[14]);
                    $array[$i]['dt_prazo']      = trim($valor[16]);


                    //var_dump($valor);exit;
                    $array[$i]['nu_fa']         = $indice[3];
                    $array[$i]['id_fa']         = $indice[0];
                    $array[$i]['no_consumidor'] = utf8_decode(trim($valor[10]));
                    $array[$i]['tipo'] = 1;
                    
                    
                    
                    if (is_numeric(trim($indice[0])) && is_numeric(trim($indice[3])) && is_numeric(trim($indice[6]))) {
                        /* $indice[0] = '1986011';
                        $indice[3] = '08143571674';
                        $indice[6] = '00360305000104'; */
                        $url = $urlBase."/sindecconsulta/Scripts/print_form.asp?tn=RCL&intCodTermoNot=".trim($indice[0])."&opTermo=Fechar&strcodfa=".trim($indice[3])."&strCNPJ=".trim($indice[6])."&tipo=";
                        $resultado = $this->curl($ch, $url, $cookie);
                        
                        
                        $retorno = $this->getManifestos($resultado);
                        $array[$i]['manifesto'] = html_entity_decode($retorno);
                        /* preg_match_all('/CIP(.+)&gt;"/s', $resultado, $retorno);
                        $array[$i]['manifesto'] = "CIP:".html_entity_decode($retorno[1][0]); */
                    } else {
                        $array[$i]['manifesto'] = "Erro Manifesto";
                    }
                    $i++;
            }
            
            //var_dump($array);exit;
            return $array;
        }
        
        public function getdataAdm($contents, $urlBase, $cgc, $ch, $cookie)
        {
            $DOM = new DOMDocument('1.0', 'iso-8859-1');
            $DOM->preserveWhiteSpace = FALSE;
            //var_dump($contents);exit;
            
            $retira = array('<img','&nbsp',';','-','.','"',"'",',','onClick=','javascript:window.open','imprimirReclamacao','(',')');
            $DOM->loadHTML(str_replace($retira," ",$contents));
            
            $items = $DOM->getElementsByTagName('tr');
            $array = array();
            $i=0;
            foreach ($items as $node)
            {
                $valor = explode(',',$this->tdrows($node->childNodes));
                
                $anexo = strpos($valor[4],'anexos'); 
                $indice = explode(' ',trim($valor[2]));
                
                    $array[$i]['nu_fa']         = trim($indice[3]);
                    $array[$i]['id_fa']         = trim($indice[0]);
                    $array[$i]['tipo'] = 2;
                    
                    if (is_numeric(trim($indice[0])) && is_numeric(trim($indice[3])) && is_numeric(trim($indice[6]))) {
                        $url = $urlBase."/sindecconsulta/Scripts/print_form.asp?tn=RCL&intCodTermoNot=".trim($indice[0])."&opTermo=Fechar&strcodfa=".trim($indice[3])."&strCNPJ=".trim($indice[6])."&tipo=";
                        
                        $resultado = $this->curl($ch, $url, $cookie);
                        //var_dump($resultado);exit;
                        $retorno = $this->getManifestos($resultado);
                        $array[$i]['manifesto']         = html_entity_decode($retorno);
                        //$array[$i]['manifesto']         = html_entity_decode($retorno[1][0]);
                        $array[$i]['dt_procon']         = trim($valor[6]);
                        $array[$i]['no_consumidor']     = utf8_decode(trim($valor[8]));
                        
                    } else {
                        $array[$i]['manifesto']         = "Erro Manifesto";
                        $array[$i]['dt_procon']         = trim($valor[6]);
                        $array[$i]['no_consumidor']     = utf8_decode(trim($valor[8]));
                        
                    }
                    if (is_numeric($anexo)) $array[$i]['ic_anexo']      = 'S';
                    else $array[$i]['ic_anexo']      = 'N';
                    
                    $i++;
            }
            //var_dump($array);exit;
            return $array;
        }
        
        public function geraDados($urlBase, $cookie, $ch, $dias = null, $tipo = null, $cgc, $id, $dtIni) {
            $dtIni = $this->transformaDataTela($dtIni);
            $dtFimCompara = $this->dataSql();
            if (!$dias) {
                $dtFim = $this->dataAtualTela();
                $dias = 1;
            } else 
                $dtFim = $this->dataAddDias($dtIni, $dias);
            $dtFimComparaX = $this->transformaDataSql($dtFim);
            $dtFimCompara = $this->dataSql();
            //echo 'ok';
            while ($dtFimComparaX <= $dtFimCompara) {
                $valor = $this->retornaFAs($urlBase, $cookie, $dtIni, $dtFim, $ch, $tipo, $cgc);
                $dtIni = $dtFim;
                $dtFim = $this->dataAddDias($dtFim, $dias);
                $dtFimComparaX = $this->transformaDataSql($dtFim);
                
                foreach ($valor as $val) {
                    if (is_numeric($val['nu_fa'])) {
                        $chave = $val['nu_fa'].'-'.$tipo;
                        $compila[$chave] = $val;
                    }
                }
            }
            //var_dump($compila);exit;
            $registra = $this->gravaExtracao($compila, $id, $cgc, $tipo);
            return $registra;
        }
        
        public function gravaExtracao($valores, $id, $cgc, $tipo){
            
            //var_dump ($valores);exit;
            $db = Zend_Registry::get("db");
            $resultado = $db->fetchAll("select nu_fa, ic_tipo from tb_extracao_sindec where id_sindec = '$id'");
            $arrayRegistros = array();
            foreach ($resultado as $res) {
                $chave = $res['nu_fa'].'-'.$res['ic_tipo'];
                $arrayRegistros[$chave] = $chave;
            }
            
            Zend_Loader::loadClass('Extracao');
            $tbExtracao = new Extracao();
            $dtExtracao = $this->dataSql();
            $j=0;
            foreach ($valores as $key => $val) {
                if (!array_search($key,$arrayRegistros)) {
                    $row = $tbExtracao->createRow();
                    $row->id_sindec         = $id;
                    $row->dt_extracao       = $dtExtracao;
                    if(is_numeric(str_replace("/", "", $val['dt_procon'])))
                    $row->dt_procon         = $this->transformaDataSql($val['dt_procon']);
                    if(is_numeric(str_replace("/", "", $val['dt_aviso'])))
                        $row->dt_aviso          = $this->transformaDataSql($val['dt_aviso']);
                    if(is_numeric(str_replace("/", "", $val['dt_prazo'])))
                        $row->dt_prazo          = $this->transformaDataSql($val['dt_prazo']);
                    $row->nu_fa             = $val['nu_fa'];
                    $row->no_consumidor     = utf8_encode($val['no_consumidor']);
                    $row->manifesto         = $val['manifesto'];
                    $row->ic_tipo           = $val['tipo'];
                    $row->ic_anexo          = $val['ic_anexo'];
                    $row->save();
                    $j++;
                }
                
            }
            switch ($tipo) {
                case '1':
                    $ntipo = 'CIP';
                break;
                case '2':
                    $ntipo = 'Processo Administrativo';
                break;
            }
            
            $msg = $j." registros gravados para sindec de id:".$id." e CGC:".$cgc." tipo:".$ntipo." \n";
            //echo $msg;exit;
            return $msg;
        }
        
        public function retornaFAs($urlBase, $cookie, $dtIni, $dtFim, $ch, $tipo, $cgc) {
            //$ch = curl_init();
            $url = $urlBase."/sindecconsulta/fa_forn_lista.asp?dtInicio=".$dtIni."&dtFim=".$dtFim."&codCNPJ=".$cgc;
            //echo $url;exit;
            $resultado = $this->curl($ch, $url, $cookie);
            //var_dump($resultado);exit;
            if ($tipo == '2') {
                preg_match_all('/">Classifica(.+)table>/s', $resultado, $conteudo);
                $valor = $this->getdataAdm($conteudo[0][0], $urlBase, $cgc, $ch, $cookie);
                //var_dump($valor);exit;
            } else {
                preg_match_all('/Resultado<(.+)table>/s', $resultado, $conteudo);
                $valor = $this->getdata($conteudo[0][0], $urlBase, $cgc, $ch, $cookie);
            }
            //$this->verificaDados(2);
            
            
            //var_dump($valor);exit;
            return $valor;
        }
        
        public function verificaDados($baseUrl, $cookie, $ch, $tipo = null, $cgc, $dtIni) {
            $dtIni = $this->transformaDataTela($dtIni);
            $dtFim = $this->dataAtualTela();
            
            $conteudo = $this->retornaFAs($baseUrl, $cookie, $dtIni, $dtFim, $ch, $tipo, $cgc);
            $i=0;
            $retira = array('-','.');
            foreach ($conteudo as $cont) {
                if (!is_numeric(str_replace($retira,"",$cont['nu_fa'])) && $i == 0) {
                    preg_match_all('/Total de(.+)Registros/s', $cont['nu_fa'], $refina);
                    $i=1;
                }
            }
            
            $total = trim($refina[1][0]);
            if ($total <= 20) {
                $dias = null;
            } else {
                $resultado = $this->diffDate($dtIni, $dtFim, 'D','/');
                $valor = $total/$resultado;
                $dias = 20/$valor;
            }
            return $dias;
        }
        
        public function buscaSindecs(){
            Zend_Loader::loadClass('Sindec');
            $tbSindec = new Sindec();
            $sql = $tbSindec->select()->where("id_login is not null")->order("id_login");
            $sindecs = $tbSindec->fetchAll($sql);
            $array = array();
            foreach ($sindecs as $sindec) {
                $id = $sindec['id'];
                $array[$id]['nome']          = $sindec['no_municipio'];
                $array[$id]['url']           = $sindec['url'];
                $array[$id]['id_login']      = $sindec['id_login'];
                $array[$id]['usuario']       = $sindec['usuario'];
                $array[$id]['senha']         = $sindec['senha'];
                $array[$id]['id']            = $sindec['id'];
                $array[$id]['cgc']           = $sindec['nu_cgc'];
                $array[$id]['dtIni']         = $this->buscaMaxDataSindec($id);
                $array[$id]['valida']        = 0;
            }
            return $array;
        }
        
        public function buscaMaxIDSindec(){
           $db = Zend_Registry::get("db");
           $sql = "select max(id) id from tb_sindec";
           $consulta = $db->fetchRow($sql);
           
            return $consulta['id'];
        }
        
        public function buscaMaxDataSindec($id){
           $db = Zend_Registry::get("db");
           $sql = "select dt_procon from tb_extracao_sindec where id_sindec = '$id' order by dt_procon DESC limit 1";
           $consulta = $db->fetchRow($sql);
           
            return $consulta['dt_procon'];
        }
        
	public function transformaDataTela($datasql) {
		$data = new Zend_Date($datasql, 'pt_BR');
		$dataTela = $data->toString('dd/MM/yyyy');
		return $dataTela;
	}
	
	public function transformaDataHoraTela($datasql) {
		$data = new Zend_Date($datasql, 'pt_BR');
		$dataTela = $data->toString('dd/MM/yyyy HH:mm:ss');
		return $dataTela;
	}

	public function transformaDataSql($datatela) {
		$data = new Zend_Date($datatela, 'pt_BR');
		$dataSql = $data->toString('yyyy-MM-dd');
		return $dataSql;
	}
	
	public function dataAtualSql() {
		$dataAtual = new Zend_Date('pt_BR');
		$data = $dataAtual->toString('yyyy-MM-dd HH:mm:ss');
		return $data;
	}
	
	public function dataSql() {
		$dataAtual = new Zend_Date('pt_BR');
		$data = $dataAtual->toString('yyyy-MM-dd');
		return $data;
	}
        
        public function dataAtualTela() {
		$dataAtual = new Zend_Date('pt_BR');
		$data = $dataAtual->toString('dd/MM/yyyy');
		return $data;
	}
	
	public function horaSql() {
		$dataAtual = new Zend_Date('pt_BR');
		$data = $dataAtual->toString('HH:mm:ss');
		return $data;
	}
	
	public function dataSubMes($valor) {
		$datat = new Zend_Date($valor, 'pt_BR');
		$date = $datat->sub('1', Zend_Date::MONTH);
		$data = $date->toString('yyyy-MM-dd');
		return $data;
	}
	
	public function dataSub3Meses($valor) {
		$datat = new Zend_Date($valor, 'pt_BR');
		$date = $datat->sub('3', Zend_Date::MONTH);
		$data = $date->toString('yyyy-MM-dd');
		return $data;
	}
	
	public function dataAddMes($valor) {
		$datat = new Zend_Date($valor, 'pt_BR');
		$date = $datat->add('1', Zend_Date::MONTH);
		$data = $date->toString('yyyy-MM-dd');
		return $data;
	}
        
        public function dataAddDias($valor,$qtd) {
		$datat = new Zend_Date($valor, 'pt_BR');
		$date = $datat->add($qtd, Zend_Date::DAY);
		$data = $date->toString('dd/MM/yyyy');
		return $data;
	}
	
	function diffDate($d1, $d2, $type='', $sep='-')
	{
		 $d1 = explode($sep, $d1);
		 $d2 = explode($sep, $d2);
		 switch ($type)
		 {
		 case 'A':
		 $X = 31536000;
		 break;
		 case 'M':
		 $X = 2592000;
		 break;
		 case 'D':
		 $X = 86400;
		 break;
		 case 'H':
		 $X = 3600;
		 break;
		 case 'MI':
		 $X = 60;
		 break;
		 default:
		 $X = 1;
		 }
		 return floor(((mktime(0,0,0,$d2[1],$d2[0],$d2[2])-mktime(0,0,0,$d1[1],$d1[0],$d1[2]))/$X));
	}
		
}