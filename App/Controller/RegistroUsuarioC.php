<?php

use App\Model\RolM;
use App\Model\SeguridadM;


/* ===================================================== */
/* REGISTRO DE USUARIO */
/* ===================================================== */

/* Variables utilizadas en la vista */
$mensajeRegistro = '';
$listaRoles = [];

$datosRegistro = [
    'nombres' => '',
    'apellidos' => '',
    'cedula' => '',
    'telefono' => '',
    'correo' => '',
    'id_rol' => ''
];

$esPeticionAjax = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';

$responderJson = static function (array $datos, int $estado = 200): void {
    http_response_code($estado);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');

    echo json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
};


/* ===================================================== */
/* AUTORIZACIÓN PARA CREAR LA CUENTA */
/* ===================================================== */

/* Comprobar que la clave universal haya sido validada previamente */
$autorizadoHasta = (int) ($_SESSION['registro_usuario_autorizado_hasta'] ?? 0);

if ($autorizadoHasta < time()) {
    unset($_SESSION['registro_usuario_autorizado_hasta']);

    if ($esPeticionAjax) {
        $responderJson([
            'ok' => false,
            'mensaje' => 'La autorización para registrarse ha vencido. Valide nuevamente la clave universal.',
            'redireccion' => 'Index.php?url=claveAdmin'
        ], 403);
    }

    header('Location: Index.php?url=claveAdmin');
    exit;
}


try {

    /* Crear las instancias de los modelos */
    $modeloSeguridad = new SeguridadM();
    $modeloRol = new RolM();

    /* Consultar los roles activos */
    $listaRoles = $modeloRol->consultar();


    /* ================================================= */
    /* LECTURA DE LOS DATOS DEL FORMULARIO */
    /* ================================================= */

    $datosFormulario = $_POST;
    $metodoSolicitud = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $tipoContenido = strtolower(trim(explode(';', $_SERVER['CONTENT_TYPE'] ?? '')[0]));

    if ($metodoSolicitud === 'POST' && $tipoContenido === 'application/json') {
        $datosFormulario = json_decode(file_get_contents('php://input'), true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($datosFormulario)) {
            throw new \InvalidArgumentException('Los datos enviados no tienen un formato válido.');
        }
    }

    $textoFormulario = static function (
        string $campo,
        bool $recortar = true
    ) use ($datosFormulario): string {

        $valor = $datosFormulario[$campo] ?? '';

        if (!is_string($valor)) {
            throw new \InvalidArgumentException('Los datos enviados no son válidos.');
        }

        return $recortar ? trim($valor) : $valor;
    };


    /* ================================================= */
    /* VALIDACIONES DEL REGISTRO */
    /* ================================================= */

    if (
        $metodoSolicitud === 'POST' &&
        $textoFormulario('accion') === 'registrar_usuario'
    ) {
        $datosRegistro = [
            'nombres' => $textoFormulario('nombres'),
            'apellidos' => $textoFormulario('apellidos'),
            'cedula' => $textoFormulario('cedula'),
            'telefono' => $textoFormulario('telefono'),
            'correo' => $textoFormulario('correo'),
            'id_rol' => $textoFormulario('id_rol')
        ];

        $clave = $textoFormulario('clave', false);
        $confirmarClave = $textoFormulario('confirmar_clave', false);
        $campoError = '';


        /* Validar los nombres */
        if (!preg_match("/^[\\p{L}\\s'-]{2,60}$/u", $datosRegistro['nombres'])) {
            $mensajeRegistro = 'Los nombres ingresados no son válidos.';
            $campoError = 'nombres';

        /* Validar los apellidos */
        } elseif (!preg_match("/^[\\p{L}\\s'-]{2,60}$/u", $datosRegistro['apellidos'])) {
            $mensajeRegistro = 'Los apellidos ingresados no son válidos.';
            $campoError = 'apellidos';

        /* Validar la cédula */
        } elseif (!preg_match('/^[0-9]{6,20}$/', $datosRegistro['cedula'])) {
            $mensajeRegistro = 'La cédula debe contener solamente números.';
            $campoError = 'cedula';

        /* Validar el teléfono cuando sea suministrado */
        } elseif (
            $datosRegistro['telefono'] !== '' &&
            !preg_match('/^[0-9+()\-\s]{7,20}$/', $datosRegistro['telefono'])
        ) {
            $mensajeRegistro = 'El teléfono ingresado no es válido.';
            $campoError = 'telefono';

        /* Validar el correo electrónico */
        } elseif (!filter_var($datosRegistro['correo'], FILTER_VALIDATE_EMAIL)) {
            $mensajeRegistro = 'El correo electrónico ingresado no es válido.';
            $campoError = 'correo';

        /* Validar la longitud de la contraseña */
        } elseif (strlen($clave) < 8) {
            $mensajeRegistro = 'La contraseña debe contener al menos 8 caracteres.';
            $campoError = 'clave';

        /* Validar que ambas contraseñas coincidan */
        } elseif ($clave !== $confirmarClave) {
            $mensajeRegistro = 'La contraseña y su confirmación no coinciden.';
            $campoError = 'confirmar_clave';

        /* Validar el identificador del rol */
        } elseif (
            !ctype_digit($datosRegistro['id_rol']) ||
            (int) $datosRegistro['id_rol'] <= 0
        ) {
            $mensajeRegistro = 'Debe seleccionar un rol válido.';
            $campoError = 'id_rol';

        } else {

            /* Comprobar que el rol exista y esté activo */
            $modeloRol->set_idRol((int) $datosRegistro['id_rol']);

            $rolSeleccionado = $modeloRol->buscar();

            if (
                !$rolSeleccionado ||
                ($rolSeleccionado['estado_rol'] ?? '') !== 'ACTIVO'
            ) {
                $mensajeRegistro = 'El rol seleccionado no se encuentra disponible.';
                $campoError = 'id_rol';

            } else {

                /* Asignar los datos al modelo */
                $modeloSeguridad->set_cedula($datosRegistro['cedula']);
                $modeloSeguridad->set_nombre($datosRegistro['nombres']);
                $modeloSeguridad->set_apellido($datosRegistro['apellidos']);
                $modeloSeguridad->set_telefono($datosRegistro['telefono']);
                $modeloSeguridad->set_correo($datosRegistro['correo']);
                $modeloSeguridad->set_direccion('');
                $modeloSeguridad->set_ciudad('');
                $modeloSeguridad->set_idRol((int) $datosRegistro['id_rol']);
                $modeloSeguridad->set_nickname('');
                $modeloSeguridad->set_clave($clave);

                $registrado = $modeloSeguridad->registrar();

                if ($registrado) {

                    /* Utilizar la autorización para registrar una sola cuenta */
                    unset($_SESSION['registro_usuario_autorizado_hasta']);

                    $_SESSION['mensaje_login'] = 'El usuario fue registrado exitosamente. Ya puede iniciar sesión.';

                    if ($esPeticionAjax) {
                        $responderJson([
                            'ok' => true,
                            'mensaje' => $_SESSION['mensaje_login'],
                            'redireccion' => 'Index.php?url=login'
                        ]);
                    }

                    header('Location: Index.php?url=login');
                    exit;
                }

                $mensajeRegistro = 'La persona ya posee una cuenta de usuario registrada.';
                $campoError = 'cedula';
            }
        }

        if ($esPeticionAjax) {
            $responderJson([
                'ok' => false,
                'mensaje' => $mensajeRegistro,
                'campo' => $campoError
            ], 422);
        }

    } elseif ($esPeticionAjax) {
        $responderJson(['ok' => false, 'mensaje' => 'La solicitud enviada no es válida.'], 400);
    }

} catch (\Throwable $error) {

    /* Registrar el error internamente y preparar el mensaje para el formulario */
    error_log($error->getMessage());

    $estadoRespuesta = 500;
    $mensajeRegistro = 'No fue posible completar el registro. Intente nuevamente.';

    if ($error instanceof \JsonException) {
        $estadoRespuesta = 422;
        $mensajeRegistro = 'Los datos enviados no tienen un formato válido.';

    } elseif ($error instanceof \InvalidArgumentException) {
        $estadoRespuesta = 422;
        $mensajeRegistro = $error->getMessage();
    }

    if ($esPeticionAjax) {
        $responderJson([
            'ok' => false,
            'mensaje' => $mensajeRegistro
        ], $estadoRespuesta);
    }
}


/* ===================================================== */
/* CARGA DE LA VISTA */
/* ===================================================== */

require_once __DIR__ . '/../View/RegistroUsuarioV.php';