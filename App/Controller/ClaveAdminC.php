<?php

$mensajeClaveAdmin = '';
$tipoMensajeClaveAdmin = 'error';

$hashClaveUniversal = '$2y$12$yhjAIohVHg.buqNIDwmi0.ADc//0D6x4xF5bqphiK/U9qaFogkVi6';

$duracionAutorizacion = 600;

if (
    isset($_SESSION['registro_usuario_autorizado_hasta']) &&
    $_SESSION['registro_usuario_autorizado_hasta'] < time()
) {
    unset($_SESSION['registro_usuario_autorizado_hasta']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $accion = $_POST['accion'] ?? '';

    if ($accion === 'validar_clave_universal') {

        $claveUniversal = trim(
            (string) ($_POST['clave_universal'] ?? '')
        );

        if ($claveUniversal === '') {

            $mensajeClaveAdmin = 'Debe ingresar la clave universal.';

        } elseif (
            password_verify(
                $claveUniversal,
                $hashClaveUniversal
            )
        ) {

            session_regenerate_id(true);

            $_SESSION['registro_usuario_autorizado_hasta'] =
                time() + $duracionAutorizacion;

            header(
                'Location: Index.php?url=registroUsuario'
            );

            exit;

        } else {

            unset(
                $_SESSION['registro_usuario_autorizado_hasta']
            );

            $mensajeClaveAdmin =
                'La clave universal ingresada no es válida.';
        }
    }
}

require_once __DIR__ . '/../View/ClaveAdminV.php';