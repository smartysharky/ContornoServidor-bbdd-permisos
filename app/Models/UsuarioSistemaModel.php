<?php

declare(strict_types=1);

namespace Com\Daw2\Models;

class UsuarioSistemaModel extends \Com\Daw2\Core\BaseModel {
    
    private const SELECT_FROM = "SELECT us.*, ar.nombre_rol, ai.nombre_idioma FROM usuario_sistema us LEFT JOIN aux_rol ar ON ar.id_rol = us.id_rol LEFT JOIN aux_idiomas ai ON ai.id_idioma = us.id_idioma ORDER BY us.nombre";
    
    function getAll() : array{
        return $this->pdo->query(self::SELECT_FROM)->fetchAll();
    }
    
    /**
     * 
     * @param array $data 
     * @return int 0 si hay algún error. El id autogenerado en caso de éxito.
     */
    function insertUsuarioSistema(array $data) : int{
        $query = "INSERT INTO usuario_sistema (id_rol, email, pass, nombre, id_idioma) VALUES(:id_rol, :email, :pass, :nombre, :id_idioma)";
        $stmt = $this->pdo->prepare($query);
        $vars = [
            'id_rol' => $data['id_rol'],
            'email' => $data['email'],
            'pass' => password_hash($data['pass'], PASSWORD_DEFAULT),
            'nombre' => $data['nombre'],
            'id_idioma' => $data['id_idioma']
        ];
        if($stmt->execute($vars)){            
            return (int)$this->pdo->lastInsertId();
        }
        else{
            return 0;
        }
    }
    
    function editUsuarioSistema(int $idUsuario, array $data) : bool{
        $query = "UPDATE usuario_sistema SET id_rol=:id_rol, email=:email, nombre=:nombre, id_idioma=:id_idioma WHERE id_usuario=:id_usuario";
        $stmt = $this->pdo->prepare($query);
        $vars = [
            'id_rol' => $data['id_rol'],
            'email' => $data['email'],            
            'nombre' => $data['nombre'],
            'id_idioma' => $data['id_idioma'],
            'id_usuario' => $idUsuario
        ];
        return $stmt->execute($vars);            
    }
    
    function editPassword(int $idUsuario, string $pass) : bool{
        $query = "UPDATE usuario_sistema SET pass=? WHERE id_usuario=?";
        $stmt = $this->pdo->prepare($query);
        $encryptedPass = password_hash($pass, PASSWORD_DEFAULT);
        return $stmt->execute([$encryptedPass, $idUsuario]);            
    }
    
    function loadByEmail(string $email) : ?array{
        $query = "SELECT * FROM usuario_sistema WHERE email = ?";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$email]);
        if($row = $stmt->fetch()){
            return $row;
        }
        else{
            return null;
        }                
    }
    
    function loadByEmailNotId(string $email, int $id) : ?array{
        $query = "SELECT * FROM usuario_sistema WHERE email = ? AND id_usuario != ?";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$email, $id]);
        if($row = $stmt->fetch()){
            return $row;
        }
        else{
            return null;
        }                
    }
    
    function loadUsuarioSistema(int $id) : ?array{
        $query = "SELECT * FROM usuario_sistema WHERE id_usuario = ?";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$id]);
        if($row = $stmt->fetch()){
            return $row;
        }
        else{
            return null;
        }   
    }
}