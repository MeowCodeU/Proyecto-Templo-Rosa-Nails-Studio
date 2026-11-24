<?php

use App\Model\ClientesM;


/* ===================================================== */
/* GESTIÓN DE CLIENTES */
/* ===================================================== */


/* ================================================= */
/* IDENTIFICAR LA SOLICITUD */
/* ================================================= */

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
/* PROCESAR LA SOLICITUD */
/* ================================================= */

try {

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

    /* Recibir los datos como JSON o mediante un formulario normal */
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

    $modeloClientes = new ClientesM();


    /* ================================================= */
    /* CONSULTAR CLIENTES */
    /* ================================================= */

    if ($metodoClientes === 'GET' && $esAjaxClientes) {

        $responderClientes(
            [
                'ok' => true,
                'clientes' => $modeloClientes->consultar($_GET['estado'] ?? 'ACTIVO')
            ]
        );
    }


    if ($metodoClientes === 'POST') {

        $accionClientes = $datosClientes['accion'] ?? '';

        /* Admitir únicamente las acciones disponibles en el módulo */
        if (
            !is_string($accionClientes) ||
            !in_array(
                $accionClientes,
                [
                    'consultar',
                    'buscar',
                    'consultar_persona',
                    'historial',
                    'registrar',
                    'modificar',
                    'desactivar',
                    'reactivar',
                    'eliminar'
                ],
                true
            )
        ) {
            throw new InvalidArgumentException(
                'La acción solicitada no es válida.'
            );
        }

        if ($accionClientes === 'consultar') {

            $responderClientes(
                [
                    'ok' => true,
                    'clientes' => $modeloClientes->consultar($datosClientes['estado'] ?? 'ACTIVO')
                ]
            );
        }


        /* ================================================= */
        /* IDENTIFICAR AL CLIENTE */
        /* ================================================= */

        $modeloClientes->set_Cedula($datosClientes['cedula'] ?? '');

        /* Solicitar al modelo la validación de la cédula antes de continuar */
        if (!$modeloClientes->validarIdentificacion()) {
            $erroresClientes = $modeloClientes->obtenerErroresValidacion();

            $responderClientes(
                [
                    'ok' => false,
                    'mensaje' => ($erroresClientes['cedula'] ?? '') === 'La cédula es obligatoria.'
                        ? 'Indique la cédula del cliente.'
                        : 'Revise los campos señalados.',
                    'errores' => $erroresClientes
                ],
                422
            );
        }


        /* ================================================= */
        /* CONSULTAR PERSONA POR CÉDULA */
        /* ================================================= */

        if ($accionClientes === 'consultar_persona') {

            if (!$esAjaxClientes) {
                $responderClientes(
                    [
                        'ok' => false,
                        'mensaje' => 'La solicitud enviada no es válida.'
                    ],
                    400
                );
            }

            $personaClientes = $modeloClientes->consultarDatosPersona(
                trim($datosClientes['cedula'])
            );

            $clienteExistente = $personaClientes
                ? $modeloClientes->buscar(true)
                : false;

            $responderClientes(
                [
                    'ok' => true,
                    'encontrada' => $personaClientes !== null,
                    'ya_es_cliente' => $clienteExistente !== false,
                    'estado_cliente' => $clienteExistente['estado'] ?? null,
                    'persona' => $personaClientes
                ]
            );
        }


        /* Permitir la consulta y reactivación de una ficha inactiva */
        if ($accionClientes !== 'registrar') {

            $clienteActual = $modeloClientes->buscar(
                in_array($accionClientes, ['buscar', 'historial', 'reactivar'], true)
            );

            if (!$clienteActual) {
                $responderClientes(
                    [
                        'ok' => false,
                        'mensaje' =>
                            'El cliente no está disponible para esta operación. Actualice la lista.'
                    ],
                    404
                );
            }


            /* ================================================= */
            /* CONSULTAR EL HISTORIAL DE SERVICIOS */
            /* ================================================= */

            if ($accionClientes === 'historial') {

                /* Obtener el identificador desde el cliente encontrado */
                $historialClientes = $modeloClientes->consultarHistorial(
                    (int) $clienteActual['id_cliente']
                );

                $responderClientes(
                    [
                        'ok' => true,
                        'cliente' => $clienteActual,
                        'historial' => $historialClientes
                    ]
                );
            }


            /* Devolver los datos actualizados del cliente solicitado */
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

            /* Entregar los datos al modelo, que los valida antes de guardar */
            $modeloClientes->set_Nombre($datosClientes['nombre'] ?? '');
            $modeloClientes->set_Apellido($datosClientes['apellido'] ?? '');
            $modeloClientes->set_Telefono($datosClientes['telefono'] ?? '');
            $modeloClientes->set_Correo($datosClientes['correo'] ?? '');
            $modeloClientes->set_Direccion($datosClientes['direccion'] ?? '');
            $modeloClientes->set_Ciudad($datosClientes['ciudad'] ?? '');
            $modeloClientes->set_Alergias($datosClientes['alergias'] ?? '');


            if ($accionClientes === 'registrar') {

                $resultadoClientes = $modeloClientes->registrar();

                $mensajeClientes =
                    'Cliente registrado correctamente.';

            } else {

                $resultadoClientes = $modeloClientes->modificar();

                $mensajeClientes =
                    'Cliente actualizado correctamente.';
            }

        } elseif ($accionClientes === 'reactivar') {

            /* Recuperar la misma ficha sin registrar nuevamente al cliente */
            $resultadoClientes = $modeloClientes->reactivar();

            $mensajeClientes =
                'Cliente reactivado correctamente.';

        } else {

            /* ================================================= */
            /* DESACTIVAR CLIENTE */
            /* ================================================= */

            /* Conservar el registro y su historial mediante la baja lógica */
            $resultadoClientes = $modeloClientes->eliminar();

            $mensajeClientes =
                'Cliente desactivado correctamente.';
        }


        /* ================================================= */
        /* CONFIRMAR LA OPERACIÓN */
        /* ================================================= */

        $erroresClientes = $modeloClientes->obtenerErroresValidacion();

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

        if ($resultadoClientes !== true) {
            throw new RuntimeException(
                'El modelo no confirmó la operación de clientes.'
            );
        }

        if ($esAjaxClientes) {
            $responderClientes(
                [
                    'ok' => true,
                    'mensaje' => $mensajeClientes
                ]
            );
        }

        /* Mantener la navegación de los formularios enviados sin AJAX */
        header('Location: Index.php?url=clientes');

        exit;
    }


    /* Obtener los registros iniciales para cargar la vista */
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

} catch (DomainException $e) {

    /* Informar cambios de estado realizados desde otra solicitud */
    $responderClientes(
        [
            'ok' => false,
            'mensaje' => $e->getMessage()
        ],
        409
    );

} catch (PDOException $e) {

    /* ================================================= */
    /* ERRORES DE BASE DE DATOS */
    /* ================================================= */

    /* Registrar el detalle técnico únicamente en el servidor */
    error_log('Clientes: ' . $e->getMessage());

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
/* CARGAR LA VISTA */
/* ================================================= */

require_once __DIR__ . '/../View/ClientesV.php';