<?php

namespace Paw\App\Models;

use Paw\Core\Model;
use PDOException;
use Exception;

class UserCollection extends Model
{
    /**
     * metodos que deberian estar para
     * autenticar usuario
     * debiera haber una consulta a la base de datos
     * usuario queryBuilder
     */

    public function findByEmailAndPassword($email, $password)
    {
        $result = $this->queryBuilder->select('usuarios', ['email' => $email]);

        // Verificar si se encontró un usuario con ese nombre de usuario
        if ($result && count($result) > 0) {
            // Comparar la contraseña hasheada almacenada en la base de datos con la proporcionada
            $hashedPassword = $result[0]['contrasenia'];
            if (password_verify($password, $hashedPassword)) {
                // Si las contraseñas coinciden, se devuelve el usuario encontrado
                return $result[0];
            }
        }
        return null;
    }

    public function buscarToken($token)
    {
        try {
            // Utiliza el método select del QueryBuilder para buscar por email
            $result = $this->queryBuilder->select('password_resets', ['token' => $token]);

            // Verifica si se encontró algún resultado
            if (empty($result)) {
                return [
                    'exito' => false,
                    'message' => 'No se encontró el token'
                ]; // No se encontró ningún usuario con ese email
            }

            return [
                'exito' => true,
                'token' => $result[0]
            ];
        } catch (PDOException $e) {
            // Manejar la excepción de la base de datos
            throw new Exception('Error al buscar el token en la base de datos: ' . $e->getMessage());
        } catch (Exception $e) {
            // Manejar otras excepciones
            throw new Exception('[BuscarToken] Ocurrió un error inesperado: ' . $e->getMessage());
        }
    }

    public function buscarCorreoEnUsuarios($email)
    {
        try {
            // Utiliza el método select del QueryBuilder para buscar por email
            $result = $this->queryBuilder->select('usuarios', ['email' => $email]);

            // Verifica si se encontró algún resultado
            if (empty($result)) {
                return [
                    'exito' => false,
                    'message' => 'No se encontró el correo'
                ]; // No se encontró ningún usuario con ese email
            }

            return [
                'exito' => true,
                'usuario' => $result[0]
            ];
        } catch (PDOException $e) {
            // Manejar la excepción de la base de datos
            throw new Exception('Error al buscar el correo en la base de datos: ' . $e->getMessage());
        } catch (Exception $e) {
            // Manejar otras excepciones
            throw new Exception('Ocurrió un error inesperado: ' . $e->getMessage());
        }
    }

    public function actualizarContrasenia($userId, $password)
    {
        try {
            $data = [
                'contrasenia' => password_hash($password, PASSWORD_BCRYPT)
            ];
            $where = [
                'id' => $userId
            ];
            $this->queryBuilder->update('usuarios', $data, $where);
            return ['exito' => true];
        } catch (\Exception $e) {
            $this->logger->error('Error al actualizar la contraseña: ' . $e->getMessage());
            return ['exito' => false, 'message' => $e->getMessage()];
        }
    }

    public function insertResetToken($userId, $token)
    {
        try {
            $data = [
                'user_id' => $userId,
                'token' => $token,
                'created_at' => date('Y-m-d H:i:s')
            ];
            $result = $this->queryBuilder->insert('password_resets', $data);
            return $result;
        } catch (\Exception $e) {
            $this->logger->error('Error al insertar el token de restablecimiento de contraseña: ' . $e->getMessage());
            return false;
        }
    }

    public function insert($table, $data)
    {
        global $log;
        try {
            // Llamar al método insert del QueryBuilder y devolver el resultado
            return $this->queryBuilder->insert($table, $data, ['email']);
        } catch (PDOException $e) {
            // Capturar excepción y manejarla
            $msjError = '(UserCollection) (method insert) PDOException Error en la inserción: ' . $e->getMessage();

            $log->error("error al registrar: ", ["Error al insertar datos en la tabla $table: " . $e->getMessage()]);

            return [null, $msjError];  // O devuelve un valor que indique que hubo un error
        } catch (Exception $e) {
            // Manejo de errores y excepciones
            $msjError = '(UserCollection) (method insert) Exception Error en la inserción: ' . $e->getMessage();
            $this->logger->error($msjError);

            // Retornar una estructura que indique que hubo un error
            return [null, $msjError];
        }
    }



    public function findById($id)
    {
        global $log;
        try {
            $log->info("findById id: ", [$id]);
            $result = $this->queryBuilder->selectUserAndTipo($id);
            if ($result && count($result) > 0) {
                $log->info("result: ", [$result[0]]);
                return $result[0];
            }
            return null;
        } catch (PDOException $e) {
            // Manejo de la excepción
            if ($this->logger) { // Asumiendo que tienes un logger configurado en tu modelo
                $this->logger->error("Error al obtener el usuario por ID: " . $id, ['exception' => $e]);
            }
            return null;
        }
    }

    public function updateEmail($userId, $email)
    {
        try {
            $data = [
                'email' => $email
            ];
            $where = [
                'id' => $userId
            ];
            $this->queryBuilder->update('usuarios', $data, $where);
            return ['exito' => true];
        } catch (\Exception $e) {
            $this->logger->error('Error al actualizar la email: ' . $e->getMessage());
            return ['exito' => false, 'message' => $e->getMessage()];
        }
    }
}
