<?php

declare(strict_types=1);

namespace Com\Daw2\Controllers;

class UsuarioSistemaController extends \Com\Daw2\Core\BaseController {

    private const ROL_ADMIN = 1;
    private const ROL_PRODUCTOS = 2;
    private const ROL_CATEGORIAS = 3;
    private const ROL_PROVEEDOR = 4;
    private const ROL_AUDITOR = 5;

    function mostrarLogin() {
        $this->view->show("login.view.php");
    }

    function procesarLogin() {
        $modelo = new \Com\Daw2\Models\UsuarioSistemaModel();
        $email = $_POST["email"];
        $pass = $_POST["pass"];

        $usuario = $modelo->loadByEmail($email);
        $errores = $this->checkLogin($_POST);

        if (count($errores) == 0) {
            if (!is_null($usuario)) {
                if (password_verify($pass, $usuario["pass"])) {
                    unset($usuario["pass"]);

                    $_SESSION["usuario"] = $usuario;
                    $_SESSION["permisos"] = $this->getPermisos($usuario["id_rol"]);

                    $modelo->updateLogin($usuario["id_usuario"]);

                    header("location: /");
                } else {
                    $errores["pass"] = "Datos de acceso incorrectos";
                }
            } else {
                $errores["pass"] = "Datos de acceso incorrectos";
            }
        }

        $data = [];
        $data["inputEmail"] = $_POST["email"];
        $data["errores"] = $errores;
        $data["email"] = filter_input(INPUT_POST, "email", FILTER_SANITIZE_SPECIAL_CHARS);
        $this->view->show("login.view.php", $data);
    }

    private function getPermisos(int $idRol): array {
        $permisos = [
            'usuarios_sistema' => '',
            'categorias' => '',
            'productos' => '',
            'proveedores' => ''
        ];

        switch ($idRol) {

            case self::ROL_ADMIN:
                foreach ($permisos as $zona => $perm) {
                    $permisos[$zona] = 'rwd';
                }
                break;

            case self::ROL_AUDITOR:
                foreach ($permisos as $zona => $perm) {
                    $permisos[$zona] = 'r';
                }
                break;

            case self::ROL_PRODUCTOS:
                $permisos['productos'] = 'rwd';
                break;

            case self::ROL_CATEGORIAS:
                $permisos["categorias"] = 'rwd';
                break;

            case self::ROL_PROVEEDOR:
                $permisos["proveedores"] = 'rwd';
                break;
        }

        return $permisos;
    }

    function checkLogin(array $data): array {
        $errores = [];
        if (empty($data["email"])) {
            $errores["email"] = "Introduzca un email";
        }
        if (empty($data["pass"])) {
            $errores["pass"] = "Introduzca una contraseña";
        }
        return $errores;
    }

    function procesarLogOut() {
        session_destroy();
        header("location: /");
    }

    function mostrarTodos() {
        $data = [];
        $data['titulo'] = 'Todos los usuarios';
        $data['seccion'] = '/usuarios-sistema';

        $modelo = new \Com\Daw2\Models\UsuarioSistemaModel();
        $data['usuarios'] = $modelo->getAll();

        if (isset($_SESSION['mensaje'])) {
            $data['mensaje'] = $_SESSION['mensaje'];
            unset($_SESSION['mensaje']);
        }

        $this->view->showViews(array('templates/header.view.php', 'usuario_sistema.view.php', 'templates/footer.view.php'), $data);
    }
    
    function mostrarView(int $id){
        $data = [];
        $data['titulo'] = 'Datos usuario';
        $data['seccion'] = '/usuarios-sistema/view';
        $data['tituloDiv'] = 'Datos usuario';
                
        $rolModel = new \Com\Daw2\Models\AuxRolModel();
        $data['roles'] = $rolModel->getAll();
        
        $idiomaModel = new \Com\Daw2\Models\AuxIdiomasModel();
        $data['idiomas'] = $idiomaModel->getAll();
        
        $usuarioModel = new \Com\Daw2\Models\UsuarioSistemaModel();
        $data['input'] = $usuarioModel->loadUsuarioSistema($id);
        
        $data['readonly'] = true;
        
        if(!is_null($data['input'])){
            $this->view->showViews(array('templates/header.view.php', 'edit.usuario_sistema.view.php', 'templates/footer.view.php'), $data);
        }
        else{            
            header('location: /usuarios-sistema');
        }
    }

    function mostrarAdd(): void {
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

    function processAdd(): void {
        $errores = $this->checkAddForm($_POST);
        if (count($errores) == 0) {
            $model = new \Com\Daw2\Models\UsuarioSistemaModel();
            $id = $model->insertUsuarioSistema($_POST);
            if ($id > 0) {
                header('location: /usuarios-sistema');
                die;
            } else {
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

    function mostrarEdit(int $id): void {
        $data = [];
        $data['titulo'] = 'Editar usuario';
        $data['seccion'] = '/usuarios-sistema/edit';
        $data['tituloDiv'] = 'Datos usuario';

        $rolModel = new \Com\Daw2\Models\AuxRolModel();
        $data['roles'] = $rolModel->getAll();

        $idiomaModel = new \Com\Daw2\Models\AuxIdiomasModel();
        $data['idiomas'] = $idiomaModel->getAll();

        $usuarioModel = new \Com\Daw2\Models\UsuarioSistemaModel();
        $data['input'] = $usuarioModel->loadUsuarioSistema($id);

        if (!is_null($data['input'])) {
            $this->view->showViews(array('templates/header.view.php', 'edit.usuario_sistema.view.php', 'templates/footer.view.php'), $data);
        } else {

            header('location: /usuarios-sistema');
        }
    }

    function processEdit(int $id): void {
        $errores = $this->checkEditForm($_POST, $id);
        if (count($errores) == 0) {
            $model = new \Com\Daw2\Models\UsuarioSistemaModel();
            if ($model->editUsuarioSistema($id, $_POST) && ((empty($_POST['pass']) || $model->editPassword($id, $_POST['pass'])))) {
                header('location: /usuarios-sistema');
                die;
            } else {
                $errores['nombre'] = 'Error desconocido. No se ha editado el usuario.';
            }
        }
        $data['titulo'] = 'Editar usuario';
        $data['seccion'] = '/usuarios-sistema/edit';
        $data['tituloDiv'] = 'Datos usuario';
        $data['input'] = filter_var_array($_POST, FILTER_SANITIZE_SPECIAL_CHARS);

        $rolModel = new \Com\Daw2\Models\AuxRolModel();
        $data['roles'] = $rolModel->getAll();

        $idiomaModel = new \Com\Daw2\Models\AuxIdiomasModel();
        $data['idiomas'] = $idiomaModel->getAll();

        $data['errores'] = $errores;

        $this->view->showViews(array('templates/header.view.php', 'edit.usuario_sistema.view.php', 'templates/footer.view.php'), $data);
    }

    function processDelete(int $id): void {
        $model = new \Com\Daw2\Models\UsuarioSistemaModel();

        if ($_SESSION["usuario"]["id_usuario"] != $id) {
            if (!$model->delete($id)) {
                $mensaje = [];
                $mensaje['class'] = 'danger';
                $mensaje['texto'] = 'No se ha podido borrar al usuario.';
            } else {
                $mensaje = [];
                $mensaje['class'] = 'success';
                $mensaje['texto'] = 'Usuario eliminado con éxito.';
            }
        } else {
            $mensaje = [];
            $mensaje['class'] = 'danger';
            $mensaje['texto'] = 'No se puede borrar a uno mismo';
        }

        $_SESSION['mensaje'] = $mensaje;
        header('location: /usuarios-sistema');
    }

    function processBaja(int $id): void {
        $model = new \Com\Daw2\Models\UsuarioSistemaModel();
        $usuarioActual = $model->loadUsuarioSistema($id);
        
        if($_SESSION["usuario"]["id_usuario"] != $id){
            if (!is_null($usuarioActual)) {
                if ($usuarioActual['baja'] == 0) {
                    $baja = 1;
                } else {
                    $baja = 0;
                }
                if (!$model->baja($id, $baja)) {
                    $mensaje = [];
                    $mensaje['class'] = 'danger';
                    $mensaje['texto'] = 'No se ha podido cambiar el estado del usuario.';
                } else {
                    $mensaje = [];
                    $mensaje['class'] = 'success';
                    $mensaje['texto'] = 'Estado cambiado con éxito.';
                }
            } else {
                $mensaje = [];
                $mensaje['class'] = 'warning';
                $mensaje['texto'] = 'El usuario seleccionado no existe.';
            }
        }else {
            $mensaje = [];
            $mensaje['class'] = 'danger';
            $mensaje['texto'] = 'No se puede dar de baja a uno mismo';
        }
        
        $_SESSION['mensaje'] = $mensaje;
        header('location: /usuarios-sistema');
    }

    private function checkComunForm(array $data): array {
        $errores = [];
        if (empty($data['nombre'])) {
            $errores['nombre'] = 'Inserte un nombre al usuario';
        } else if (!preg_match('/^[a-zA-Z_ ]{4,255}$/', $data['nombre'])) {
            $errores['nombre'] = 'El nombre debe estar formado por letras, espacios o _ y tener una longitud de comprendida entre 4 y 255 caracteres.';
        }
        if (empty($data['id_rol'])) {
            $errores['id_rol'] = 'Por favor, seleccione un rol';
        } else {
            $rolModel = new \Com\Daw2\Models\AuxRolModel();
            if (!filter_var($data['id_rol'], FILTER_VALIDATE_INT) || is_null($rolModel->loadRol((int) $data['id_rol']))) {
                $errores['id_rol'] = 'Valor incorrecto';
            }
        }

        if (empty($data['id_idioma'])) {
            $errores['id_idioma'] = 'Por favor, seleccione un idioma';
        } else {
            $idiomaModel = new \Com\Daw2\Models\AuxIdiomasModel();
            if (!filter_var($data['id_idioma'], FILTER_VALIDATE_INT) || is_null($idiomaModel->loadIdioma((int) $data['id_idioma']))) {
                $errores['id_idioma'] = 'Valor incorrecto';
            }
        }
        return $errores;
    }

    private function checkPassword(array $data): array {
        $errores = [];
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $data['pass'])) {
            $errores['pass'] = 'El password debe contener una mayúscula, una minúscula y un número y tener una longitud de al menos 8 caracteres';
        } else if ($data['pass'] != $data['pass2']) {
            $errores['pass'] = 'Las contraseñas no coinciden';
        }
        return $errores;
    }

    private function checkAddForm(array $data): array {
        $errores = $this->checkComunForm($data);
        array_merge($errores, $this->checkPassword($data));

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errores['email'] = 'Inserte un email válido';
        } else {
            $model = new \Com\Daw2\Models\UsuarioSistemaModel();
            $usuario = $model->loadByEmail($data['email']);
            if (!is_null($usuario)) {
                $errores['email'] = 'El email seleccionado ya está en uso';
            }
        }
        return $errores;
    }

    private function checkEditForm(array $data, int $idUsuario): array {
        $errores = $this->checkComunForm($data);
        if (!empty($data['pass'])) {
            array_merge($errores, $this->checkPassword($data));
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errores['email'] = 'Inserte un email válido';
        } else {
            $model = new \Com\Daw2\Models\UsuarioSistemaModel();
            $usuario = $model->loadByEmailNotId($data['email'], $idUsuario);
            if (!is_null($usuario)) {
                $errores['email'] = 'El email seleccionado ya está en uso';
            }
        }
        return $errores;
    }
}
