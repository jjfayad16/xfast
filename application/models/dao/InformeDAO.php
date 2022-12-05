<?php
/**
 * Classe que realiza o calculo do AVCAIXA
 *
 */
class InformeDAO extends Extra_Model_DAO {
	
	public static function build() {
		return new self();
	}
        
        public function getNomeProduto($id) {
            Zend_Loader::loadClass('Produto');
            if (is_numeric($id)) {
                $tbProduto = new Produto();
                $produto = $tbProduto->fetchRow("id = '$id'");
                return $produto['no_produto'];
            } else 
                return 'Todos';
        }
	
	public function qtdTop10($id) {
            
            Zend_Loader::loadClass('UtilDAO');
            $util = new UtilDAO();
            
		$db = Zend_Registry::get("db");
                $sql = "select id_produto, dt_mes
                            from tb_top10 as topa
				WHERE topa.id_produto in (SELECT top.id_produto FROM tb_top10 as top where top.dt_mes = topa.dt_mes order by top.qtd DESC limit 10)
					and topa.id_produto = '$id'";
                //echo $sql;exit;
                $resultado = $db->fetchAll($sql);
                
                $sql2 = "select count(DISTINCT dt_mes) as qtd
                            from tb_top10";
                //echo $sql;exit;
                $resultado2 = $db->fetchRow($sql2);
                
                $db->closeConnection();
                $resposta = '<h4 style="margin-top:-3px;">Em '.$resultado2['qtd'].' meses o produto selecionado figurou '.sizeof($resultado).' vezes no TOP 10!</h4>';
                return $resposta;
	}
        
        public function qtdProcon($id) {
            
            Zend_Loader::loadClass('UtilDAO');
            $util = new UtilDAO();
            
            $list = '';
                $arrayid = array();
                if ($id) {
                    $in = "(";
                    $ids = explode('-',$id);
                    //var_dump($ids);exit;
                    foreach ($ids as $num) {
                        if (!key_exists($num, $arrayid)) {
                            $arrayid[$num] = $num;
                            $in .= "'$num',";
                        }
                    }
                    $in = substr($in,0,-1).")";
                    $list = "$in";
                }
            
		$db = Zend_Registry::get("db");
                $sql = "select DISTINCT ON(topa.dt_mes) dt_mes, id_item
                            from tb_rkprocon as topa
				WHERE topa.id_item in $list";
                //echo $sql;exit;
                $resultado = $db->fetchAll($sql);
                
                $sql2 = "select count(DISTINCT dt_mes) as qtd
                            from tb_rkprocon";
                //echo $sql;exit;
                $resultado2 = $db->fetchRow($sql2);
                
                $db->closeConnection();
                $resposta = '<h4 style="margin-top:-3px;">Em '.$resultado2['qtd'].' meses o produto selecionado figurou '.sizeof($resultado).' vezes no TOP 10!</h4>';
                return $resposta;
	}
        
        public function qtdTotal($id = null, $mes = null) {
                Zend_Loader::loadClass('UtilDAO');
                $util = new UtilDAO();
                
                //$list = 'where';
                /* if ($mes) $list .= " dt_mes is not null";
                else $list .= " dt_mes >= '2014-01-01'"; */
                
                if ($id) $list = " and id_produto = '$id'";
                   
                // $data = $util->dataSql();
                
		$db = Zend_Registry::get("db");
                $sqlD = "with total_ranking_previa AS (
                            select count(dt_ranking_previa) as qtd1, dt_ranking_previa as data1 from tb_bacen where dt_ranking_previa in (select max(dt_ranking_previa) from tb_bacen 
                                                                    where dt_ranking_previa is not null
                                                                    group by dt_ranking_previa order by dt_ranking_previa DESC limit 5) group by dt_ranking_previa order by dt_ranking_previa DESC),
                            total_leituras AS (
                            select count(dt_ranking_previa) as qtd2, dt_ranking_previa as data2 from tb_bacen where dt_ranking_previa in (select max(dt_ranking_previa) from tb_bacen 
                                                                    where dt_ranking_previa is not null
                                                                    group by dt_ranking_previa order by dt_ranking_previa DESC limit 5) and dt_leitura is not null group by dt_ranking_previa order by dt_ranking_previa DESC)
                            select t1.data1 from total_ranking_previa as t1, total_leituras as t2 where t1.data1 = t2.data2 and t1.qtd1 = t2.qtd2 limit 1
                            ";
                $resultadoD = $db->fetchRow($sqlD);
                $dataD = $resultadoD['data1'];
                
                $sql = "select count(1) qtd, 
                            CASE WHEN dt_ranking_previa is not null THEN to_char(dt_ranking_previa, 'YYYY/MM/01') 
                                    WHEN dt_ranking is not null THEN to_char(dt_ranking, 'YYYY/MM/01')
                                    END as dt_mes,
                            CASE WHEN dt_ranking_previa is not null THEN to_char(dt_ranking_previa, 'YYYY-MM-01')
                                    WHEN dt_ranking is not null THEN to_char(dt_ranking, 'YYYY-MM-01')
                                    END as dt_compara
                                    from tb_bacen 
                                        where id_usuario is not null and st_ativo <> '0' and dt_ranking_previa <= '$dataD' $list
                                        group by dt_mes, dt_compara order by dt_mes ASC";
                //echo $sql;exit;
                $resultado = $db->fetchAll($sql);
                $db->closeConnection();
                $resposta = array();
                
                $valores = $this->calculoDias($dataD);
                $dtMes = $valores[2];
                $dtAnt = $util->dataSubMes($dtMes);
                foreach ($resultado as $res) {
                    $data = $res['dt_mes'];
                    $nData = $util->dataNome($data);
                    if ($dtMes == $res['dt_compara']) {
                        $qtd = round(($res['qtd']/$valores[0])*$valores[1],0);
                        $resposta[$data]['projecao'] = $qtd;
                        $resposta[$data]['dia'] = $util->transformaDataTela($dataD);
                    } else if ($dtAnt == $res['dt_compara']) {
                        $resposta[$data]['projecao'] = $res['qtd'];
                    }
                    $qtd = $res['qtd'];
                    $resposta[$data]['data'] = $nData;
                    $resposta[$data]['bacen'] = $qtd;
                }
                return $resposta;
	}
        
        public function motivosProduto($id = null) {
                Zend_Loader::loadClass('UtilDAO');
                $util = new UtilDAO();
                
                //$list = 'where';
                /* if ($mes) $list .= " dt_mes is not null";
                else $list .= " dt_mes >= '2014-01-01'"; */
                
                if ($id) $list = " and b.id_produto = '$id'";
                   
                
		$db = Zend_Registry::get("db");
                /* $sql = "select sum(qtd) qtd, TO_CHAR(dt_mes, 'YYYY/MM/DD') as dt_mes, dt_mes as dt_compara
                            from tb_top10 $list
					group by dt_mes order by dt_compara ASC";
                */
                $sql = "select count(1) qtd, oq.no_motivo, uni.no_vice,
                            CASE WHEN b.dt_ranking_previa is not null THEN to_char(b.dt_ranking_previa, 'YYYY/MM/01') 
                                    WHEN b.dt_ranking is not null THEN to_char(b.dt_ranking, 'YYYY/MM/01')
                                    END as dt_mes,
                            CASE WHEN b.dt_ranking_previa is not null THEN to_char(b.dt_ranking_previa, 'YYYY-MM-01')
                                    WHEN b.dt_ranking is not null THEN to_char(b.dt_ranking, 'YYYY-MM-01')
                                    END as dt_compara
                                    from tb_bacen as b
					inner join tb_oque_irritou as oq on oq.id = b.id_que_irritou 
					inner join tb_unidade as uni on uni.id = b.id_unidade
                                        where b.id_usuario is not null and (b.dt_ranking is not null or b.dt_ranking_previa is not null) $list
                                        group by dt_mes, dt_compara, oq.no_motivo, uni.no_vice order by dt_mes ASC";
                //echo $sql;exit;
                $resultado = $db->fetchAll($sql);
                $db->closeConnection();
                $i=0;
                $data = $resultado[0]['dt_compara'];
                $width='400px';
                foreach ($resultado as $res) {
                    $data2 = $res['dt_compara'];
                    if (strlen($res['no_motivo']) > '40') $width ='1';
                    
                    if ($data == $data2) {
                        if (empty($resposta['motivo'.$i]['texto']))
                            $resposta['motivo'.$i]['texto'] = '<span style="color:#4572A7;">'.$res['no_vice'].'</span> - '.$res['no_motivo'].': <b>'.$res['qtd'].'</b>';
                        else
                            $resposta['motivo'.$i]['texto'] .= '<br><span style="color:#4572A7;">'.$res['no_vice'].'</span> - '.$res['no_motivo'].': <b>'.$res['qtd'].'</b>';
                    } else {
                        $data = $data2;
                        $i++;
                        if (empty($resposta['motivo'.$i]['texto']))
                            $resposta['motivo'.$i]['texto'] = '<span style="color:#4572A7;">'.$res['no_vice'].'</span> - '.$res['no_motivo'].': <b>'.$res['qtd'].'</b>';
                        else
                            $resposta['motivo'.$i]['texto'] .= '<br><span style="color:#4572A7;">'.$res['no_vice'].'</span> - '.$res['no_motivo'].': <b>'.$res['qtd'].'</b>';
                    }
                }
                $retorno ='';
                foreach ($resposta as $key => $value) {
                    $retorno .= "<div id='".$key."' style='display:none;'>".$value['texto']."</div>"; 
                }
                if ($width == '1') $width = '500px';
                $retorno .= "<div id='larger' style='display:none;'>".$width."</div>";
                return $retorno;
	}
        
        
        public function colocacaoProcon() {
                
             Zend_Loader::loadClass('UtilDAO');
             $util          = new UtilDAO();
             
             Zend_Loader::loadClass('ResolubilidadeProcon');
             $table         = new ResolubilidadeProcon(); 
             
             $sql = $table->select()->order("co_tipo ASC")->order("dt_mes ASC")->order("vr_resolubilidade DESC");
            
             $resultado = $table->fetchAll($sql);
                
                $i=0;
                $data = $resultado[0]['dt_mes'];
                $width='400px';
                $cip = 0;
                foreach ($resultado as $res) {
                    $data2 = $res['dt_mes'];
                    if (strlen($res['no_banco']) > '40') $width ='1';
                     $span = '';
                     if ($res['no_banco'] == 'Caixa Econômica Federal') $span = 'style="color:#4572A7;"';
                     if ($res['co_tipo'] == '1') $chave = 'colocacaocip';
                    else { 
                        if ($chave != 'colocacaoadm') { 
                            $i=0;
                            $data = $data2;
                            $cip=0;
                         }
                            
                        $chave = 'colocacaoadm';
                    }
                    if ($data == $data2) {
                        $cip++;
                        if (empty($resposta[$chave.$i]['texto']))
                            $resposta[$chave.$i]['texto'] = '<b>'.$cip.'º</b> <span '.$span.'>'.$res['no_banco'].'</span> - '.$res['vr_resolubilidade'].' %';
                        else
                            $resposta[$chave.$i]['texto'] .= '<br><b>'.$cip.'º</b> <span '.$span.'>'.$res['no_banco'].'</span> - '.$res['vr_resolubilidade'].' %';
                    } else {
                        $cip=1;
                        $data = $data2;
                        $i++;
                        if (empty($resposta[$chave.$i]['texto']))
                            $resposta[$chave.$i]['texto'] = '<b>'.$cip.'º</b> <span '.$span.'>'.$res['no_banco'].'</span> - '.$res['vr_resolubilidade'].' %';
                        else
                            $resposta[$chave.$i]['texto'] .= '<br><b>'.$cip.'º</b> <span '.$span.'>'.$res['no_banco'].'</span> - '.$res['vr_resolubilidade'].' %';
                    }
                }
                $retorno ='';
                foreach ($resposta as $key => $value) {
                    $retorno .= "<div id='".$key."' style='display:none;'>".$value['texto']."</div>"; 
                }
                if ($width == '1') $width = '500px';
                $retorno .= "<div id='larger' style='display:none;'>".$width."</div>";
                
                //echo $retorno;exit;
                return $retorno;
	}
        
        public function ocorrenciasProduto($id = null) {
                Zend_Loader::loadClass('UtilDAO');
                $util = new UtilDAO();
                
                //$list = 'where';
                /* if ($mes) $list .= " dt_mes is not null";
                else $list .= " dt_mes >= '2014-01-01'"; */
                
                if ($id) $list = " and b.id_produto = '$id'";
                   
                
		$db = Zend_Registry::get("db");
                /* $sql = "select sum(qtd) qtd, TO_CHAR(dt_mes, 'YYYY/MM/DD') as dt_mes, dt_mes as dt_compara
                            from tb_top10 $list
					group by dt_mes order by dt_compara ASC";
                */
                $sql = "select b.rdr, b.id_ocorrencia, prd.no_produto, oq.no_motivo, uni.no_vice,
                            CASE WHEN b.dt_ranking_previa is not null THEN to_char(b.dt_ranking_previa, 'YYYY/MM/01') 
                                    WHEN b.dt_ranking is not null THEN to_char(b.dt_ranking, 'YYYY/MM/01')
                                    END as dt_mes,
                            CASE WHEN b.dt_ranking_previa is not null THEN to_char(b.dt_ranking_previa, 'YYYY-MM-01')
                                    WHEN b.dt_ranking is not null THEN to_char(b.dt_ranking, 'YYYY-MM-01')
                                    END as dt_compara
                                    from tb_bacen as b
                                        inner join tb_produto as prd on prd.id = b.id_produto 
					inner join tb_oque_irritou as oq on oq.id = b.id_que_irritou 
					inner join tb_unidade as uni on uni.id = b.id_unidade
                                        where b.id_usuario is not null and (b.dt_ranking is not null or b.dt_ranking_previa is not null) $list
                                        order by dt_mes ASC";
                //echo $sql;exit;
                $resultado = $db->fetchAll($sql);
                $db->closeConnection();
                $i=0;
                $j=0;
                $data = $resultado[0]['dt_compara'];
                foreach ($resultado as $res) {
                    $data2 = $res['dt_compara'];
                    if ($data == $data2) {
                       $resposta[$i][$j] = $res;
                    } else {
                        $data = $data2;
                        $i++;
                       $resposta[$i][$j] = $res;
                    }
                    $j++;
                }
                
                return $resposta;
	}
        
        public function qtdTotalProcon($id = null) {
                Zend_Loader::loadClass('UtilDAO');
                $util = new UtilDAO();
                
                $list = '';
                $arrayid = array();
                if ($id) {
                    $in = "(";
                    $ids = explode('-',$id);
                    //var_dump($ids);exit;
                    foreach ($ids as $num) {
                        if (!key_exists($num, $arrayid)) {
                            $arrayid[$num] = $num;
                            $in .= "'$num',";
                        }
                    }
                    $in = substr($in,0,-1).")";
                    $list = "where rk.id_item in $in";
                }
                if ($list == '') {
                    $list = "where dt_mes in (select rk2.dt_mes from tb_rkprocon as rk2 group by rk2.dt_mes order by rk2.dt_mes DESC limit 7)";
                }
                
		$db = Zend_Registry::get("db");
                $sql = "select sum(rk.qtd) qtd, sum(rk.nu_resolv) resolv, TO_CHAR(rk.dt_mes, 'YYYY/MM/DD') as dt_mes, rk.dt_mes as dt_compara, rk.nu_tipo as tipo
                            from tb_rkprocon as rk
                                $list 
					group by dt_mes, tipo order by rk.dt_mes ASC";
                //echo $sql;exit;
                $resultado = $db->fetchAll($sql);
                $db->closeConnection();
                $resposta = array();
                
                /*$valores = $this->calculoDias();
                $dtMes = $valores[2];
                $dtAnt = $util->dataSubMes($dtMes);*/
                foreach ($resultado as $res) {
                    $data = $res['dt_mes'];
                    $nData = $util->dataNome($data);
                    /*if ($dtMes == $res['dt_compara']) {
                        $qtd = round(($res['qtd']/$valores[0])*$valores[1],0);
                        $resposta[$data]['projecao'] = $qtd;
                    } else if ($dtAnt == $res['dt_compara']) {
                        $resposta[$data]['projecao'] = $res['qtd'];
                    }*/
                    $resolv = round(($res['resolv']/$res['qtd'])*100,0);
                    
                    $resposta[$data]['data'] = $nData;
                    if ($res['tipo'] == 1)
                    $resposta[$data]['proconcip'] = $resolv;
                    else if ($res['tipo'] == 2)
                    $resposta[$data]['proconrec'] = $resolv;
                }
                return $resposta;
	}
        
        public function qtdTotalProconRes($tipo) {
                Zend_Loader::loadClass('UtilDAO');
                $util = new UtilDAO();
                
                Zend_Loader::loadClass('ResolubilidadeProcon');
                $table         = new ResolubilidadeProcon(); 

                $sql = $table->select()->where("co_tipo = '$tipo'")->where("no_banco ~* 'caixa'")->order("dt_mes ASC");

                $resultado = $table->fetchAll($sql);
                
                $resposta = array();
                
                /*$valores = $this->calculoDias();
                $dtMes = $valores[2];
                $dtAnt = $util->dataSubMes($dtMes);*/
                foreach ($resultado as $res) {
                    $data = $res['dt_mes'];
                    $nData = $util->dataNome($data);
                    
                    $resposta[$data]['data'] = $nData;
                    $resposta[$data]['proconcip'] = str_replace(",",".",$res['vr_resolubilidade']);
                    
                }
                return $resposta;
	}
        
        public function qtdMesBacen() {
            
		$db = Zend_Registry::get("db");
                /* $sql = "select top.qtd, TO_CHAR(top.dt_mes, 'MM/YYYY') as dt_mes, top.dt_mes as dt_ordena, prod.no_produto, top.id_produto, top.no_unidaderesp
                            from tb_bacen as top, tb_produto as prod
                                where prod.id = top.id_produto
                                    and top.dt_mes in (select case when dt_ranking_previa is not null then dt_ranking_previa
                                                                when dt_ranking is not null then dt_ranking end as dt_mes from tb_bacen group by dt_mes order by dt_mes DESC limit 7)
					order by dt_ordena ASC"; */
                
                $sql = "select * from (select count(top.id_ocorrencia) as qtd, prod.no_produto, top.id_produto,
                            CASE WHEN dt_ranking_previa is not null THEN to_char(dt_ranking_previa, 'MM/YYYY') 
                                                                WHEN dt_ranking is not null THEN to_char(dt_ranking, 'MM/YYYY')
                                                                END as dt_mes,
                            CASE WHEN dt_ranking_previa is not null THEN TO_CHAR(dt_ranking_previa, 'YYYY-MM-01')
                                                                WHEN dt_ranking is not null THEN TO_CHAR(dt_ranking, 'YYYY-MM-01')
                                                                END as dt_ordena
                                                        from tb_bacen as top, tb_produto as prod
                                                            where prod.id = top.id_produto group by no_produto, id_produto, dt_mes, dt_ordena) as refina where
                                                                dt_ordena in (select case when dt_ranking_previa is not null then TO_CHAR(dt_ranking_previa, 'YYYY-MM-01')
                                                                                            when dt_ranking is not null then TO_CHAR(dt_ranking, 'YYYY-MM-01') end as dt_mes 
                                                                                              from tb_bacen where dt_ranking_previa is not null or dt_ranking is not null group by dt_mes order by dt_mes DESC limit 7)
                                                                    order by dt_ordena ASC";
                                                
                //echo $sql;exit;
                $resultado = $db->fetchAll($sql);
                
                //$db = Zend_Registry::get("db");
                $sqlD = "with total_ranking_previa AS (
                            select count(dt_ranking_previa) as qtd1, dt_ranking_previa as data1 from tb_bacen where dt_ranking_previa in (select max(dt_ranking_previa) from tb_bacen 
                                                                    where dt_ranking_previa is not null
                                                                    group by dt_ranking_previa order by dt_ranking_previa DESC limit 5) group by dt_ranking_previa order by dt_ranking_previa DESC),
                            total_leituras AS (
                            select count(dt_ranking_previa) as qtd2, dt_ranking_previa as data2 from tb_bacen where dt_ranking_previa in (select max(dt_ranking_previa) from tb_bacen 
                                                                    where dt_ranking_previa is not null
                                                                    group by dt_ranking_previa order by dt_ranking_previa DESC limit 5) and dt_leitura is not null group by dt_ranking_previa order by dt_ranking_previa DESC)
                            select t1.data1 from total_ranking_previa as t1, total_leituras as t2 where t1.data1 = t2.data2 and t1.qtd1 = t2.qtd2 limit 1
                            ";
                $resultadoD = $db->fetchRow($sqlD);
                $dataD = $resultadoD['data1'];
                
                $sql2 = "select case when dt_ranking_previa is not null then TO_CHAR(dt_ranking_previa, 'YYYY-MM')
                                     when dt_ranking is not null then TO_CHAR(dt_ranking, 'YYYY-MM') end as dt_mes 
                                        from tb_bacen where dt_ranking_previa is not null or dt_ranking is not null group by dt_mes order by dt_mes DESC limit 3";
                //echo $sql;exit;
                $meses = $db->fetchAll($sql2);
                
                $mesFim = $meses[0]['dt_mes'];
                $mesFim  = substr($mesFim,5,2).'/'.substr($mesFim,0,4);
                $mesMeio = $meses[1]['dt_mes'];
                $mesMeio  = substr($mesMeio,5,2).'/'.substr($mesMeio,0,4);
                $mesIni = $meses[2]['dt_mes'];
                $mesIni  = substr($mesIni,5,2).'/'.substr($mesIni,0,4);
                
                
                $resposta = array();
                $valores = $this->calculoDias($dataD);
                $dtMes = $valores[2];
                foreach ($resultado as $res) {
                    $data = $res['dt_mes'];
                    $prod = $res['no_produto'];
                    if ($res['dt_mes'] = $mesFim) {
                    $sql3 = "select uni.no_vice from tb_bacen as b
                                inner join tb_unidade as uni on uni.id = b.id_unidade and uni.no_vice is not null
                                    where b.id_produto ='".$res['id_produto']."' and (TO_CHAR(dt_ranking,'MM/YYYY') = '$mesFim' or TO_CHAR(dt_ranking_previa,'MM/YYYY') = '$mesFim') group by uni.no_vice";
                    
                    $res3 = $db->fetchAll($sql3);
                    $resposta[1][$prod]['unidade'] = $res3;
                    }
                    
                    $resposta[0][$data] = $data; 
                    $resposta[1][$prod]['nome'] = $prod;
                    
                    $resposta[1][$prod]['id']   = $res['id_produto'];
                    $resposta[1][$prod][$data]  = $res['qtd'];
                    
                    if ($dtMes == $res['dt_ordena']) {
                        $qtd = round(($res['qtd']/$valores[0])*$valores[1],0);
                        $resposta[0]['projeção']  = 'projeção'; 
                        $resposta[1][$prod]['projeção']  = $qtd; 
                    }
                }
                $db->closeConnection();
                $listaacoes = $this->listaAcoes();
                foreach ($resposta[1] as $refina) {
                    $nome = $refina['nome'];
                    $id = $refina['id'];
                    if (array_search($id,$listaacoes))
                        $resposta[1][$nome]['acao'] = 'SIM';
                    else 
                        $resposta[1][$nome]['acao'] = 'NAO';
                    if ($refina['projeção'] < $refina[$mesMeio] && $refina['projeção'] < $refina[$mesIni]) $resposta[1][$nome]['tendencia'] = 2;
                    if ($refina['projeção'] > $refina[$mesMeio] && $refina['projeção'] > $refina[$mesIni]) $resposta[1][$nome]['tendencia'] = 1;
                }
                
                
                return $resposta;
	}
        
        public function qtdMesProcon() {
                Zend_Loader::loadClass('TextmineDAO');
                $textDAO = new TextmineDAO();
		
                $db = Zend_Registry::get("db");
                $sql = "select pc.qtd, pc.nu_resolv as resolv, TO_CHAR(pc.dt_mes, 'MM/YYYY') as dt_mes, TO_CHAR(pc.dt_mes, 'YYYY/MM/DD') as mes_t, pc.dt_mes as dt_ordena, it.no_item, pc.id_item, pc.nu_tipo as tipo, it.no_vp as no_sigla
                            from tb_rkprocon as pc, tb_item as it
                                where it.id = pc.id_item and nu_tipo = '1'
                                    and pc.dt_mes in (select dt_mes from tb_rkprocon group by dt_mes order by dt_mes DESC limit 6)
					order by dt_ordena ASC";
                //echo $sql;exit;
                $resultado = $db->fetchAll($sql);
                
                $sql2 = "select TO_CHAR(top.dt_mes, 'MM/YYYY') as dt_mes, top.dt_mes as dt_ordena
                            from tb_rkprocon as top
                                group by dt_ordena, dt_mes order by dt_ordena DESC limit 3";
                //echo $sql;exit;
                $meses = $db->fetchAll($sql2);
                
                $mesFim = $meses[0]['dt_mes'];
                $mesMeio = $meses[1]['dt_mes'];
                $mesIni = $meses[2]['dt_mes'];
                
                $db->closeConnection();
                $resposta = array();
                $refina = array();
                //$valores = $this->calculoDias();
                //$dtMes = $valores[2];
                $proconTotal = $this->qtdTotalProcon();
                //var_dump($proconTotal);exit;
                $total = 0;
                
                foreach ($resultado as $res1) {
                    $no_item = trim(eregi_replace("[^0-z]", " ", $textDAO->tiraAcento($res1['no_item'])));
                    $no_item2 = trim(eregi_replace("[^0-z]", "", $textDAO->tiraAcento($res1['no_item'])));
                    //echo $no_item.'<br>';
                    $data = $res1['dt_mes'];
                    $chave = $no_item2.'-'.$data;
                    //var_dump($no_item);
                    if (empty($refina[1][$chave]['qtd'])) $refina[1][$chave]['qtd'] = 0;
                    $refina[1][$chave]['qtd'] += $res1['qtd'];
                    if (empty($refina[1][$chave]['resolv'])) $refina[1][$chave]['resolv'] = 0;
                    $refina[1][$chave]['resolv'] += $res1['resolv'];
                    $refina[1][$chave]['unidade'] = $res1['no_sigla'];
                    $refina[1][$chave]['dt_mes']       = $data;
                    $refina[1][$chave]['tipo']         = $res1['tipo'];
                    $refina[1][$chave]['no_item']      = $no_item;
                    $refina[1][$chave]['no_item2']      = $no_item2;
                    if (empty($refina[0][$no_item2]['id_item'])) $refina[0][$no_item2]['id_item'] = $res1['id_item'];
                    else $refina[0][$no_item2]['id_item'] .= '-'.$res1['id_item'];
                    $refina[1][$chave]['mes_t']        = $res1['mes_t'];
                }
                //exit;
                foreach ($refina[1] as $res) {
                    $total += $res['qtd'];
                    $data = $res['dt_mes'];
                    $tipo = $res['tipo'];
                    $item = trim($res['no_item']);
                    $item2 = trim($res['no_item2']);
                    $resposta[0][$data] = $data; 
                    $resposta[1][$item2]['nome'] = $item;
                    $resposta[1][$item2]['item2'] = $item2;
                    $resposta[1][$item2]['unidade'] = $res['unidade'];
                    if (empty($resposta[1]['total'][$data][1])) {
                        $resposta[1]['total'][$data][1] = $proconTotal[$res['mes_t']]['proconcip'].'%';
                    }
                    if (empty($resposta[1][$item2]['qtd'][$tipo])) $resposta[1][$item2]['qtd'][$tipo] = 0;
                    $resposta[1][$item2]['qtd'][$tipo] += $res['qtd'];
                    $resposta[1][$item2]['id']   = $refina[0][$item2]['id_item'];
                    $resposta[1][$item2][$data][$tipo]  = round(($res['resolv']/$res['qtd'])*100,0).'%';
                    
                }
                $resposta[1]['total']['qtd'][1] = $total;
                $resposta[1]['total']['nome'] = 'Total';
                
                $listaacoes = $this->listaAcoesProcon();
                //var_dump($resposta[1]);exit;
                foreach ($resposta[1] as $refina) {
                    $nome = $refina['item2'];
                    if ($nome != '') {
                        $id2 = explode('-',$refina['id']);
                        foreach ($id2 as $id) {
                        if (array_search($id,$listaacoes))
                            $resposta[1][$nome]['acao'] = 'SIM';
                        else if (empty($resposta[1][$nome]['acao'])) 
                            $resposta[1][$nome]['acao'] = 'NAO';
                        }
                        if (substr($refina[$mesFim][1],0,-1) < substr($refina[$mesMeio][1],0,-1) && substr($refina[$mesFim][1],0,-1) < substr($refina[$mesIni][1],0,-1)) $resposta[1][$nome]['tendencia'] = 2;
                        if (substr($refina[$mesFim][1],0,-1) > substr($refina[$mesMeio][1],0,-1) && substr($refina[$mesFim][1],0,-1) > substr($refina[$mesIni][1],0,-1)) $resposta[1][$nome]['tendencia'] = 1;
                    }
                }
                
                return $resposta;
	}
        
        public function listaAcoes() {
            $db = Zend_Registry::get("db");
            $sql = "select ac.id_produto
                        from tb_acoes as ac group by ac.id_produto";
                
            $resultado = $db->fetchAll($sql);
            $resposta = array();
            foreach ($resultado as $res) {
                $resposta[$res['id_produto']] = $res['id_produto'];
            }
            return $resposta;
        }
        
        public function listaAcoesProcon() {
            $db = Zend_Registry::get("db");
            $sql = "select ac.id_item
                        from tb_acoesprocon as ac group by ac.id_item";
                
            $resultado = $db->fetchAll($sql);
            $resposta = array();
            foreach ($resultado as $res) {
                $resposta[$res['id_item']] = $res['id_item'];
            }
            return $resposta;
        }
        
        public function acoes($id) {
                Zend_Loader::loadClass('UnidadeAcao');
                $tbUniAcao = new UnidadeAcao();
            
            
                $db = Zend_Registry::get("db");
                $sql = "select ac.id, ac.no_descricao, ac.no_observacoes, TO_CHAR(ac.dt_mes, 'MM/YYYY') as dt_mes, TO_CHAR(ac.dt_prazo, 'DD/MM/YYYY') as dt_prazo, ac.id_unidade_registro, ac.dt_registro, ac.hr_registro, ac.id_unidade_acao, ac.nu_percentual,
                            CASE WHEN DATE(ac.dt_prazo) - DATE(NOW()) <= 0 and ac.nu_percentual < 100 THEN 'critico'
                                 WHEN DATE(ac.dt_prazo) - DATE(NOW()) > 0 and ac.nu_percentual < 100 THEN 'medio' 
                                 WHEN ac.nu_percentual = 100 THEN 'leve' 
                                 end as background
                            from tb_acoes as ac
                                WHERE ac.id_produto = '$id'";
                //echo $sql;exit;
                $resultado[0] = $db->fetchAll($sql);
                foreach ($resultado[0] as $res) {
                    $ida = $res['id'];
                    $unidades = $tbUniAcao->fetchAll("id_acao = '$ida'");
                    
                    foreach ($unidades as $uni) {
                        if (empty($resultado[1][$ida]))
                            $resultado[1][$ida] = $uni['id_unidade'];
                        else {
                            $resultado[1][$ida] .= ', '.$uni['id_unidade'];
                        }
                    }
                }
                $db->closeConnection();
                //var_dump($resultado[1]);exit;
                return $resultado;
        }
        
        public function acoesprocon($id) {
                Zend_Loader::loadClass('UnidadeAcao');
                $tbUniAcao = new UnidadeAcao();
            
            
                $db = Zend_Registry::get("db");
                $sql = "select ac.id, ac.no_descricao, ac.no_observacoes, TO_CHAR(ac.dt_mes, 'MM/YYYY') as dt_mes, TO_CHAR(ac.dt_prazo, 'DD/MM/YYYY') as dt_prazo, ac.id_unidade_registro, ac.dt_registro, ac.hr_registro, ac.id_unidade_acao, ac.nu_percentual,
                            CASE WHEN DATE(ac.dt_prazo) - DATE(NOW()) <= 0 and ac.nu_percentual < 100 THEN 'critico'
                                 WHEN DATE(ac.dt_prazo) - DATE(NOW()) > 0 and ac.nu_percentual < 100 THEN 'medio' 
                                 WHEN ac.nu_percentual = 100 THEN 'leve' 
                                 end as background
                            from tb_acoesprocon as ac
                                WHERE ac.id_item = '$id'";
                //echo $sql;exit;
                $resultado[0] = $db->fetchAll($sql);
                foreach ($resultado[0] as $res) {
                    $ida = $res['id'];
                    $unidades = $tbUniAcao->fetchAll("id_acao = '$ida'");
                    foreach ($unidades as $uni) {
                        if (empty($resultado[1][$ida]))
                            $resultado[1][$ida] = $uni['id_unidade'];
                        else {
                            $resultado[1][$ida] .= ', '.$uni['id_unidade'];
                        }
                    }
                }
                $db->closeConnection();
                return $resultado;
        }
        
        public function acao($id) {
                Zend_Loader::loadClass('UnidadeAcao');
                $tbUniAcao = new UnidadeAcao();
            
            
                $db = Zend_Registry::get("db");
                $sql = "select ac.id, ac.no_descricao, ac.no_observacoes, TO_CHAR(ac.dt_mes, 'MM/YYYY') as dt_mes, TO_CHAR(ac.dt_prazo, 'DD/MM/YYYY') as dt_prazo, ac.id_unidade_registro, ac.dt_registro, ac.hr_registro, ac.id_unidade_acao, uni.no_sigla as nome_unidade_acao, ac.nu_percentual
                            from tb_acoes as ac
                                inner join tb_unidade as uni on uni.id = ac.id_unidade_acao
                                WHERE ac.id = '$id'";
                //echo $sql;exit;
                $resultado = $db->fetchRow($sql);
                $ida = $resultado['id'];
                $sql2 = "select ua.id_unidade, uni.no_sigla
                            from tb_unidade_acao as ua
                                inner join tb_unidade as uni on uni.id = ua.id_unidade
                                    where ua.id_acao = '$ida'";
                $resultado2 = $db->fetchAll($sql2);
                foreach ($resultado2 as $res) {
                    $resultado['id_unidade_exec'] = $res['id_unidade'];
                    $resultado['nome_unidade_exec'] = $res['no_sigla'];
                }
                
                
                $db->closeConnection();
                //var_dump($resultado);exit;
                return $resultado;
        }
        
        public function acaoprocon($id) {
                Zend_Loader::loadClass('UnidadeAcao');
                $tbUniAcao = new UnidadeAcao();
            
            
                $db = Zend_Registry::get("db");
                $sql = "select ac.id, ac.no_descricao, ac.no_observacoes, TO_CHAR(ac.dt_mes, 'MM/YYYY') as dt_mes, TO_CHAR(ac.dt_prazo, 'DD/MM/YYYY') as dt_prazo, ac.id_unidade_registro, ac.dt_registro, ac.hr_registro, ac.id_unidade_acao, uni.no_sigla as nome_unidade_acao, ac.nu_percentual
                            from tb_acoesprocon as ac
                                inner join tb_unidade as uni on uni.id = ac.id_unidade_acao
                                WHERE ac.id = '$id'";
                //echo $sql;exit;
                $resultado = $db->fetchRow($sql);
                $ida = $resultado['id'];
                $sql2 = "select ua.id_unidade, uni.no_sigla
                            from tb_unidade_acao as ua
                                inner join tb_unidade as uni on uni.id = ua.id_unidade
                                    where ua.id_acao = '$ida'";
                $resultado2 = $db->fetchAll($sql2);
                foreach ($resultado2 as $res) {
                    $resultado['id_unidade_exec'] = $res['id_unidade'];
                    $resultado['nome_unidade_exec'] = $res['no_sigla'];
                }
                
                
                $db->closeConnection();
                //var_dump($resultado);exit;
                return $resultado;
        }
        
        public function deletar($id) {
            Zend_Loader::loadClass('Usuario');
            Zend_Loader::loadClass('Mensagens');
            $tbUser = new Usuario();
            
            $user = Zend_Auth::getInstance()->getIdentity();
            
            $permissao = false;
            if (isset($user) && ($user->usuario == 'c080695' || $user->usuario == 'c074329')) {
                    $permissao = true;
            }
            if ($permissao) {
                    $row = $tbUser->fetchRow("id = '$id'");
                    
                    if ($user->usuario != $row->usuario) {
                        $row->delete(); 
                        $obj = Mensagens::M03;
                    } else {
                        $obj = Mensagens::E01;
                    }
                    
                    return $obj;
			 
            } else {
                    $obj = Mensagens::E01;
                    return $obj;
            }
        }
        
        public function salvar($request) {
            Zend_Loader::loadClass('Acoes');
            Zend_Loader::loadClass('UnidadeAcao');
            Zend_Loader::loadClass('UnidadeAcaoC');
            Zend_Loader::loadClass('Mensagens');
            Zend_Loader::loadClass('UtilDAO');
            $tbAcoes = new Acoes();
            $tbUniAcao = new UnidadeAcao();
            $tbUniAcaoC = new UnidadeAcaoC();
            $util = new UtilDAO();
            
            Zend_Loader::loadClass('ControleBacen');
            $tbControle = new ControleBacen();
            
            
            $id_acao = $request['id_acao'];
            $session = new Zend_Session_Namespace('usuario');
            
            $permissao = true;
            
            $uniUser = $session->user['unidade'];
            $tipoUnidade = $util->getTipoUnidade($uniUser);
            
            $array_unidades = array("SR","AG","PAP","PAB","PAA","PA");
            if (in_array(trim($tipoUnidade),$array_unidades)) $permissao = false;
            
            if ($permissao) {
                    if (is_numeric($id_acao)) {
                        
                        $row = $tbAcoes->fetchRow("id = '$id_acao'");
                        $tipo = 'alteração';
                    } else {
                        $row = $tbAcoes->createRow();
                        $row->id_unidade_registro   = $uniUser;
                        $row->id_produto            = $request['id_produto'];
                        $row->dt_mes                = substr($util->dataSql(),0,-2).'01';
                        $row->dt_registro           = $util->dataSql();
                        $row->hr_registro           = $util->horaSql();
                        $tipo = 'inclusão';
                    }
                    $row->id_unidade_acao       = $request['unidade'];
                    $row->no_descricao          = $request['descricao'];
                    $row->dt_prazo              = $util->transformaDataSql($request['dtprazo']);
                    $row->no_observacoes        = $request['observacoes'];
                    $row->nu_percentual         = $request['percentual'];
                    
                    
                    
                    $id = $row->save();
                    
                    $rowCont = $tbControle->createRow();
                    $rowCont->dh_alteracao   =   $util->dataAtualSql();
                    $rowCont->dt_mes         = substr($util->dataSql(),0,-2).'01';
                    $rowCont->id_acoes       =   $id;
                    $rowCont->de_controle    =   $tipo;
                    $rowCont->id_unidade_acao       = $request['unidade'];
                    $rowCont->no_descricao          = $request['descricao'];
                    $rowCont->dt_prazo              = $util->transformaDataSql($request['dtprazo']);
                    $rowCont->no_observacoes        = $request['observacoes'];
                    $rowCont->nu_percentual         = $request['percentual'];
                    $rowCont->id_unidade_registro   = $uniUser;
                    $idC = $rowCont->save();
                    
                    $unidades = explode(",",$request['unidade_exec']);
                    if (is_array($unidades)) {
                        $rowUniA = $tbUniAcao->fetchRow("id_acao = '$id'");
                        if ($rowUniA) {
                            $db = Zend_Registry::get("db");
                            $db->delete('tb_unidade_acao', "id_acao = '$id'");
                            $db->closeConnection();
                        }
                        foreach($unidades as $uniexec) {
                            if (is_numeric($uniexec)) {
                                $rowUni = $tbUniAcao->createRow();
                                $rowUni->id_unidade = $uniexec;
                                $rowUni->id_acao    = $id;
                                $rowUni->save();
                                
                                $rowUniC = $tbUniAcaoC->createRow();
                                $rowUniC->id_unidade  = $uniexec;
                                $rowUniC->id_controle = $idC;
                                $rowUniC->save();
                            }
                        }
                    } else if (is_numeric($request['unidade_exec'])) {
                        $rowUniA = $tbUniAcao->fetchRow("id_acao = '$id'");
                        if ($rowUniA) {
                            $db = Zend_Registry::get("db");
                            $db->delete('tb_unidade_acao', "id_acao = '$id'");
                            $db->closeConnection();
                        }
                        $uniex = $request['unidade_exec'];
                        $rowUni = $tbUniAcao->createRow();
                        $rowUni->id_unidade = $request['unidade_exec'];
                        $rowUni->id_acao    = $id;
                        $rowUni->save();
                        
                        $rowUniC = $tbUniAcaoC->createRow();
                        $rowUniC->id_unidade  = $request['unidade_exec'];
                        $rowUniC->id_controle = $idC;
                        $rowUniC->save();
                    }
                    if ($id)
                        $obj = Mensagens::M05;
                    else
                        $obj = Mensagens::E01;
                    
                    return $obj;
			 
            } else {
                    $obj = Mensagens::E01;
                    return $obj;
            }
        }
        
        public function salvarprocon($request) {
            Zend_Loader::loadClass('AcoesProcon');
            Zend_Loader::loadClass('UnidadeAcaoProcon');
            Zend_Loader::loadClass('UnidadeAcaoProconC');
            Zend_Loader::loadClass('Mensagens');
            Zend_Loader::loadClass('UtilDAO');
            $tbAcoes = new AcoesProcon();
            $tbUniAcao = new UnidadeAcaoProcon();
            $tbUniAcaoC = new UnidadeAcaoProconC();
            $util = new UtilDAO();
            $id_acao = $request['id_acao'];
            $session = new Zend_Session_Namespace('usuario');
            
            Zend_Loader::loadClass('ControleProcon');
            $tbControle = new ControleProcon();
            
            $permissao = true;
            
            $uniUser = $session->user['unidade'];
            $tipoUnidade = $util->getTipoUnidade($uniUser);
            
            $array_unidades = array("SR","AG","PAP","PAB","PAA","PA");
            if (in_array(trim($tipoUnidade),$array_unidades)) $permissao = false;
            
            if ($permissao) {
                    if (is_numeric($id_acao)) {
                        
                        $row = $tbAcoes->fetchRow("id = '$id_acao'");
                        $tipo = 'alteração';
                    } else {
                        $row = $tbAcoes->createRow();
                        $row->id_unidade_registro   = $uniUser;
                        $row->id_item               = $request['id_produto'];
                        $row->dt_mes                = substr($util->dataSql(),0,-2).'01';
                        $row->dt_registro           = $util->dataSql();
                        $row->hr_registro           = $util->horaSql();
                        $tipo = 'inclusão';
                    }
                    $row->id_unidade_acao       = $request['unidade'];
                    $row->no_descricao          = $request['descricao'];
                    $row->dt_prazo              = $util->transformaDataSql($request['dtprazo']);
                    $row->no_observacoes        = $request['observacoes'];
                    $row->nu_percentual         = $request['percentual'];
                    
                    $id = $row->save();
                    
                    $rowCont = $tbControle->createRow();
                    $rowCont->dh_alteracao          = $util->dataAtualSql();
                    $rowCont->dt_mes                = substr($util->dataSql(),0,-2).'01';
                    $rowCont->id_acoes              = $id;
                    $rowCont->de_controle           = $tipo;
                    $rowCont->id_unidade_acao       = $request['unidade'];
                    $rowCont->no_descricao          = $request['descricao'];
                    $rowCont->dt_prazo              = $util->transformaDataSql($request['dtprazo']);
                    $rowCont->no_observacoes        = $request['observacoes'];
                    $rowCont->nu_percentual         = $request['percentual'];
                    $rowCont->id_unidade_registro   = $uniUser;
                    $idC = $rowCont->save();
                    
                    $unidades = explode(",",$request['unidade_exec']);
                    if (is_array($unidades)) {
                        $rowUnia = $tbUniAcao->fetchRow("id_acao = '$id'");
                        if ($rowUnia) {
                            $db = Zend_Registry::get("db");
                            $db->delete('tb_unidade_acaoprocon', "id_acao = '$id'");
                            $db->closeConnection();
                        }
                        foreach($unidades as $uniexec) {
                            if (is_numeric($uniexec)) {
                                $rowUni = $tbUniAcao->createRow();
                                $rowUni->id_unidade = $uniexec;
                                $rowUni->id_acao    = $id;
                                $rowUni->save();
                                
                                $rowUniC = $tbUniAcaoC->createRow();
                                $rowUniC->id_unidade  = $uniexec;
                                $rowUniC->id_controle = $idC;
                                $rowUniC->save();
                            }
                        }
                    } else if (is_numeric($request['unidade_exec'])) {
                        $rowUnia = $tbUniAcao->fetchRow("id_acao = '$id'");
                        if ($rowUnia) {
                            $db = Zend_Registry::get("db");
                            $db->delete('tb_unidade_acaoprocon', "id_acao = '$id'");
                            $db->closeConnection();
                        }
                        
                        $rowUni = $tbUniAcao->createRow();
                        $rowUni->id_unidade = $request['unidade_exec'];
                        $rowUni->id_acao    = $id;
                        $rowUni->save();
                        
                        $rowUniC = $tbUniAcaoC->createRow();
                        $rowUniC->id_unidade  = $request['unidade_exec'];
                        $rowUniC->id_controle = $idC;
                        $rowUniC->save();
                    }
                    if ($id)
                        $obj = Mensagens::M05;
                    else
                        $obj = Mensagens::E01;
                    
                    return $obj;
			 
            } else {
                    $obj = Mensagens::E01;
                    return $obj;
            }
        }
        
        public function periodoEditaAcoes(){
            Zend_Loader::loadClass("UtilDAO");
            
            $util = new UtilDAO();
            
            $data = $util->dataSql();
            
            if (substr($data,-2) <= 16 && substr($data,-2) >= 14) {
                $resposta = 0;
            } else {
            
            $db = Zend_Registry::get("db");
            $sql = "SELECT dia FROM
                        (SELECT ('$data'::date+s.a*'1 day'::interval) AS dia
                           FROM generate_series(0, '$data'::date -
                                              '$data'::date, 1) AS s(a)) foo
                      WHERE EXTRACT(DOW FROM dia) BETWEEN 1 AND 5 except
                    SELECT dt_feriado as dia FROM tb_feriados where dt_feriado = '$data'";
            //echo $sql;exit;
            $resultado = $db->fetchAll($sql);
            $resposta = sizeof($resultado);
            }
            return $resposta;
        }
        
        public function calculoDias($data){
            Zend_Loader::loadClass("UtilDAO");
            $util = new UtilDAO();
            /* Zend_Loader::loadClass("Controle");
            
            
            $tbControle = new Controle();
            $controle = $tbControle->fetchRow("id = '7'"); */
            $dtFim = $data; 
            $dtIni = substr($data,0,-2).'01';
            
            $db = Zend_Registry::get("db");
            $sql = "SELECT dia FROM
                        (SELECT ('$dtIni'::date+s.a*'1 day'::interval) AS dia
                           FROM generate_series(0, '$dtFim'::date -
                                              '$dtIni'::date, 1) AS s(a)) foo
                      WHERE EXTRACT(DOW FROM dia) BETWEEN 1 AND 5 except
                    SELECT dt_feriado as dia FROM tb_feriados where dt_feriado BETWEEN '$dtIni' and '$dtFim'";
            //echo $sql;exit;
            $resultado = $db->fetchAll($sql);
            $valor1 = sizeof($resultado);
            
            $dtFim2 = $util->dataSubDia($util->dataAddMes($dtIni));
            
                        
            $sql2 = "SELECT dia FROM
                        (SELECT ('$dtIni'::date+s.a*'1 day'::interval) AS dia
                           FROM generate_series(0, '$dtFim2'::date -
                                              '$dtIni'::date, 1) AS s(a)) foo
                      WHERE EXTRACT(DOW FROM dia) BETWEEN 1 AND 5 except
                    SELECT dt_feriado as dia FROM tb_feriados where dt_feriado BETWEEN '$dtIni' and '$dtFim2'";
            //echo $sql;exit;
            $resultado2 = $db->fetchAll($sql2);
            $valor2 = sizeof($resultado2);
            
            $resposta = array();
            $resposta[0] = $valor1;
            $resposta[1] = $valor2;
            $resposta[2] = $dtIni;
            
            return $resposta;
        }
        
        public function listaAlteracoesProcon(){
            Zend_Loader::loadClass("UtilDAO");
            
            $util = new UtilDAO();
            $dataFim = $util->dataSql();
            $dataIni = $util->dataSubMes($dataFim);
            
            $db = Zend_Registry::get("db");
            $sql = "SELECT TO_CHAR(cont.dh_alteracao, 'DD/MM/YYYY') as data, TO_CHAR(cont.dh_alteracao, 'YYYY-MM-DD') as dia, '1' as qtd, item.no_item as produto, cont.de_controle as tipo, acoes.id_item as id_produto
                        from tb_controle_acoes_procon as cont
                            inner join tb_acoesprocon as acoes on acoes.id = cont.id_acoes
                            inner join tb_item as item on item.id = acoes.id_item
                            where TO_CHAR(cont.dh_alteracao,'YYYY-MM-DD') between '$dataIni' and '$dataFim'
                            group by data, produto, tipo, dia, id_produto order by data";
            //echo $sql;exit;
            $resultado = $db->fetchAll($sql);
            $resposta = array();
            foreach ($resultado as $res) {
                $resposta[0][$res['data']] = $res['data'];
                $resposta[1][$res['produto']]['produto'] = $res['produto'];
                $resposta[1][$res['produto']]['id_produto'] = $res['id_produto'];
                $resposta[1][$res['produto']][$res['data']][$res['tipo']]['qtd'] = $res['qtd'];
                $resposta[1][$res['produto']][$res['data']]['dia'] = $res['dia'];
            }
            $db->closeConnection();
            return $resposta;
        }
        
        public function listaAlteracoesBacen(){
            Zend_Loader::loadClass("UtilDAO");
                        
            $util = new UtilDAO();
            $dataFim = $util->dataSql();
            $dataIni = $util->dataSubMes($dataFim);
            
            $db = Zend_Registry::get("db");
            $sql = "SELECT TO_CHAR(dh_alteracao, 'DD/MM/YYYY') as data, TO_CHAR(dh_alteracao, 'YYYY-MM-DD') as dia, 
                           count(1) as qtd, acoes.id_produto, prd.no_produto as produto , cont.de_controle as tipo, cont.id_acoes as id_acao
                                from tb_controle_acoes_bacen as cont
                                    inner join tb_acoes as acoes on acoes.id = cont.id_acoes
                                    inner join tb_produto as prd on prd.id = acoes.id_produto
                                        where TO_CHAR(cont.dh_alteracao,'YYYY-MM-DD') between '$dataIni' and '$dataFim'
                                            group by data, dia, produto, id_produto, tipo, id_acao  order by dia DESC";
            //echo $sql;exit;
            $resultado = $db->fetchAll($sql);
            $resposta = array();
            foreach ($resultado as $res) {
                $resposta[0][$res['data']] = $res['data'];
                $resposta[1][$res['produto']]['produto'] = $res['produto'];
                $resposta[1][$res['produto']]['id_produto'] = $res['id_produto'];
                if (empty($resposta[1][$res['produto']][$res['data']][$res['tipo']])) $resposta[1][$res['produto']][$res['data']][$res['tipo']]['qtd'] = 0;
                $resposta[1][$res['produto']][$res['data']][$res['tipo']]['qtd'] += $res['qtd'];
                $resposta[1][$res['produto']][$res['data']]['dia'] = $res['dia'];
            }
            $db->closeConnection();
            return $resposta;
        }
        
        public function listaAcaoBacen($request){
            Zend_Loader::loadClass("UtilDAO");
            $util = new UtilDAO();
            
            Zend_Loader::loadClass("UnidadeAcaoC");
            $tbUniAcaoC = new UnidadeAcaoC();
            
            $where = '';
            
            $dataIni = $util->transformaDataSql($request['inicio']);
            $dataFim = $util->transformaDataSql($request['fim']);
            
            $tipoData = $request['tipodata'];
            switch ($tipoData) {
                case '1':
                    $where = "acoes.dt_prazo between '$dataIni' and '$dataFim'";
                break;
                case '2':
                    $where = "TO_CHAR(cont.dh_alteracao,'YYYY-MM-DD') between '$dataIni' and '$dataFim' and cont.de_controle = 'inclusão'";
                break;
                case '3':
                    $where = "TO_CHAR(cont.dh_alteracao,'YYYY-MM-DD') between '$dataIni' and '$dataFim' and cont.de_controle = 'alteração'";
                break;
                default:
                    $where = "TO_CHAR(cont.dh_alteracao,'YYYY-MM-DD') between '$dataIni' and '$dataFim'";
                break;
            }
            
            $id = $request['produto'];
            if (is_numeric($id) && $id != 'todos') $where .= " and prd.id = '$id'";
            
            $unidaderesp = $request['unidaderesp'];
            if (is_numeric($unidaderesp)) $where .= " and cont.id_unidade_acao in ('$unidaderesp')";
            
            $innerjoin = "";
            $unidadeexec = $request['unidadeexec'];
            if (is_numeric($unidadeexec)) $innerjoin = " inner join tb_unidade_acao_controle as acont on acont.id_controle = cont.id and acont.id_unidade in ('$unidadeexec')";
            

            $db = Zend_Registry::get("db");
                $sql = "select cont.id, cont.id_acoes, cont.no_descricao, cont.no_observacoes, TO_CHAR(cont.dt_mes, 'MM/YYYY') as dt_mes, TO_CHAR(cont.dt_prazo, 'DD/MM/YYYY') as dt_prazo, cont.id_unidade_registro, TO_CHAR(cont.dh_alteracao, 'DD/MM/YYYY') dt_alteracao, cont.id_unidade_acao, uni.no_sigla, cont.nu_percentual, cont.de_controle, prd.no_produto
                            from tb_controle_acoes_bacen as cont
                                left join tb_unidade as uni on uni.id = cont.id_unidade_acao
                                inner join tb_acoes as acoes on acoes.id = cont.id_acoes
                                inner join tb_produto as prd on prd.id = acoes.id_produto $innerjoin
                                where $where";
                //echo $sql;exit;
                //$resultado = $db->fetchAll($sql);
                //$ida = $resultado['id'];
                $resultado[0] = $db->fetchAll($sql);
                foreach ($resultado[0] as $res) {
                    $ida = $res['id'];
                    $unidades = $tbUniAcaoC->fetchAll("id_controle = '$ida'");
                    foreach ($unidades as $uni) {
                        if (empty($resultado[1][$ida]))
                            $resultado[1][$ida] = $uni['id_unidade'];
                        else {
                            $resultado[1][$ida] .= ', '.$uni['id_unidade'];
                        }
                    }
                }
                
                
            $db->closeConnection();
            //var_dump($resultado[1]);exit;
            return $resultado;
        }
        
        public function listaAcaoProcon($request){
            Zend_Loader::loadClass("UtilDAO");
            $util = new UtilDAO();
            
            Zend_Loader::loadClass("UnidadeAcaoProconC");
            $tbUniAcao = new UnidadeAcaoProconC();
            
            $where = '';
            
            $dataIni = $util->transformaDataSql($request['inicio']);
            $dataFim = $util->transformaDataSql($request['fim']);
            
            $tipoData = $request['tipodata'];
            switch ($tipoData) {
                case '1':
                    $where = "acoes.dt_prazo between '$dataIni' and '$dataFim'";
                break;
                case '2':
                    $where = "TO_CHAR(cont.dh_alteracao,'YYYY-MM-DD') between '$dataIni' and '$dataFim' and cont.de_controle = 'inclusão'";
                break;
                case '3':
                    $where = "TO_CHAR(cont.dh_alteracao,'YYYY-MM-DD') between '$dataIni' and '$dataFim' and cont.de_controle = 'alteração'";
                break;
                default:
                    $where = "TO_CHAR(cont.dh_alteracao,'YYYY-MM-DD') between '$dataIni' and '$dataFim'";
                break;
            }
            
            $id = $request['produto'];
            if (is_numeric($id) && $id != 'todos') $where .= " and prd.id = '$id'";
            
            $unidaderesp = $request['unidaderesp'];
            if (is_numeric($unidaderesp)) $where .= " and cont.id_unidade_acao in ('$unidaderesp')";
            
            $innerjoin = "";
            $unidadeexec = $request['unidadeexec'];
            if (is_numeric($unidadeexec)) $innerjoin = " inner join tb_unidade_acao_controleprocon as acont on acont.id_controle = cont.id and acont.id_unidade in ('$unidadeexec')";

            $db = Zend_Registry::get("db");
                $sql = "select cont.id, cont.id_acoes, cont.no_descricao, cont.no_observacoes, TO_CHAR(cont.dt_mes, 'MM/YYYY') as dt_mes, TO_CHAR(cont.dt_prazo, 'DD/MM/YYYY') as dt_prazo, cont.id_unidade_registro, TO_CHAR(cont.dh_alteracao, 'DD/MM/YYYY') dt_alteracao, cont.id_unidade_acao, uni.no_sigla, cont.nu_percentual, cont.de_controle, prd.no_item as no_produto
                            from tb_controle_acoes_procon as cont
                                left join tb_unidade as uni on uni.id = cont.id_unidade_acao
                                inner join tb_acoesprocon as acoes on acoes.id = cont.id_acoes
                                inner join tb_item as prd on prd.id = acoes.id_item $innerjoin
                                where $where";
                    

                $resultado = $db->fetchAll($sql);
                $ida = $resultado['id'];
                $resultado[0] = $db->fetchAll($sql);
                foreach ($resultado[0] as $res) {
                    $ida = $res['id'];
                    $unidades = $tbUniAcao->fetchAll("id_controle = '$ida'");
                    foreach ($unidades as $uni) {
                        if (empty($resultado[1][$ida]))
                            $resultado[1][$ida] = $uni['id_unidade'];
                        else {
                            $resultado[1][$ida] .= ', '.$uni['id_unidade'];
                        }
                    }
                }
                $db->closeConnection();
                return $resultado;
                
                
            $db->closeConnection();
            
            return $resultado;
        }
        
        public function qtdProconProduto() {
            $db = Zend_Registry::get("db");
            $sql = "select dt_mes, no_item, tipo,
                        sum(case when st_resolvido = '0' then qtd else 0 end) as nres,
                        sum(case when st_resolvido = '1' then qtd else 0 end) as res,
                        sum(qtd) as total,
                        (sum(case when st_resolvido = '1' then qtd else 0 end)/sum(qtd))*100 as resol
                        from (SELECT count(oco.id_ocorrencia) as qtd, TO_CHAR(sindec.dt_procon,'MM/YYYY') as dt_mes, sindec.no_classificacao, it.no_item, sindec.st_resolvido,
                            case when ori.id in ('7','772') then 'cip'
                                when ori.id in ('10','90') then 'acordo'
                                end as tipo
                                    FROM tb_procon as prc
                                          left join tb_ocorrencia as oco on oco.id_ocorrencia = prc.id_ocorrencia
                                          left join tb_origem as ori on ori.id = oco.id_origem
                                          left join tb_item as it on it.id = oco.id_item
                                          inner join tb_procon_extracao as sindec on sindec.nu_fa = prc.nu_procon
                                                  where ((sindec.id_tipo ='1' and ori.id in ('7','772')) or (sindec.id_tipo = '2' and ori.id in ('10','90'))) and st_resolvido in ('0','1') and sindec.dt_procon >= '2013-01-01'
                                                        group by sindec.dt_procon, sindec.no_classificacao, tipo, it.no_item, sindec.st_resolvido) as refina where tipo = 'cip' group by dt_mes, no_item, tipo  order by no_item";
            $resultado = $db->fetchAll($sql);
            return $resultado;
            
        }
        
        public function qtdProconUnidades() {
            $db = Zend_Registry::get("db");
            /* when no_item ~* 'CARTÃO  DE CRÉDITO' THEN 'GECOP' */
            $sql = "select dt_mes, tipo, unidade, no_item, no_motivo,
                        sum(case when st_resolvido = '0' then qtd else 0 end) as nres,
                        sum(case when st_resolvido = '1' then qtd else 0 end) as res,
                        sum(qtd) as total,
                        (sum(case when st_resolvido = '1' then qtd else 0 end)/sum(qtd))*100 as resol
                                from (select count(id_ocorrencia) as qtd, dt_mes, st_resolvido, tipo, no_item, no_motivo,
                                        CASE 
                                            WHEN uni.id = '5049' or uni.id_nivel2 = '5049' or uni.id_nivel3 = '5049' or uni.id_nivel4 = '5049' or uni.id_nivel5 = '5049' THEN 'SUATA'
                                            WHEN uni.id = '5050' or uni.id_nivel2 = '5050' or uni.id_nivel3 = '5050' or uni.id_nivel4 = '5050' or uni.id_nivel5 = '5050' THEN 'SUATB'
                                            WHEN uni.id = '5052' or uni.id_nivel2 = '5052' or uni.id_nivel3 = '5052' or uni.id_nivel4 = '5052' or uni.id_nivel5 = '5052' THEN 'SUATD'
                                            WHEN uni.id = '5053' or uni.id_nivel2 = '5053' or uni.id_nivel3 = '5053' or uni.id_nivel4 = '5053' or uni.id_nivel5 = '5053' THEN 'SUATE'
                                            WHEN uni.id = '5060' or uni.id_nivel2 = '5060' or uni.id_nivel3 = '5060' or uni.id_nivel4 = '5060' or uni.id_nivel5 = '5060' THEN 'SUATC'
                                        ELSE 'OUTROS'
                                        end as unidade from (
                                        SELECT distinct on(oco.id_ocorrencia) oco.id_ocorrencia, mov.dt_movimentacao, mov.hr_movimentacao, TO_CHAR(sindec.dt_procon,'MM/YYYY') as dt_mes, sindec.no_classificacao, it.no_item, sindec.st_resolvido, mot.no_motivo,
                                        mov.id_unidade,
                                    case when ori.id in ('7','772') then 'cip'
                                        when ori.id in ('10','90') then 'acordo'
                                        end as tipo
                                            FROM tb_procon as prc
                                                  left join tb_ocorrencia as oco on oco.id_ocorrencia = prc.id_ocorrencia
                                                  left join tb_movimentacao as mov on oco.id_ocorrencia = mov.id_ocorrencia
                                                  left join tb_origem as ori on ori.id = oco.id_origem
                                                  left join tb_item as it on it.id = oco.id_item
                                                  left join tb_motivo as mot on mot.id = oco.id_motivo
                                                  inner join tb_procon_extracao as sindec on sindec.nu_fa = prc.nu_procon
                                                          where ((sindec.id_tipo ='1' and ori.id in ('7','772')) or (sindec.id_tipo = '2' and ori.id in ('10','90'))) and st_resolvido in ('0','1') and sindec.dt_procon >= '2013-01-01' and mov.id_unidade not in ('5500','7700')
                                                                order by oco.id_ocorrencia, mov.dt_movimentacao DESC, mov.hr_movimentacao DESC) as refina
                                                                left join tb_unidade as uni on uni.id = refina.id_unidade 
                                                                        group by dt_mes, unidade, st_resolvido, tipo, no_item, no_motivo) as refina2 group by  dt_mes, tipo, unidade, no_item, no_motivo";
            $resultado = $db->fetchAll($sql);
            return $resultado;
            
        }
        
        public function analiticoProcon($dtIni,$dtFim) {
            
            $dtIni = '2014-01-01';
            $dtFim = '2014-09-30';
            
            $db = Zend_Registry::get("db");
            $sql = "SELECT oco.id_ocorrencia, TO_CHAR(sindec.dt_procon,'DD/MM/YYYY') as dt_procon, TO_CHAR(mov.dt_movimentacao,'DD/MM/YYYY') as dt_resposta, TO_CHAR(sindec.dt_procon,'MM') as dt_mes, TO_CHAR(sindec.dt_procon,'YYYY') as dt_ano, ast.no_assunto, it.no_item, mot.no_motivo,                            CASE WHEN sindec.st_resolvido = '0' THEN 'Não Resolvido'
                                WHEN sindec.st_resolvido = '1' THEN 'Resolvido'
                                end as resultado,
                            case when sindec.id_tipo = '1' then 'CIP'
                                when sindec.id_tipo = '2' then 'PROCESSO ADMINISTRATIVO'
                                end as tipo
                                    FROM tb_procon as prc
                                          left join tb_ocorrencia as oco on oco.id_ocorrencia = prc.id_ocorrencia
                                          left join tb_movimentacao as mov on mov.id_ocorrencia = oco.id_ocorrencia and mov.id_tipomovimentacao = '8'
                                          left join tb_origem as ori on ori.id = oco.id_origem
                                          left join tb_item as it on it.id = oco.id_item
                                          left join tb_assunto as ast on ast.id = it.id_assunto
                                          left join tb_motivo as mot on mot.id = oco.id_motivo
                                          inner join tb_procon_extracao as sindec on sindec.nu_fa = prc.nu_procon
                                                  where ((sindec.id_tipo ='1' and ori.id in ('7','772')) or (sindec.id_tipo = '2' and ori.id in ('10','90'))) and st_resolvido in ('0','1') and sindec.dt_procon between '$dtIni' and '$dtFim'
                                                        group by sindec.dt_procon, sindec.no_classificacao, tipo, it.no_item, sindec.st_resolvido) as refina where tipo = 'cip' group by dt_mes, no_item, tipo  order by no_item";
            $resultado = $db->fetchAll($sql);
            return $resultado;
            
        }
        
        
        
        	
}