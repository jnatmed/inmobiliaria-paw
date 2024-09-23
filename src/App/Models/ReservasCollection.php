<?php 

namespace Paw\App\Models;

use Paw\Core\Model;

use Exception;
use PDOException;


class ReservasCollection extends Model 
{
    
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

    public function reservarAlojamiento($id_publicacion, $id_usuario_reserva, $desde, $hasta, $precio_x_noche, $estado_reserva, $notas)
    {
        $data = [
            'id_publicacion' => $id_publicacion,
            'id_usuario_reserva' => $id_usuario_reserva,
            'fecha_inicio' => $desde,
            'fecha_fin' => $hasta,
            'precio_por_noche' => $precio_x_noche,
            'estado_reserva' => $estado_reserva,
            'notas' => $notas
        ];

        try {
            $result = $this->queryBuilder->insert('reservas_publicacion', $data);
            if ($result[1]) {
                return [
                    "exito" => true,
                    "mensaje" => "Reserva realizada con éxito.",
                    "nro_reserva" =>$result[0] // es el id_generado
                ];
            } else {
                return [
                    "exito" => false,
                    "mensaje" => "No se pudo realizar la reserva."
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