<?php

declare(strict_types=1);

namespace Com\Daw2\Controllers;

class UsuarioSistemaController extends \Com\Daw2\Core\BaseController {
       
  
    function mostrarTodos(){
        $data = [];
        $data['titulo'] = 'Todos los usuarios';
        $data['seccion'] = '/usuarios-sistema';
        
        $modelo = new \Com\Daw2\Models\UsuarioSistemaModel();
        $data['usuarios'] = $modelo->getAll();                
        
        $this->view->showViews(array('templates/header.view.php', 'usuario_sistema.view.php', 'templates/footer.view.php'), $data);
    }  
    
    function mostrarAdd() : void{
        $data = [];
        $data['titulo'] = 'Alta usuario';
        $data['seccion'] = '/usuarios-sistema/add';
        $data['tituloDiv'] = 'Datos usuario';
        
        $rolModel = new \Com\Daw2\Models\AuxRolModel();
        $data['roles'] = $rolModel->getAll();
        
        $idiomaModel = new \Com\Daw2\Models\AuxIdiomasModel();
        $data['idiomas'] = $idiomaModel->getAll();
        
        $this->view->showViews(array('templates/header.view.php', 'edit.usuario_sistema.view.php', 'templates/footer.view.php'), $data);
    }
    
    function processAdd() : void{
        $errores = $this->checkAddForm($_POST);
        if(count($errores) == 0){
            $model = new \Com\Daw2\Models\UsuarioSistemaModel();
            $id = $model->insertUsuarioSistema($_POST);
            if($id > 0){
                header('location: /usuarios-sistema');
                die;                
            }
            else{
                $errores['nombre'] = 'Error desconocido. No se ha insertado el usuario.';
            }
        }
        $data['titulo'] = 'Alta usuario';
        $data['seccion'] = '/usuarios-sistema/add';
        $data['tituloDiv'] = 'Datos usuario';
        $data['input'] = filter_var_array($_POST, FILTER_SANITIZE_SPECIAL_CHARS);
        
        $rolModel = new \Com\Daw2\Models\AuxRolModel();
        $data['roles'] = $rolModel->getAll();
        
        $idiomaModel = new \Com\Daw2\Models\AuxIdiomasModel();
        $data['idiomas'] = $idiomaModel->getAll();
        
        $data['errores'] = $errores;
        
        $this->view->showViews(array('templates/header.view.php', 'edit.usuario_sistema.view.php', 'templates/footer.view.php'), $data);
    }
    
    private function checkAddForm(array $data) : array{
        $errores = [];
        if(empty($data['nombre'])){
            $errores['nombre'] = 'Inserte un nombre al usuario';
        }
        else if(!preg_match('/^[a-zA-Z_ ]{4,255}$/', $data['nombre'])){
            $errores['nombre'] = 'El nombre debe estar formado por letras, espacios o _ y tener una longitud de comprendida entre 4 y 255 caracteres.';
        }
        
        if(!filter_var($data['email'], FILTER_VALIDATE_EMAIL)){
            $errores['email'] = 'Inserte un email válido';
        }
        else{
            $model = new \Com\Daw2\Models\UsuarioSistemaModel();
            $usuario = $model->loadByEmail($data['email']);
            if(!is_null($usuario)){
                $errores['email'] = 'El email seleccionado ya está en uso';
            }
        }
        
        if(!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $data['pass'])){
            $errores['pass'] = 'El password debe contener una mayúscula, una minúscula y un número y tener una longitud de al menos 8 caracteres';
        }
        else if($data['pass'] != $data['pass']){
            $errores['pass'] = 'Las contraseñas no coinciden';
        }
        
        if(empty($data['id_rol'])){
            $errores['id_rol'] = 'Por favor, seleccione un rol';
        }
        else{
            $rolModel = new \Com\Daw2\Models\AuxRolModel();
            if(!filter_var($data['id_rol'], FILTER_VALIDATE_INT) || is_null($rolModel->loadRol((int)$data['id_rol']))){
                $errores['id_rol'] = 'Valor incorrecto';
            }
        }
        
        if(empty($data['id_idioma'])){
            $errores['id_idioma'] = 'Por favor, seleccione un idioma';
        }
        else{
            $idiomaModel = new \Com\Daw2\Models\AuxIdiomasModel();
            if(!filter_var($data['id_idioma'], FILTER_VALIDATE_INT) || is_null($idiomaModel->loadIdioma((int)$data['id_idioma']))){
                $errores['id_idioma'] = 'Valor incorrecto';
            }
        }
        
        return $errores;
        
    }
}