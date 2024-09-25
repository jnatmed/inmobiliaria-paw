<?php 

namespace Paw\App\Models;

use Paw\Core\Model;

use Exception;
use PDOException;


class ReservasCollection extends Model 
{
    public $table = 'reservas_publicacion';

    public function getReservas($id_publicacion)
    {
        // Utilizamos el QueryBuilder para obtener las reservas
        $result = $this->queryBuilder->select('reservas_publicacion', ['id_publicacion' => $id_publicacion]);

        // Formatear los resultados según el formato requerido
        $reservas = [];
        foreach ($result as $row) {
            $fecha_inicio = (new \DateTime($row['fecha_inicio']))->format('d/m/Y');
            $fecha_fin = (new \DateTime($row['fecha_fin']))->format('d/m/Y');
            $reservas[] = [$fecha_inicio, $fecha_fin];
        }

        return $reservas;
    }
    
    public function obtenerReservasPendientesYConfirmadas($id_usuario) {
        return $this->queryBuilder->getReservasByUsuario($id_usuario);
    }

    public function getSolicitudesDeReserva($id_usuario) {
        try{
            $params = ['id_usuario_reserva' => $id_usuario];
            $result = $this->queryBuilder->select('reservas_publicacion', $params);

            if (!empty($result)) {
                return $result;
            } else {
                return []; 
            }
        } catch (Exception $e) {

            return false;
        }
    }    

    public function actualizarEstadoReserva($idReserva, $accion)
    {
        try {

            if($accion === 'aceptar'){
                $nuevoEstado = 'confirmada';
            }
            if($accion === 'cancelar'){
                $nuevoEstado = 'cancelada';
            }
            if($accion === 'rechazar'){
                $nuevoEstado = 'rechazada';
            }

            $this->queryBuilder->update(
                'reservas_publicacion',
                ['estado_reserva' => $nuevoEstado],
                ['id' => $idReserva]
            );
        } catch (Exception $e) {
            throw new Exception("Error al aceptar la reserva: " . $e->getMessage());
        }
    }    

    public function reservarAlojamiento(Reserva $reserva)
    {
        try {
            $data = $reserva->getAll();
            $this->logger->info("data : ", [$data]);

            list($idReservaGenerado, $resultado) = $this->queryBuilder->insert($this->table, $data);

            $this->logger->info("Info Reserva (Method - create): " , [$idReservaGenerado, $resultado]);

            if ($idReservaGenerado) {
                return [
                    "exito" => true,
                    "mensaje" => "Reserva realizada con éxito",
                    "nro_reserva" => $idReservaGenerado // es el id_generado
                ];
            } else {
                return [
                    "exito" => false,
                    "mensaje" => "No se pudo realizar la reserva, error: " . $resultado 
                ];
            }
        } catch (PDOException $e) {
            global $log;
            $log->error("Error al reservar el alojamiento: " . $e->getMessage());
            return [
                "exito" => false,
                "mensaje" => "Error al reservar el alojamiento: " . $e->getMessage()
            ];
        }
    }
}