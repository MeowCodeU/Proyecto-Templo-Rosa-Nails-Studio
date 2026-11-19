<?php

namespace App\Traits;

/* ===================================================== */
/* VALIDACIONES COMPARTIDAS DEL SERVIDOR */
/* ===================================================== */

trait ValidacionTrait
{
    /* Errores de la última validación realizada por el modelo */
    private array $erroresValidacion = [];


    /* ================================================= */
    /* CONSULTAR ERRORES DE VALIDACIÓN */
    /* ================================================= */

    public function obtenerErroresValidacion(): array
    {
        return $this->erroresValidacion;
    }


    /* ================================================= */
    /* VALIDAR UN CONJUNTO DE CAMPOS */
    /* ================================================= */

    protected function validarCampos(array $datos, array $obligatorios = []): bool
    {
        $this->erroresValidacion = [];

        foreach (array_unique(array_merge(array_keys($datos), $obligatorios)) as $campo) {
            $mensaje = $this->validarCampo($campo, $datos[$campo] ?? '', in_array($campo, $obligatorios, true));

            if ($mensaje !== null) $this->erroresValidacion[$campo] = $mensaje;
        }

        return $this->erroresValidacion === [];
    }


    /* ================================================= */
    /* VALIDAR UN CAMPO */
    /* ================================================= */

    protected function validarCampo(string $campo, mixed $entrada, bool $requerido = false): ?string
    {
        $etiquetas = [
            'cedula' => 'La cédula',
            'nombre' => 'El nombre',
            'apellido' => 'El apellido',
            'telefono' => 'El teléfono',
            'correo' => 'El correo',
            'ciudad' => 'La ciudad',
            'direccion' => 'La dirección',
            'alergias' => 'Las observaciones',
            'rif' => 'El RIF',
            'nombre_empresa' => 'El nombre de la empresa'
        ];

        $limites = [
            'cedula' => [6, 20],
            'nombre' => [2, 60],
            'apellido' => [2, 60],
            'telefono' => [7, 25],
            'correo' => [0, 100],
            'ciudad' => [2, 80],
            'direccion' => [0, 150],
            'alergias' => [0, 2000],
            'rif' => [1, 25],
            'nombre_empresa' => [1, 100]
        ];

        $patrones = [
            'cedula' => '/\A[0-9]{6,20}\z/',
            'nombre' => '/\A[\p{L}\p{M}][\p{L}\p{M} .\'’-]*\z/u',
            'apellido' => '/\A[\p{L}\p{M}][\p{L}\p{M} .\'’-]*\z/u',
            'ciudad' => '/\A[\p{L}\p{M}][\p{L}\p{M} .\'’-]*\z/u',
            'telefono' => '/\A[+0-9() -]+\z/',
            'correo' => '/\A[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}\z/'
        ];

        if (!isset($etiquetas[$campo])) return null;
        if (!is_string($entrada)) return 'El valor del campo no es válido.';

        $valor = trim($entrada);
        $etiqueta = $etiquetas[$campo];

        if ($valor === '') {
            if (!$requerido) return null;

            return $etiqueta . (str_starts_with($etiqueta, 'El ') ? ' es obligatorio.' : ' es obligatoria.');
        }

        [$minimo, $maximo] = $limites[$campo];
        $longitud = $this->longitud($valor);

        if ($longitud < $minimo || $longitud > $maximo) {
            return "$etiqueta debe tener entre $minimo y $maximo caracteres.";
        }

        if (isset($patrones[$campo]) && preg_match($patrones[$campo], $valor) !== 1) {
            return match ($campo) {
                'cedula' => 'La cédula debe contener solo números (6 a 20 dígitos).',
                'telefono' => 'El teléfono debe contener solo números, espacios, +, - o paréntesis.',
                'correo' => 'Ingrese un correo electrónico válido.',
                default => "$etiqueta contiene caracteres no permitidos."
            };
        }

        if ($campo === 'telefono' && preg_match_all('/[0-9]/', $valor) < 7) {
            return 'El teléfono debe incluir al menos 7 dígitos.';
        }

        return null;
    }


    /* ================================================= */
    /* VALIDAR IDENTIFICADORES */
    /* ================================================= */

    protected function validarIdentificador(mixed $entrada, string $campo, string $mensaje): ?int
    {
        $identificador = false;

        if (is_string($entrada) || is_int($entrada)) {
            $identificador = filter_var($entrada, FILTER_VALIDATE_INT, [
                'options' => ['min_range' => 1, 'max_range' => 4294967295]
            ]);
        }

        if ($identificador === false) {
            $this->erroresValidacion[$campo] = $mensaje;
            return null;
        }

        return $identificador;
    }


    /* ================================================= */
    /* VALIDAR SELECCIÓN DE INSUMOS */
    /* ================================================= */

    protected function validarSeleccionInsumos(mixed $idsInsumos): array
    {
        $mensaje = null;
        $seleccion = [];

        if (!is_array($idsInsumos) || array_values($idsInsumos) !== $idsInsumos || count($idsInsumos) > 1000) {
            $mensaje = 'La selección de insumos no es válida.';

        } else {
            foreach ($idsInsumos as $valor) {
                if (!is_string($valor) && !is_int($valor)) {
                    $mensaje = 'La selección contiene un identificador inválido.';
                    break;
                }

                $idInsumo = filter_var($valor, FILTER_VALIDATE_INT, [
                    'options' => ['min_range' => 1, 'max_range' => 4294967295]
                ]);

                if ($idInsumo === false || isset($seleccion[$idInsumo])) {
                    $mensaje = 'La selección de insumos contiene datos inválidos o repetidos.';
                    break;
                }

                $seleccion[$idInsumo] = $idInsumo;
            }
        }

        if ($mensaje !== null) {
            $this->erroresValidacion['id_insumos'] = $mensaje;
            throw new \InvalidArgumentException($mensaje);
        }

        return $seleccion;
    }


    /* ================================================= */
    /* VALIDAR CONTRASEÑAS NUEVAS */
    /* ================================================= */

    protected function validarClave(string $clave): bool
    {
        return preg_match('/\A(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9\s])\S{8,}\z/', $clave) === 1;
    }


    /* ================================================= */
    /* CONTAR CARACTERES UNICODE */
    /* ================================================= */

    private function longitud(string $valor): int
    {
        if (function_exists('mb_strlen')) return mb_strlen($valor, 'UTF-8');

        $cantidad = preg_match_all('/./us', $valor);

        return $cantidad === false ? strlen($valor) : $cantidad;
    }
}