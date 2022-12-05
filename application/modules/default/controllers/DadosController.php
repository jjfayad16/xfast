<?php

class DadosController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
        Zend_Loader::loadClass('UtilDAO');
	
		
    }
	


    public function sindecAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $dados = new UtilDAO();
        
        $diretorio = "/xampp/htdocs/procon/public/dados/sindec/"; 
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
            $submit = $dados->gravaSindec($valores);
            fclose($a);
            unlink($link);
            echo $submit;
        } else 
            echo 'Nenhum arquivo existente na pasta';
    }
    
    public function resolubilidadeAction(){
        $this->_helper->layout->disableLayout();
        Zend_Loader::loadClass('InformeDAO');
        $informe = new InformeDAO();
        
        $tipo = $this->_getParam('tipo');
        
        if ($tipo == '1') {
            $this->view->resultado = $informe->qtdProconUnidades();
            $this->render('resoluni');
        } else if ($tipo == '2') {
            $this->view->resultado = $informe->qtdProconProduto();
            $this->render('resolitem');
        }
        
        
    }
    
    public function unidadeResponsavelAction(){
        $this->_helper->viewRenderer->setNoRender(true);
        $data = '2014-10-01';
        $data2= '2014-10-13';
         $db = Zend_Registry::get("db");
        $sql = "select * from
                       ((select DISTINCT ON(o.id_ocorrencia) o.id_ocorrencia, m.id_unidade, 'Ultima Unidade de Movimentação' as tipo, 'rcli' as indicador
                        FROM tb_movimentacao as m inner join tb_ocorrencia as o on o.id_ocorrencia = m.id_ocorrencia 
                            WHERE o.id_ocorrencia in (select o2.id_ocorrencia from tb_ocorrencia as o2 inner join tb_movimentacao as m2 on m2.id_ocorrencia = o2.id_ocorrencia and m2.id_tipomovimentacao = '2' 
                                AND m2.id_grausatisfacao in ('621','623','624','642') AND m2.dt_movimentacao > '$data' AND m2.dt_movimentacao <= '$data2' where o2.id_origem = '651' and o2.st_ocorrencia = '0')
                                    AND m.id_unidade not in ('5500','7700') ORDER BY o.id_ocorrencia, m.dt_movimentacao DESC, m.hr_movimentacao DESC)
                        union
                        select m.id_ocorrencia, uniresp.id_unidade, 'Unidade Responsável' as tipo, 'rcli' as indicador
                        FROM tb_movimentacao as m 
				inner join tb_unidade_responsavel as uniresp on m.id = uniresp.id_movimentacao
                            WHERE m.id_tipomovimentacao = '8' and m.id_ocorrencia in (select o2.id_ocorrencia from tb_ocorrencia as o2 inner join tb_movimentacao as m2 on m2.id_ocorrencia = o2.id_ocorrencia and m2.id_tipomovimentacao = '2' 
                                AND m2.id_grausatisfacao in ('621','623','624','642') AND m2.dt_movimentacao > '$data' AND m2.dt_movimentacao <= '$data2' where o2.id_origem = '651' and o2.st_ocorrencia = '0')
                        union
                        (select DISTINCT ON(o.id_ocorrencia) o.id_ocorrencia, m.id_unidade, 'Ultima Unidade de Movimentação' as tipo, 'resolvido' as indicador
                        FROM tb_movimentacao as m inner join tb_ocorrencia as o on o.id_ocorrencia = m.id_ocorrencia 
                            WHERE o.id_ocorrencia in (select o2.id_ocorrencia from tb_ocorrencia as o2 inner join tb_movimentacao as m2 on m2.id_ocorrencia = o2.id_ocorrencia and m2.id_tipomovimentacao = '2' 
                                AND m2.id_grausatisfacao in ('621') AND m2.dt_movimentacao > '$data' AND m2.dt_movimentacao <= '$data2' where 
                                o2.id_origem in ('7','90','430','731','772','310','2','6','4','190','290','734','732','733','651','30','616','511','512','510','1','210','5','692','211','671','691','711') and (o2.st_natureza = '1' or o2.st_natureza is null) and o2.st_ocorrencia = '0')
                                    and m.id_unidade not in ('5500','7700') ORDER BY o.id_ocorrencia, m.dt_movimentacao DESC, m.hr_movimentacao DESC)
                    union
                    select m.id_ocorrencia, uniresp.id_unidade, 'Unidade Responsável' as tipo, 'resolvido' as indicador
                        FROM tb_movimentacao as m 
				inner join tb_unidade_responsavel as uniresp on m.id = uniresp.id_movimentacao
                            WHERE m.id_tipomovimentacao = '8' and m.id_ocorrencia in (select o2.id_ocorrencia from tb_ocorrencia as o2 inner join tb_movimentacao as m2 on m2.id_ocorrencia = o2.id_ocorrencia and m2.id_tipomovimentacao = '2' 
                                AND m2.id_grausatisfacao in ('621') AND m2.dt_movimentacao > '$data' AND m2.dt_movimentacao <= '$data2' where 
                                o2.id_origem in ('7','90','430','731','772','310','2','6','4','190','290','734','732','733','651','30','616','511','512','510','1','210','5','692','211','671','691','711') and (o2.st_natureza = '1' or o2.st_natureza is null) and o2.st_ocorrencia = '0')
                                union
                     (select DISTINCT ON(o.id_ocorrencia) o.id_ocorrencia, m.id_unidade, 'Ultima Unidade de Movimentação' as tipo, 'não resolvido' as indicador
                        FROM tb_movimentacao as m inner join tb_ocorrencia as o on o.id_ocorrencia = m.id_ocorrencia
                            WHERE o.id_ocorrencia in (select o2.id_ocorrencia from tb_ocorrencia as o2 inner join tb_movimentacao as m2 on m2.id_ocorrencia = o2.id_ocorrencia and m2.id_tipomovimentacao = '2' 
                                AND m2.id_grausatisfacao in ('623','624','642') AND m2.dt_movimentacao > '$data' AND m2.dt_movimentacao <= '$data2' where 
                                o2.id_origem in ('7','90','430','731','772','310','2','6','4','190','290','734','732','733','651','30','616','511','512','510','1','210','5','692','211','671','691','711') and (o2.st_natureza = '1' or o2.st_natureza is null) and o2.st_ocorrencia = '0')
                                    and o.id_ocorrencia not in (select prc.id_ocorrencia FROM tb_procon as prc
					  inner join tb_ocorrencia as ocoP on ocoP.id_ocorrencia = prc.id_ocorrencia
                                          inner join tb_procon_extracao as sindec on sindec.nu_fa = prc.nu_procon
                                                  where ((sindec.id_tipo ='1' and ocoP.id_origem in ('7','772')) or (sindec.id_tipo = '2' and ocoP.id_origem in ('10','90'))) and st_resolvido in ('1'))
							and m.id_unidade not in ('5500','7700') ORDER BY o.id_ocorrencia, m.dt_movimentacao DESC, m.hr_movimentacao DESC)
                    union
                    select m.id_ocorrencia, uniresp.id_unidade, 'Unidade Responsável' as tipo, 'não resolvido' as indicador
                        FROM tb_movimentacao as m 
				inner join tb_unidade_responsavel as uniresp on m.id = uniresp.id_movimentacao
                            WHERE m.id_tipomovimentacao = '8' and m.id_ocorrencia in (select o2.id_ocorrencia from tb_ocorrencia as o2 inner join tb_movimentacao as m2 on m2.id_ocorrencia = o2.id_ocorrencia and m2.id_tipomovimentacao = '2' 
                                AND m2.id_grausatisfacao in ('623','624','642') AND m2.dt_movimentacao > '$data' AND m2.dt_movimentacao <= '$data2' where 
                                o2.id_origem in ('7','90','430','731','772','310','2','6','4','190','290','734','732','733','651','30','616','511','512','510','1','210','5','692','211','671','691','711') and (o2.st_natureza = '1' or o2.st_natureza is null) and o2.st_ocorrencia = '0')
                                    and m.id_ocorrencia not in (select prc.id_ocorrencia FROM tb_procon as prc
					  inner join tb_ocorrencia as ocoP on ocoP.id_ocorrencia = prc.id_ocorrencia
                                          inner join tb_procon_extracao as sindec on sindec.nu_fa = prc.nu_procon
                                                  where ((sindec.id_tipo ='1' and ocoP.id_origem in ('7','772')) or (sindec.id_tipo = '2' and ocoP.id_origem in ('10','90'))) and st_resolvido in ('1'))
                     union
                     (select DISTINCT ON(o.id_ocorrencia) o.id_ocorrencia, m.id_unidade, 'Ultima Unidade de Movimentação' as tipo, 'bonus' as indicador
                        FROM tb_movimentacao as m inner join tb_ocorrencia as o on o.id_ocorrencia = m.id_ocorrencia 
                            WHERE o.id_ocorrencia in (select o2.id_ocorrencia from tb_ocorrencia as o2 inner join tb_movimentacao as m2 on m2.id_ocorrencia = o2.id_ocorrencia and m2.id_tipomovimentacao = '2' 
                                AND m2.id_grausatisfacao in ('621','623','624','642') AND m2.dt_movimentacao > '$data' AND m2.dt_movimentacao <= '$data2' where 
                                (o2.id_origem in ('7','90','430','731','772','3','330','551','999') and (o2.st_natureza = '1' or o2.st_natureza is null)) or (o2.id_origem in ('3','330','551','999') and o2.st_natureza = '4') and o2.st_ocorrencia = '0')
                                    and m.id_unidade not in ('5500','7700') ORDER BY o.id_ocorrencia, m.dt_movimentacao DESC, m.hr_movimentacao DESC)
                    union
                    select m.id_ocorrencia, uniresp.id_unidade, 'Unidade Responsável' as tipo, 'bonus' as indicador
                        FROM tb_movimentacao as m 
				inner join tb_unidade_responsavel as uniresp on m.id = uniresp.id_movimentacao
                            WHERE m.id_tipomovimentacao = '8' and m.id_ocorrencia in (select o2.id_ocorrencia from tb_ocorrencia as o2 inner join tb_movimentacao as m2 on m2.id_ocorrencia = o2.id_ocorrencia and m2.id_tipomovimentacao = '2' 
                                AND m2.id_grausatisfacao in ('621','623','624','642') AND m2.dt_movimentacao > '$data' AND m2.dt_movimentacao <= '$data2' where 
                                (o2.id_origem in ('7','90','430','731','772','3','330','551','999') and (o2.st_natureza = '1' or o2.st_natureza is null)) or (o2.id_origem in ('3','330','551','999') and o2.st_natureza = '4') and o2.st_ocorrencia = '0')
                            ) as ref
                                ";
        
        $resultado = $db->fetchAll($sql);
        
        header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header ("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
        header ("Cache-Control: no-cache, must-revalidate");
        header ("Pragma: no-cache");
        header ("Content-type: application/x-msexcel");
        header ("Content-Disposition: attachment; filename=\"Resolubilidade.xls\"" );
        header ("Content-Description: PHP Generated Data" );
        
        echo "<table><thead>
            <tr>
                <th>Indicador</th>
                <th>tipo</th>
                <th>ocorrencia</th>
                <th>unidade</th>
            </tr>
            </thead><tbody>";
        foreach ($resultado as $res) {
            echo "<tr>
                    <td>".$res['indicador']."</td>
                    <td>".$res['tipo']."</td>
                    <td>".$res['id_ocorrencia']."</td>
                    <td>".$res['id_unidade']."</td>
                </tr>";
        }
        echo "</tbody></table>"; 
    }
    
    public function batimentoAction(){
        $this->_helper->layout->disableLayout();
        //$this->_helper->viewRenderer->setNoRender(true);
        $dados = new UtilDAO();
        
        $diretorio = "/xampp/htdocs/procon/public/dados/unidaderesp/"; 
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
            $submit = $dados->verificaUnidade($valores);
            fclose($a);
            //unlink($link);
            $this->view->batimento = $submit;
        } else 
            echo 'Nenhum arquivo existente na pasta';
    }

}

