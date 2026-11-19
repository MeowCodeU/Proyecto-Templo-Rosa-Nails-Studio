<?php

namespace App\Traits;

/* ===================================================== */
/* VALIDACIONES DE CONFIGURACIÓN */
/* ===================================================== */

trait ValidacionConfiguracionTrait
{
    use ValidacionTrait;

    /* ================================================= */
    /* VALIDAR LOS DATOS DE UN CATÁLOGO */
    /* ================================================= */

    protected function validarDatosConfiguracion(
        array $datos,
        string $campoNombre,
        int $maximoNombre,
        int $maximoDescripcion,
        ?string $campoId = null
    ): array {
        $this->erroresValidacion = [];

        $datos[$campoNombre] = $this->textoConfiguracion(
            $datos[$campoNombre] ?? '', $campoNombre, $maximoNombre, true
        );
        $datos['descripcion'] = $this->textoConfiguracion(
            $datos['descripcion'] ?? '', 'descripcion', $maximoDescripcion
        );

        if ($campoId !== null) {
            $datos[$campoId] = $this->validarIdentificador(
                $datos[$campoId] ?? null, $campoId, 'Seleccione un registro válido.'
            );
        }

        if ($campoNombre === 'nombre_servicio') {
            $datos['precio'] = $this->precioConfiguracion($datos['precio'] ?? null);
            $datos['duracion_estimada'] = $this->duracionConfiguracion($datos['duracion_estimada'] ?? null);
        }

        return $datos;
    }

    /* ================================================= */
    /* VALIDAR EL IDENTIFICADOR DE UN CATÁLOGO */
    /* ================================================= */

    protected function validarIdConfiguracion(mixed $entrada, string $campo): ?int
    {
        $this->erroresValidacion = [];

        return $this->validarIdentificador($entrada, $campo, 'Seleccione un registro válido.');
    }

    /* ================================================= */
    /* VALIDAR NOMBRES Y DESCRIPCIONES */
    /* ================================================= */

    private function textoConfiguracion(mixed $entrada, string $campo, int $maximo, bool $obligatorio = false): string
    {
        if (!is_string($entrada) || preg_match('//u', $entrada) !== 1) {
            $this->erroresValidacion[$campo] = 'Ingrese un texto válido.';
            return '';
        }

        $valor = trim($entrada);

        if ($obligatorio && $valor === '') {
            $this->erroresValidacion[$campo] = 'El nombre es obligatorio.';
        } elseif ($this->longitud($valor) > $maximo) {
            $this->erroresValidacion[$campo] = "El texto no puede superar $maximo caracteres.";
        }

        return $valor;
    }

    /* ================================================= */
    /* VALIDAR EL PRECIO DEL SERVICIO */
    /* ================================================= */

    private function precioConfiguracion(mixed $entrada): ?string
    {
        $mensaje = 'Ingrese un precio entre 0 y 99999999.99, con un máximo de dos decimales.';

        if ((!is_string($entrada) && !is_int($entrada) && !is_float($entrada)) ||
            (is_float($entrada) && !is_finite($entrada))) {
            $this->erroresValidacion['precio'] = $mensaje;
            return null;
        }

        $valor = trim((string) $entrada);

        if ($valor === '' || preg_match('/\A([0-9]*)(?:\.([0-9]{1,2}))?\z/', $valor, $partes) !== 1) {
            $this->erroresValidacion['precio'] = $mensaje;
            return null;
        }

        $entero = ltrim($partes[1], '0');
        $entero = $entero === '' ? '0' : $entero;

        if (strlen($entero) > 8) {
            $this->erroresValidacion['precio'] = $mensaje;
            return null;
        }

        return $entero . '.' . str_pad($partes[2] ?? '', 2, '0');
    }

    /* ================================================= */
    /* VALIDAR LA DURACIÓN OPCIONAL DEL SERVICIO */
    /* ================================================= */

    private function duracionConfiguracion(mixed $entrada): ?int
    {
        if ($entrada === null || $entrada === '') return null;

        if ((!is_string($entrada) && !is_int($entrada)) ||
            preg_match('/\A[0-9]+\z/', (string) $entrada) !== 1 ||
            (float) $entrada < 1 || (float) $entrada > 65535) {
            $this->erroresValidacion['duracion_estimada'] = 'Ingrese una duración entera entre 1 y 65535 minutos, o deje el campo vacío.';
            return null;
        }

        return (int) $entrada;
    }
}