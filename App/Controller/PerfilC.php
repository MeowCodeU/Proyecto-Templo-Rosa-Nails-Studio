<?php

use App\Controller\Components\Session;
use App\Model\PerfilM;


/*
 * Redirige al inicio mostrando el resultado
 * correspondiente al cambio de contraseña.
 */
$redirigirCambioClave =
    static function (
        string $resultado
    ): void {

        header(
            'Location: Index.php?url=inicio&clave=' .
            urlencode($resultado)
        );

        exit;
    };


/*
 * La operación solamente puede ejecutarse
 * cuando existe un usuario autenticado.
 */
if (!Session::estaAutenticada()) {

    header(
        'Location: Index.php?url=login'
    );

    exit;
}


/*
 * Aceptar únicamente solicitudes enviadas
 * mediante el formulario de cambio de clave.
 */
if (
    $_SERVER['REQUEST_METHOD'] !== 'POST' ||
    ($_POST['accion'] ?? '') !== 'cambiar_clave'
) {
    $redirigirCambioClave(
        'solicitud_invalida'
    );
}


/*
 * Obtener el usuario directamente desde
 * la sesión activa.
 *
 * El identificador no se recibe mediante
 * el formulario para impedir que se intente
 * modificar la cuenta de otro usuario.
 */
$usuarioActual =
    Session::obtenerUsuario();


$idUsuario =
    (int) (
        $usuarioActual['id_usuario']
        ?? 0
    );


if ($idUsuario <= 0) {
    $redirigirCambioClave(
        'usuario_no_disponible'
    );
}


/*
 * Comprobar el token de seguridad generado
 * para el formulario.
 */
$tokenFormulario =
    (string) (
        $_POST['token_csrf']
        ?? ''
    );


$tokenSesion =
    (string) (
        $_SESSION['token_cambiar_clave']
        ?? ''
    );


if (
    $tokenFormulario === '' ||
    $tokenSesion === '' ||
    !hash_equals(
        $tokenSesion,
        $tokenFormulario
    )
) {
    $redirigirCambioClave(
        'solicitud_invalida'
    );
}


/*
 * Recibir las contraseñas.
 *
 * No se aplica trim(), debido a que los
 * espacios pueden formar parte de la clave.
 */
$claveActual =
    (string) (
        $_POST['clave_actual']
        ?? ''
    );


$nuevaClave =
    (string) (
        $_POST['nueva_clave']
        ?? ''
    );


$confirmarClave =
    (string) (
        $_POST['confirmar_clave']
        ?? ''
    );


/*
 * Comprobar que todos los campos
 * hayan sido completados.
 */
if (
    $claveActual === '' ||
    $nuevaClave === '' ||
    $confirmarClave === ''
) {
    $redirigirCambioClave(
        'datos_incompletos'
    );
}


/*
 * Exigir una contraseña nueva
 * de ocho caracteres como mínimo.
 */
if (strlen($nuevaClave) < 8) {
    $redirigirCambioClave(
        'clave_corta'
    );
}


/*
 * Comprobar que la nueva contraseña
 * y su confirmación coincidan.
 */
if ($nuevaClave !== $confirmarClave) {
    $redirigirCambioClave(
        'no_coinciden'
    );
}


try {

    $modeloPerfil =
        new PerfilM();


    /*
     * Recuperar la clave cifrada almacenada
     * para la cuenta autenticada.
     */
    $claveCifradaActual =
        $modeloPerfil->obtenerClaveActual(
            $idUsuario
        );


    if ($claveCifradaActual === null) {
        $redirigirCambioClave(
            'usuario_no_disponible'
        );
    }


    /*
     * Verificar que la contraseña actual
     * suministrada sea correcta.
     */
    if (
        !password_verify(
            $claveActual,
            $claveCifradaActual
        )
    ) {
        $redirigirCambioClave(
            'actual_incorrecta'
        );
    }


    /*
     * Evitar guardar nuevamente
     * la misma contraseña.
     */
    if (
        password_verify(
            $nuevaClave,
            $claveCifradaActual
        )
    ) {
        $redirigirCambioClave(
            'misma_clave'
        );
    }


    /*
     * Cifrar y actualizar la nueva clave
     * mediante el modelo.
     */
    $claveActualizada =
        $modeloPerfil->cambiarClave(
            $idUsuario,
            $nuevaClave
        );


    if (!$claveActualizada) {
        $redirigirCambioClave(
            'error'
        );
    }


    /*
     * El token utilizado deja de ser válido
     * después de completar la operación.
     */
    unset(
        $_SESSION['token_cambiar_clave']
    );


    /*
     * Regenerar el identificador de sesión
     * después de modificar una credencial.
     */
    session_regenerate_id(
        true
    );


    $redirigirCambioClave(
        'actualizada'
    );

} catch (\Throwable $error) {

    error_log(
        $error->getMessage()
    );


    $redirigirCambioClave(
        'error'
    );
}