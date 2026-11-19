<?php

namespace App\Traits;


/* ===================================================== */
/* VALIDACIONES DE USUARIOS */
/* ===================================================== */

trait ValidacionUsuarioTrait
{
    use ValidacionTrait;


    /* ================================================= */
    /* VALIDAR LOS DATOS DEL INICIO DE SESIÓN */
    /* ================================================= */

    protected function validarDatosAcceso(mixed $cedula, mixed $clave): ?array
    {
        $cedulaValidada = $this->validarCedulaAcceso($cedula);


        /* Comprobar la contraseña existente sin imponer una regla nueva */
        if (!is_string($clave) || $clave === '' || str_contains($clave, "\0")) {
            $this->erroresValidacion['clave'] = 'Ingrese una contraseña válida.';
        }

        if ($this->erroresValidacion) {
            return null;
        }

        return [
            'cedula' => $cedulaValidada,
            'clave' => $clave
        ];
    }


    /* ================================================= */
    /* VALIDAR LA CÉDULA ANTES DE CONSULTAR */
    /* ================================================= */

    protected function validarCedulaAcceso(mixed $cedula): ?string
    {
        $this->erroresValidacion = [];
        $this->validarCampos(['cedula' => $cedula], ['cedula']);

        if (is_string($cedula) && str_contains($cedula, "\0")) {
            $this->erroresValidacion['cedula'] = 'La cédula debe contener solo números (6 a 20 dígitos).';
        }

        return $this->erroresValidacion ? null : trim($cedula);
    }


    /* ================================================= */
    /* VALIDAR LOS DATOS DE LA CLAVE UNIVERSAL */
    /* ================================================= */

    protected function validarDatosClaveUniversal(mixed $claveUniversal): ?string
    {
        $this->erroresValidacion = [];

        if (!is_string($claveUniversal) || str_contains($claveUniversal, "\0")) {
            $this->erroresValidacion['clave_universal'] = 'La clave universal ingresada no es válida.';
            return null;
        }

        $claveUniversal = trim($claveUniversal);

        if ($claveUniversal === '') {
            $this->erroresValidacion['clave_universal'] = 'Debe ingresar la clave universal.';
            return null;
        }

        return $claveUniversal;
    }


    /* ================================================= */
    /* VALIDAR DATOS PERSONALES Y DATOS DE LA CUENTA */
    /* ================================================= */

    protected function validarDatosUsuario(bool $esRegistro, mixed $confirmarClave = null): bool
    {
        $datos = [
            'nombre' => $this->nombre,
            'apellido' => $this->apellido,
            'telefono' => $this->telefono ?? '',
            'correo' => $this->correo,
            'direccion' => $this->direccion ?? '',
            'ciudad' => $this->ciudad ?? ''
        ];
        $obligatorios = ['nombre', 'apellido', 'correo'];

        if ($esRegistro) {
            $datos['cedula'] = $this->cedula;
            $obligatorios[] = 'cedula';
        }

        $this->validarCampos($datos, $obligatorios);
        $idRol = $this->validarIdentificador($this->idRol, 'id_rol', 'Seleccione un rol válido.');
        $idUsuario = null;
        $idPersona = null;

        if (!$esRegistro) {
            $idUsuario = $this->validarIdentificador($this->idUsuario, 'id_usuario', 'Seleccione un usuario válido.');

            if ($this->idPersona !== null) {
                $idPersona = $this->validarIdentificador($this->idPersona, 'id_persona', 'Seleccione una persona válida.');
            }
        }

        $nickname = $this->nickname ?? '';

        if (!is_string($nickname) || preg_match('//u', $nickname) !== 1) {
            $this->erroresValidacion['nickname'] = 'Ingrese un nickname válido.';
        } elseif ($this->longitud(trim($nickname)) > 50) {
            $this->erroresValidacion['nickname'] = 'El nickname no puede superar 50 caracteres.';
        }

        if ($esRegistro || ($this->clave !== null && $this->clave !== '')) {
            $this->validarClaveSeguridad($this->clave, 'clave', $confirmarClave);
        }

        if ($this->erroresValidacion !== []) return false;

        foreach ($datos as $campo => $valor) $this->$campo = trim($valor);

        $this->idRol = $idRol;
        $this->nickname = trim($nickname);

        if (!$esRegistro) {
            $this->idUsuario = $idUsuario;
            $this->idPersona = $idPersona;
        }

        return true;
    }


    /* ================================================= */
    /* VALIDAR EL IDENTIFICADOR DE UN USUARIO */
    /* ================================================= */

    protected function validarIdUsuarioSeguridad(): bool
    {
        $this->erroresValidacion = [];
        $id = $this->validarIdentificador($this->idUsuario, 'id_usuario', 'Seleccione un usuario válido.');

        if ($id === null) return false;

        $this->idUsuario = $id;
        return true;
    }


    /* ================================================= */
    /* VALIDAR LA DESACTIVACIÓN SOLICITADA */
    /* ================================================= */

    protected function validarDesactivacionUsuario(): bool
    {
        $this->validarIdUsuarioSeguridad();

        if (!is_string($this->estadoUsuario) || strtoupper(trim($this->estadoUsuario)) !== 'INACTIVO') {
            $this->erroresValidacion['estado_usuario'] = 'Seleccione una desactivación válida.';
        }

        return $this->erroresValidacion === [];
    }


    /* ================================================= */
    /* VALIDAR CONTRASEÑAS NUEVAS Y SU CONFIRMACIÓN */
    /* ================================================= */

    protected function validarClaveSeguridad(mixed $clave, string $campo, mixed $confirmarClave = null): void
    {
        if (!is_string($clave) || str_contains($clave, "\0") || $this->longitud($clave) < 8 || !$this->validarClave($clave)) {
            $this->erroresValidacion[$campo] = 'La contraseña debe tener al menos 8 caracteres, una mayúscula, una minúscula, un número y un símbolo, sin espacios.';
        } elseif (strlen($clave) > 72) {
            $this->erroresValidacion[$campo] = 'La contraseña es demasiado larga. Utilice una más corta.';
        }

        if ($confirmarClave !== null && (!is_string($confirmarClave) || $confirmarClave !== $clave)) {
            $this->erroresValidacion['confirmar_clave'] = 'Las contraseñas no coinciden.';
        }
    }


    /* ================================================= */
    /* VALIDAR LOS DATOS EDITABLES DEL PERFIL */
    /* ================================================= */

    protected function validarDatosPerfil(mixed $idUsuario, mixed $nickname, mixed $telefono, mixed $correo): ?array
    {
        $this->codigoResultado = 'datos_invalidos';
        $this->validarCampos(['telefono' => $telefono, 'correo' => $correo]);
        $id = $this->validarIdentificador($idUsuario, 'id_usuario', 'No fue posible identificar la cuenta del usuario autenticado.');

        if (!is_string($nickname) || preg_match('//u', $nickname) !== 1) {
            $this->erroresValidacion['nickname'] = 'Ingrese un nickname válido.';
        } elseif ($this->longitud(trim($nickname)) > 50) {
            $this->erroresValidacion['nickname'] = 'El nickname no puede superar 50 caracteres.';
        }

        if ($this->erroresValidacion !== []) return null;

        return ['id_usuario' => $id, 'nickname' => trim($nickname), 'telefono' => trim($telefono), 'correo' => trim($correo)];
    }


    /* ================================================= */
    /* VALIDAR LOS DATOS DEL CAMBIO DE CONTRASEÑA */
    /* ================================================= */

    protected function validarCambioClavePerfil(mixed $idUsuario, mixed $claveActual, mixed $nuevaClave, mixed $confirmarClave): ?int
    {
        $this->erroresValidacion = [];
        $this->codigoResultado = 'datos_invalidos';
        $id = $this->validarIdentificador($idUsuario, 'id_usuario', 'No fue posible identificar la cuenta del usuario autenticado.');

        if (!is_string($claveActual) || $claveActual === '') {
            $this->erroresValidacion['clave_actual'] = 'Ingrese la contraseña actual.';
        }

        $this->validarClaveSeguridad($nuevaClave, 'nueva_clave', $confirmarClave ?? '');

        if (!is_string($claveActual) || !is_string($nuevaClave) || !is_string($confirmarClave)) {
            $this->codigoResultado = 'solicitud_invalida';
        } elseif ($claveActual === '' || $nuevaClave === '' || $confirmarClave === '') {
            $this->codigoResultado = 'datos_incompletos';
        } elseif ($this->longitud($nuevaClave) < 8) {
            $this->codigoResultado = 'clave_corta';
        } elseif ($nuevaClave !== $confirmarClave) {
            $this->codigoResultado = 'no_coinciden';
        }

        return $this->erroresValidacion === [] ? $id : null;
    }


    /* ================================================= */
    /* VALIDAR LA FOTOGRAFÍA RECIBIDA */
    /* ================================================= */

    protected function validarFotoPerfil(mixed $archivo): ?array
    {
        if ($archivo === null) return null;

        if (!is_array($archivo) || !isset($archivo['error']) || !is_int($archivo['error'])) {
            return $this->errorFotoPerfil('No fue posible recibir la fotografía seleccionada.');
        }

        if ($archivo['error'] === UPLOAD_ERR_NO_FILE) return null;

        if (in_array($archivo['error'], [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)) {
            return $this->errorFotoPerfil('La fotografía supera el tamaño permitido para la carga.');
        }

        if ($archivo['error'] !== UPLOAD_ERR_OK) {
            return $this->errorFotoPerfil('La fotografía no se recibió correctamente. Selecciónela nuevamente.');
        }

        $rutaTemporal = $archivo['tmp_name'] ?? '';

        if (!is_string($rutaTemporal) || !is_uploaded_file($rutaTemporal)) {
            return $this->errorFotoPerfil('El archivo recibido no es una carga válida.');
        }

        $tamano = filesize($rutaTemporal);

        if ($tamano === false) throw new \RuntimeException('No fue posible consultar el tamaño de la fotografía.');

        if ($tamano <= 0 || $tamano > 2 * 1024 * 1024) {
            return $this->errorFotoPerfil('Seleccione una fotografía de hasta 2 MB.');
        }

        $tiposPermitidos = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $informacion = new \finfo(FILEINFO_MIME_TYPE);
        $tipo = $informacion->file($rutaTemporal);
        $dimensiones = @getimagesize($rutaTemporal);

        if (!is_string($tipo) || !isset($tiposPermitidos[$tipo]) || $dimensiones === false ||
            ($dimensiones['mime'] ?? '') !== $tipo || $dimensiones[0] <= 0 || $dimensiones[1] <= 0) {
            return $this->errorFotoPerfil('La fotografía debe ser una imagen JPEG, PNG o WebP válida.');
        }

        return ['ruta_temporal' => $rutaTemporal, 'extension' => $tiposPermitidos[$tipo]];
    }


    /* Registrar el error de la fotografía sin modificar la base de datos */
    private function errorFotoPerfil(string $mensaje): ?array
    {
        $this->codigoResultado = 'foto_invalida';
        $this->erroresValidacion['foto'] = $mensaje;
        return null;
    }
}