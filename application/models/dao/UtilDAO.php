<?php
/**
 * Classe que realiza interações com dados para contratos
 *
 */
class UtilDAO extends Extra_Model_DAO {
	//private $recurso = 3;
	public static function build() {
		return new self();
	}
        
        public function listaExtracao(){
            $nu_fa = '01140294122';
            //var_dump ($valores);exit;
            $db = Zend_Registry::get("db");
            $sql = "select TO_CHAR(es.dt_procon,'DD/MM/YYYY') dt_procon, es.nu_fa, es.no_consumidor, es.manifesto, sind.url, sind.usuario, sind.senha, sind.no_municipio, 
                    case when es.ic_tipo = '1' then 'CIP'
                         when es.ic_tipo = '2' then 'PROCESSO ADMINISTRATIVO'
                         END as tipo
                    from tb_extracao_sindec as es 
                        inner join tb_sindec as sind on sind.id = es.id_sindec
                            where nu_fa = '$nu_fa'";
            $resultado = $db->fetchRow($sql);
            
            return $resultado;
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