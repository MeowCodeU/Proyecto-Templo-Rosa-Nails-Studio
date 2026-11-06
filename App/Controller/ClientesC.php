<?php

use App\Model\ClientesM;


/* ================================================= */
/* IDENTIFICAR SOLICITUD */
/* ================================================= */

/* Identificar las solicitudes AJAX y los datos enviados como JSON */
$contenidoJsonClientes = stripos(
    (string) ($_SERVER['CONTENT_TYPE'] ?? ''),
    'application/json'
) === 0;

$esAjaxClientes = $contenidoJsonClientes || strtolower(
    (string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')
) === 'xmlhttprequest';


/* ================================================= */
/* RESPUESTAS JSON */
/* ================================================= */

/* Enviar una respuesta JSON y finalizar la solicitud */
$responderClientes = static function (
    array $datos,
    int $codigo = 200
): void {
    http_response_code($codigo);

    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');

    echo json_encode(
        $datos,
        JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
    );

    exit;
};


/* ================================================= */
/* PROCESAR SOLICITUD */
/* ================================================= */

try {

    /* Comprobar el método de la solicitud */
    $metodoClientes = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if (!in_array($metodoClientes, ['GET', 'POST'], true)) {
        header('Allow: GET, POST');

        $responderClientes(
            [
                'ok' => false,
                'mensaje' => 'Método no permitido.'
            ],
            405
        );
    }

    $datosClientes = $_POST;

    /* Recibir datos JSON o formularios normales */
    if ($metodoClientes === 'POST' && $contenidoJsonClientes) {

        $entradaClientes = json_decode(
            file_get_contents('php://input'),
            false,
            32,
            JSON_THROW_ON_ERROR
        );

        if (!$entradaClientes instanceof stdClass) {
            throw new InvalidArgumentException(
                'La solicitud debe contener un objeto JSON.'
            );
        }

        $datosClientes = get_object_vars($entradaClientes);
    }

    /* Modelo de Clientes */
    $modeloClientes = new ClientesM();


    /* ================================================= */
    /* CONSULTAR */
    /* ================================================= */

    /* Consultar clientes activos mediante AJAX */
    if ($metodoClientes === 'GET' && $esAjaxClientes) {

        $responderClientes(
            [
                'ok' => true,
                'clientes' => $modeloClientes->consultar()
            ]
        );
    }


    /* Procesar acciones enviadas por los formularios */
    if ($metodoClientes === 'POST') {

        $accionClientes = $datosClientes['accion'] ?? '';

        /* Comprobar que la acción esté permitida */
        if (
            !is_string($accionClientes) ||
            !in_array(
                $accionClientes,
                [
                    'consultar',
                    'buscar',
                    'registrar',
                    'modificar',
                    'desactivar',
                    'eliminar'
                ],
                true
            )
        ) {
            throw new InvalidArgumentException(
                'La acción solicitada no es válida.'
            );
        }

        /* Consultar clientes activos */
        if ($accionClientes === 'consultar') {

            $responderClientes(
                [
                    'ok' => true,
                    'clientes' => $modeloClientes->consultar()
                ]
            );
        }


        /* ================================================= */
        /* IDENTIFICAR CLIENTE */
        /* ================================================= */

        /* Comprobar la cédula recibida */
        $cedulaClientes = $datosClientes['cedula'] ?? '';

        if (
            !is_string($cedulaClientes) ||
            trim($cedulaClientes) === ''
        ) {
            $responderClientes(
                [
                    'ok' => false,
                    'mensaje' => 'Indique la cédula del cliente.',
                    'errores' => [
                        'cedula' => 'La cédula es obligatoria.'
                    ]
                ],
                422
            );
        }

        $modeloClientes->set_Cedula(trim($cedulaClientes));


        /* Buscar el cliente activo antes de consultar o modificar sus datos */
        if ($accionClientes !== 'registrar') {

            $clienteActual = $modeloClientes->buscar();

            if (!$clienteActual) {
                $responderClientes(
                    [
                        'ok' => false,
                        'mensaje' =>
                            'El cliente no se encuentra activo. Actualice la lista.'
                    ],
                    404
                );
            }

            /* Devolver los datos del cliente solicitado */
            if ($accionClientes === 'buscar') {
                $responderClientes(
                    [
                        'ok' => true,
                        'cliente' => $clienteActual
                    ]
                );
            }
        }


        /* ================================================= */
        /* REGISTRAR Y MODIFICAR */
        /* ================================================= */

        if (
            in_array(
                $accionClientes,
                ['registrar', 'modificar'],
                true
            )
        ) {

            /* Comprobar los campos obligatorios de la vista */
            $erroresClientes = [];

            foreach (
                ['nombre' => 'El nombre', 'apellido' => 'El apellido']
                as $campo => $etiqueta
            ) {
                $valor = $datosClientes[$campo] ?? '';

                if (!is_string($valor) || trim($valor) === '') {
                    $erroresClientes[$campo] =
                        $etiqueta . ' es obligatorio.';
                }
            }

            /* Comprobar el tipo de los campos opcionales */
            foreach (
                ['telefono', 'correo', 'direccion', 'ciudad', 'alergias']
                as $campo
            ) {
                if (
                    isset($datosClientes[$campo]) &&
                    !is_string($datosClientes[$campo])
                ) {
                    $erroresClientes[$campo] =
                        'El valor del campo no es válido.';
                }
            }

            /* Devolver los errores antes de guardar */
            if ($erroresClientes) {
                $responderClientes(
                    [
                        'ok' => false,
                        'mensaje' => 'Revise los campos señalados.',
                        'errores' => $erroresClientes
                    ],
                    422
                );
            }

            /* Asignar los datos al modelo */
            $modeloClientes->set_Nombre($datosClientes['nombre']);
            $modeloClientes->set_Apellido($datosClientes['apellido']);
            $modeloClientes->set_Telefono($datosClientes['telefono'] ?? '');
            $modeloClientes->set_Correo($datosClientes['correo'] ?? '');
            $modeloClientes->set_Direccion($datosClientes['direccion'] ?? '');
            $modeloClientes->set_Ciudad($datosClientes['ciudad'] ?? '');
            $modeloClientes->set_Alergias($datosClientes['alergias'] ?? '');


            /* Registrar cliente */
            if ($accionClientes === 'registrar') {

                $resultadoClientes = $modeloClientes->registrar();

                $mensajeClientes =
                    'Cliente registrado correctamente.';

            } else {

                /* Modificar cliente */
                $resultadoClientes = $modeloClientes->modificar();

                $mensajeClientes =
                    'Cliente actualizado correctamente.';
            }

        } else {

            /* ================================================= */
            /* DESACTIVAR */
            /* ================================================= */

            /* Desactivar cliente y conservar su historial */
            $resultadoClientes = $modeloClientes->eliminar();

            $mensajeClientes =
                'Cliente desactivado correctamente.';
        }


        /* ================================================= */
        /* CONFIRMAR OPERACIÓN */
        /* ================================================= */

        /* Comprobar el resultado del modelo */
        if ($resultadoClientes !== true) {
            throw new RuntimeException(
                'El modelo no confirmó la operación de clientes.'
            );
        }

        /* Devolver el resultado de la operación AJAX */
        if ($esAjaxClientes) {
            $responderClientes(
                [
                    'ok' => true,
                    'mensaje' => $mensajeClientes
                ]
            );
        }

        /* Redirigir cuando se utiliza un formulario sin AJAX */
        header('Location: Index.php?url=clientes');
        exit;
    }


    /* Consultar los datos iniciales para cargar la vista */
    $listaClientes = $modeloClientes->consultar();

} catch (JsonException | InvalidArgumentException $e) {

    /* ================================================= */
    /* ERRORES DE LA SOLICITUD */
    /* ================================================= */

    $responderClientes(
        [
            'ok' => false,
            'mensaje' => $e instanceof JsonException
                ? 'Los datos enviados no contienen un JSON válido.'
                : $e->getMessage()
        ],
        422
    );

} catch (PDOException $e) {

    /* ================================================= */
    /* ERRORES DE BASE DE DATOS */
    /* ================================================= */

    /* Registrar el detalle técnico del error */
    error_log('Clientes: ' . $e->getMessage());

    /* Informar que la cédula ya está registrada */
    if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
        $responderClientes(
            [
                'ok' => false,
                'mensaje' =>
                    'La cédula ya está registrada. Revise el registro existente.',
                'errores' => [
                    'cedula' => 'Esta cédula ya existe en el sistema.'
                ]
            ],
            409
        );
    }

    $responderClientes(
        [
            'ok' => false,
            'mensaje' =>
                'No fue posible completar la operación de clientes. Intente nuevamente.'
        ],
        500
    );

} catch (Throwable $e) {

    /* ================================================= */
    /* OTROS ERRORES */
    /* ================================================= */

    /* Registrar el detalle técnico del error */
    error_log('Clientes: ' . $e->getMessage());

    $responderClientes(
        [
            'ok' => false,
            'mensaje' =>
                'No fue posible completar la operación de clientes. Intente nuevamente.'
        ],
        500
    );
}


/* ================================================= */
/* CARGAR VISTA */
/* ================================================= */

/* Mostrar la vista de Clientes */
require_once __DIR__ . '/../View/ClientesV.php';