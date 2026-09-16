<?php

declare(strict_types=1);

/**
 * Controlador del catalogo de tallas.
 */
final class TallaController
{
    /**
     * GET /api/tallas
     * Lista todas las tallas.
     *
     * @param Request $request
     * @return void
     */
    public static function index(Request $request): void
    {
        $tallas = Talla::all();

        Response::success($tallas, null, 200, ['total' => count($tallas)]);
    }

    /**
     * GET /api/tallas/{id}
     * Muestra una talla.
     *
     * @param Request $request
     * @param string  $id
     * @return void
     */
    public static function show(Request $request, string $id): void
    {
        $talla = Talla::find((int) $id);

        if ($talla === null) {
            Response::error(404, 'Talla no encontrada.');
            return;
        }

        Response::success($talla);
    }

    /**
     * POST /api/tallas
     * Crea una talla validando el nombre unico.
     *
     * @param Request $request
     * @return void
     */
    public static function store(Request $request): void
    {
        $nombre = trim((string) $request->input('nombre', ''));

        $validator = (new Validator(['nombre' => $nombre]))->required('nombre', 'nombre');

        if ($validator->fails()) {
            Response::error(400, 'Datos invalidos.', $validator->errors());
            return;
        }

        if (Talla::existsNombre($nombre)) {
            Response::error(409, 'La talla ya existe.');
            return;
        }

        $id = Talla::create($nombre);

        Response::success(Talla::find($id), 'Talla creada correctamente.', 201);
    }

    /**
     * DELETE /api/tallas/{id}
     * Elimina una talla.
     *
     * @param Request $request
     * @param string  $id
     * @return void
     */
    public static function destroy(Request $request, string $id): void
    {
        $tallaId = (int) $id;

        if (Talla::find($tallaId) === null) {
            Response::error(404, 'Talla no encontrada.');
            return;
        }

        Talla::delete($tallaId);

        Response::success(null, 'Talla eliminada correctamente.');
    }
}
