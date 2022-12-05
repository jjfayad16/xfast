<?php
/**
 * Classe que realiza interações com dados para contratos
 *
 */
class UsuarioDAO extends Extra_Model_DAO {
	//private $recurso = 3;
	public static function build() {
		return new self();
	}
	/**
	 * Método que verifica se contrato existe na base
	 * @param string $login
	 * @return Extra_Mensageiro
	 */
	public function listarUsuarios() {
		Zend_Loader::loadClass('Usuario');
		$tbUsuario= new Usuario();
		$usuario = $tbUsuario->fetchAll('st_ativo = true');
		if($usuario) {
			return $usuario;
		}	
	}
	
	public function salvarUsuario($request) {
		Zend_Loader::loadClass('Usuario');
		Zend_Loader::loadClass('AuditoriaDAO');
		Zend_Loader::loadClass('Mensagens');
		$usuario = new Usuario();
		$auditoria = new AuditoriaDAO();
		$row = $usuario->createRow();
		$row->no_usuario 	= $request->getParam('no_usuario');
		$row->fk_id_perfil 	= 1;
		$row->no_login 		= $request->getParam('no_login');
		$row->no_email 		= $request->getParam('no_email');
		$row->no_senha 		= md5($request->getParam('no_senha'));
		$id = $row->save();
		
		if ($id) {
			$auditoria->salvarProcedimento($id['id_usuario'], Mensagens::A01);
			return Mensagens::M03;
		} else {
		
		}
	
	}
	
	public function alteraSenha($request, $id) {
		Zend_Loader::loadClass('Usuario');
		Zend_Loader::loadClass('Mensagens');
		$usuario = new Usuario();
		$id = $request->getParam('id_usuario');
		$sql = $usuario->select()->where("id_usuario =?", $id);
		$row = $usuario->fetchRow($sql);
		$row->no_senha 		= md5($request->getParam('no_senha'));
		$id = $row->save();
		
		if ($id) {
			return Mensagens::M06;
		} else {
		
		}
	
	}
	
	public function editarUsuario($request) {
		Zend_Loader::loadClass('Usuario');
		Zend_Loader::loadClass('AuditoriaDAO');
		Zend_Loader::loadClass('Mensagens');
		$usuario = new Usuario();
		$auditoria = new AuditoriaDAO();
		
		$id = $request->getParam('id_usuario');
		$sql = $usuario->select()->where("id_usuario =?", $id);
		$row = $usuario->fetchRow($sql);
		$row->no_usuario 	= $request->getParam('no_usuario');
		$row->no_login 		= $request->getParam('no_login');
		$row->no_email 		= $request->getParam('no_email');
		if($request->getParam('no_senha')) 
		$row->no_senha 		= md5($request->getParam('no_senha'));
		$id = $row->save();
		
		if ($id) {
			$auditoria->salvarProcedimento($id['id_usuario'], Mensagens::A02);
			return Mensagens::M04;
		} else {
		
		}
	
	}
	
	public function excluirUsuario($id) {
		Zend_Loader::loadClass('Usuario');
		Zend_Loader::loadClass('AuditoriaDAO');
		Zend_Loader::loadClass('Mensagens');
		$usuario = new Usuario();
		$auditoria = new AuditoriaDAO();
		
		$sql = $usuario->select()->where("id_usuario =?", $id);
		$row = $usuario->fetchRow($sql);
		$row->st_ativo 		= 0;
		$id = $row->save();
		
		if ($id) {
			$auditoria->salvarProcedimento($id['id_usuario'], Mensagens::A03);
			return Mensagens::M05;
		} else {
		
		}
	
	}
	
	public function editaUsuario($id) {
		Zend_Loader::loadClass('Usuario');
		$tbUsuario= new Usuario();
		$sql = $tbUsuario->select()->where('id_usuario =?',$id);
		$usuario = $tbUsuario->fetchRow($sql);
		if ($usuario) {
			return $usuario;
		}
	}
	
	public function existeUsuario($valor, $id = null) {
		Zend_Loader::loadClass('Usuario');
		Zend_Loader::loadClass('Mensagens');
		$tbUsuario= new Usuario();
		$sql = $tbUsuario->select()->where('no_login =?',$valor)->where('st_ativo = true');
		if($id) {
			$sql->where('id_usuario <>?',$id); }
		$usuario = $tbUsuario->fetchRow($sql);
		if($usuario) 
			return Mensagens::M01;
		else 
			return true;
	}
	
	public function existeEmail($valor, $id = null) {
		Zend_Loader::loadClass('Usuario');
		Zend_Loader::loadClass('Mensagens');
		$tbUsuario= new Usuario();
		$sql = $tbUsuario->select()->where('no_email =?',$valor)->where('st_ativo = true');
		if($id) {
			$sql->where('id_usuario <>?',$id); }
		$usuario = $tbUsuario->fetchRow($sql);
		if($usuario) 
			return Mensagens::M02;
		else 
			return true;
	}

}