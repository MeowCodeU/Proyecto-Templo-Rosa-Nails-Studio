<?php

use App\Model\RolM;
use App\Model\SeguridadM;


/* ===================================================== */
/* REGISTRO DE USUARIO */
/* ===================================================== */

/* Variables utilizadas en la vista */
$mensajeRegistro = '';
$listaRoles = [];
$erroresRegistro = [];
$modeloSeguridad = null;

$datosRegistro = [
    'nombres' => '',
    'apellidos' => '',
    'cedula' => '',
    'telefono' => '',
    'correo' => '',
    'id_rol' => ''
];


/* Identificar las solicitudes asíncronas */
$esPeticionAjax = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';


/* Enviar la respuesta y finalizar la solicitud */
$responderJson = static function (array $datos, int $estado = 200): void {
    http_response_code($estado);

    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');

    echo json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);

    exit;
};


/* Asociar los errores del modelo con los nombres del formulario */
$adaptarErrores = static function (array $errores): array {
    $nombresCampos = ['nombre' => 'nombres', 'apellido' => 'apellidos'];
    $resultado = [];

    foreach ($errores as $campo => $mensaje) {
        $resultado[$nombresCampos[$campo] ?? $campo] = $mensaje;
    }

    return $resultado;
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
    /* Consultar los roles activos para presentar el formulario */
    $modeloRol = new RolM();
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


    /* ================================================= */
    /* REGISTRO MEDIANTE EL MODELO */
    /* ================================================= */

    if ($metodoSolicitud === 'POST' && ($datosFormulario['accion'] ?? '') === 'registrar_usuario') {

        /* Conservar los textos que volverá a mostrar la vista */
        foreach (array_keys($datosRegistro) as $campo) {
            $valor = $datosFormulario[$campo] ?? '';
            $datosRegistro[$campo] = is_string($valor) ? trim($valor) : '';
        }


        /* Entregar los datos originales al modelo para su validación */
        $modeloSeguridad = new SeguridadM();
        $modeloSeguridad->set_cedula($datosFormulario['cedula'] ?? '');
        $modeloSeguridad->set_nombre($datosFormulario['nombres'] ?? '');
        $modeloSeguridad->set_apellido($datosFormulario['apellidos'] ?? '');
        $modeloSeguridad->set_telefono($datosFormulario['telefono'] ?? '');
        $modeloSeguridad->set_correo($datosFormulario['correo'] ?? '');
        $modeloSeguridad->set_direccion('');
        $modeloSeguridad->set_ciudad('');
        $modeloSeguridad->set_idRol($datosFormulario['id_rol'] ?? '');
        $modeloSeguridad->set_nickname('');
        $modeloSeguridad->set_clave($datosFormulario['clave'] ?? '');

        $registrado = $modeloSeguridad->registrar($datosFormulario['confirmar_clave'] ?? '');

        if ($registrado) {

            /* Utilizar la autorización para registrar una sola cuenta */
            unset($_SESSION['registro_usuario_autorizado_hasta']);

            $_SESSION['mensaje_login'] = 'El usuario fue registrado exitosamente. Ya puede iniciar sesión.';
            $_SESSION['tipo_mensaje_login'] = 'success';

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

        $erroresRegistro = $adaptarErrores($modeloSeguridad->obtenerErroresValidacion());

        $mensajeRegistro = $erroresRegistro !== []
            ? (string) reset($erroresRegistro)
            : 'No fue posible completar el registro. Intente nuevamente.';


        /* Informar los errores sin perder los datos del formulario */
        if ($esPeticionAjax) {
            $responderJson([
                'ok' => false,
                'mensaje' => $mensajeRegistro,
                'campo' => array_key_first($erroresRegistro),
                'errores' => $erroresRegistro
            ], 422);
        }

    } elseif ($esPeticionAjax) {
        $responderJson([
            'ok' => false,
            'mensaje' => 'La solicitud enviada no es válida.'
        ], 400);

    } elseif ($metodoSolicitud !== 'GET') {
        $mensajeRegistro = 'La solicitud enviada no es válida.';
    }

} catch (\Throwable $error) {
    $estadoRespuesta = 500;
    $mensajeRegistro = 'No fue posible completar el registro. Intente nuevamente.';

    if ($error instanceof \JsonException) {
        $estadoRespuesta = 422;
        $mensajeRegistro = 'Los datos enviados no tienen un formato válido.';

    } elseif ($error instanceof \InvalidArgumentException) {
        $estadoRespuesta = 422;
        $mensajeRegistro = $error->getMessage();

        if ($modeloSeguridad instanceof SeguridadM) {
            $erroresRegistro = $adaptarErrores($modeloSeguridad->obtenerErroresValidacion());
        }

    } else {
        error_log($error->getMessage());
    }

    if ($esPeticionAjax) {
        $responderJson([
            'ok' => false,
            'mensaje' => $mensajeRegistro,
            'campo' => array_key_first($erroresRegistro),
            'errores' => $erroresRegistro
        ], $estadoRespuesta);
    }
}


/* ===================================================== */
/* CARGA DE LA VISTA */
/* ===================================================== */

require_once __DIR__ . '/../View/RegistroUsuarioV.php';