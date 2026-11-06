<?php

namespace App\Model;

use App\Config\Database;
use App\Interfaces\CrudInterface;
use PDO;

/* ===================================================== */
/* MODELO DE BLOQUEOS DE HORARIO */
/* ===================================================== */

class BloqueoHorarioM extends Database implements CrudInterface
{
    /* ================================================= */
    /* ATRIBUTOS DEL MODELO */
    /* ================================================= */

    /* Conexión con la base de datos */
    private $conexionMD;

    /* Atributos de la tabla bloqueos_horarios */
    private $idBloqueo;
    private $idUsuarioEspecialista;
    private $idUsuarioRegistra;
    private $fecha;
    private $horaInicio;
    private $horaFin;
    private $motivo;


    /* ================================================= */
    /* ASIGNACIÓN DE DATOS */
    /* ================================================= */

    /* Asignar los valores recibidos a los atributos privados */
    public function set_idBloqueo($idBloqueo) {$this->idBloqueo = $idBloqueo;}
    public function set_idUsuarioEspecialista($idUsuarioEspecialista) {$this->idUsuarioEspecialista = $idUsuarioEspecialista;}
    public function set_idUsuarioRegistra($idUsuarioRegistra) {$this->idUsuarioRegistra = $idUsuarioRegistra;}
    public function set_fecha($fecha) {$this->fecha = $fecha;}
    public function set_horaInicio($horaInicio) {$this->horaInicio = $horaInicio;}
    public function set_horaFin($horaFin) {$this->horaFin = $horaFin;}
    public function set_motivo($motivo) {$this->motivo = $motivo;}


    /* ================================================= */
    /* CONSTRUCTOR */
    /* ================================================= */

    /* Obtener la conexión heredada de Database */
    public function __construct()
    {
        $this->conexionMD = $this->getConnection();
    }


    /* ================================================= */
    /* CONSULTAR BLOQUEOS DE HORARIO */
    /* ================================================= */

    /* Obtener los bloqueos activos con la especialista y el usuario que los registró */
    public function consultar()
    {
        $sql = "SELECT
                    b.id_bloqueo,
                    b.id_usuario_especialista,
                    b.id_usuario_registra,
                    b.fecha,
                    b.hora_inicio,
                    b.hora_fin,
                    b.motivo,
                    b.estado_bloqueo,

                    TRIM(CONCAT(pe.nombre, ' ', pe.apellido)) AS especialista,

                    TRIM(CONCAT(pr.nombre, ' ', pr.apellido)) AS usuario_registra

                FROM bloqueos_horarios b

                INNER JOIN usuarios ue
                    ON ue.id_usuario = b.id_usuario_especialista

                INNER JOIN personas pe
                    ON pe.id_persona = ue.id_persona

                INNER JOIN usuarios ur
                    ON ur.id_usuario = b.id_usuario_registra

                INNER JOIN personas pr
                    ON pr.id_persona = ur.id_persona

                WHERE b.estado_bloqueo = 'BLOQUEADO'

                ORDER BY b.fecha ASC, b.hora_inicio ASC";

        $stmt = $this->conexionMD->prepare($sql);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /* ================================================= */
    /* REGISTRAR BLOQUEO DE HORARIO */
    /* ================================================= */

    /* Guardar un nuevo período con estado BLOQUEADO */
    public function registrar()
    {
        $sql = "INSERT INTO bloqueos_horarios (
                    id_usuario_especialista,
                    id_usuario_registra,
                    fecha,
                    hora_inicio,
                    hora_fin,
                    motivo,
                    estado_bloqueo
                ) VALUES (
                    :id_usuario_especialista,
                    :id_usuario_registra,
                    :fecha,
                    :hora_inicio,
                    :hora_fin,
                    :motivo,
                    'BLOQUEADO'
                )";

        $stmt = $this->conexionMD->prepare($sql);

        $stmt->bindParam(':id_usuario_especialista', $this->idUsuarioEspecialista);
        $stmt->bindParam(':id_usuario_registra', $this->idUsuarioRegistra);
        $stmt->bindParam(':fecha', $this->fecha);
        $stmt->bindParam(':hora_inicio', $this->horaInicio);
        $stmt->bindParam(':hora_fin', $this->horaFin);
        $stmt->bindParam(':motivo', $this->motivo);

        return $stmt->execute();
    }


    /* ================================================= */
    /* MODIFICAR BLOQUEO DE HORARIO */
    /* ================================================= */

    /* Actualizar los datos de un bloqueo que todavía se encuentre activo */
    public function modificar()
    {
        $sql = "UPDATE bloqueos_horarios SET
                    id_usuario_especialista = :id_usuario_especialista,
                    fecha = :fecha,
                    hora_inicio = :hora_inicio,
                    hora_fin = :hora_fin,
                    motivo = :motivo
                WHERE id_bloqueo = :id_bloqueo
                  AND estado_bloqueo = 'BLOQUEADO'";

        $stmt = $this->conexionMD->prepare($sql);

        $stmt->bindParam(':id_usuario_especialista', $this->idUsuarioEspecialista);
        $stmt->bindParam(':fecha', $this->fecha);
        $stmt->bindParam(':hora_inicio', $this->horaInicio);
        $stmt->bindParam(':hora_fin', $this->horaFin);
        $stmt->bindParam(':motivo', $this->motivo);
        $stmt->bindParam(':id_bloqueo', $this->idBloqueo);

        return $stmt->execute();
    }


    /* ================================================= */
    /* DESBLOQUEAR HORARIO */
    /* ================================================= */

    /* Cambiar el estado a DESBLOQUEADO conservando el historial */
    public function eliminar()
    {
        $sql = "UPDATE bloqueos_horarios
                SET estado_bloqueo = 'DESBLOQUEADO'
                WHERE id_bloqueo = :id_bloqueo";

        $stmt = $this->conexionMD->prepare($sql);

        $stmt->bindParam(':id_bloqueo', $this->idBloqueo);

        return $stmt->execute();
    }


    /* ================================================= */
    /* COMPROBAR CONFLICTOS ENTRE BLOQUEOS */
    /* ================================================= */

    /* Detectar cruces con otros bloqueos y excluir el propio al editar */
    public function existeConflictoConBloqueo($idExcluir = null)
    {
        $sql = "SELECT COUNT(*)
                FROM bloqueos_horarios
                WHERE id_usuario_especialista = :id_usuario_especialista
                  AND fecha = :fecha
                  AND estado_bloqueo = 'BLOQUEADO'
                  AND hora_inicio < :hora_fin
                  AND hora_fin > :hora_inicio";

        if ($idExcluir !== null) {
            $sql .= " AND id_bloqueo <> :id_excluir";
        }

        $stmt = $this->conexionMD->prepare($sql);

        $stmt->bindParam(':id_usuario_especialista', $this->idUsuarioEspecialista);
        $stmt->bindParam(':fecha', $this->fecha);
        $stmt->bindParam(':hora_inicio', $this->horaInicio);
        $stmt->bindParam(':hora_fin', $this->horaFin);

        if ($idExcluir !== null) {
            $stmt->bindValue(':id_excluir', (int) $idExcluir, PDO::PARAM_INT);
        }

        $stmt->execute();

        return (int) $stmt->fetchColumn() > 0;
    }


    /* ================================================= */
    /* COMPROBAR BLOQUEOS CONTRA CITAS */
    /* ================================================= */

    /* Detectar citas que coincidan con el bloqueo sin contar las canceladas */
    public function existeConflictoConAgendamiento()
    {
        $sql = "SELECT COUNT(*)
                FROM agendamientos a

                INNER JOIN estados_agendamientos ea
                    ON ea.id_estado_agendamiento = a.id_estado_agendamiento

                WHERE a.id_usuario_asignado = :id_usuario_especialista
                  AND a.fecha = :fecha
                  AND UPPER(ea.nombre_estado) <> 'CANCELADA'
                  AND a.hora < :hora_fin
                  AND ADDTIME(
                        a.hora,
                        SEC_TO_TIME(a.duracion_minutos * 60)
                      ) > :hora_inicio";

        $stmt = $this->conexionMD->prepare($sql);

        $stmt->bindParam(':id_usuario_especialista', $this->idUsuarioEspecialista);
        $stmt->bindParam(':fecha', $this->fecha);
        $stmt->bindParam(':hora_inicio', $this->horaInicio);
        $stmt->bindParam(':hora_fin', $this->horaFin);

        $stmt->execute();

        return (int) $stmt->fetchColumn() > 0;
    }


    /* ================================================= */
    /* COMPROBAR CITAS CONTRA BLOQUEOS */
    /* ================================================= */

    /* Verificar si el horario de una cita coincide con un bloqueo activo */
    public function existeBloqueoParaAgendamiento(
        $idUsuarioEspecialista,
        $fecha,
        $horaInicio,
        $horaFin
    ) {
        $sql = "SELECT COUNT(*)
                FROM bloqueos_horarios
                WHERE id_usuario_especialista = :id_usuario_especialista
                  AND fecha = :fecha
                  AND estado_bloqueo = 'BLOQUEADO'
                  AND hora_inicio < :hora_fin
                  AND hora_fin > :hora_inicio";

        $stmt = $this->conexionMD->prepare($sql);

        $stmt->bindValue(':id_usuario_especialista', (int) $idUsuarioEspecialista, PDO::PARAM_INT);
        $stmt->bindValue(':fecha', $fecha);
        $stmt->bindValue(':hora_inicio', $horaInicio);
        $stmt->bindValue(':hora_fin', $horaFin);

        $stmt->execute();

        return (int) $stmt->fetchColumn() > 0;
    }
}