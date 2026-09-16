<?php

declare(strict_types=1);

/**
 * Controlador de camisetas: CRUD del stock.
 */
final class CamisetaController
{
    private const TIPOS = ['Local', 'Visita', 'Femenino'];

    /**
     * GET /api/camisetas
     * Lista todas las camisetas con sus tallas.
     *
     * @param Request $request
     * @return void
     */
    public static function index(Request $request): void
    {
        $tallasPorCamiseta = CamisetaTalla::tallasPorCamiseta();

        $camisetas = array_map(static function (array $camiseta) use ($tallasPorCamiseta): array {
            $camiseta['tallas'] = $tallasPorCamiseta[(int) $camiseta['id']] ?? [];

            return $camiseta;
        }, Camiseta::all());

        Response::success($camisetas, null, 200, ['total' => count($camisetas)]);
    }

    /**
     * GET /api/camisetas/{id}
     * Muestra una camiseta con sus tallas.
     *
     * @param Request $request
     * @param string  $id
     * @return void
     */
    public static function show(Request $request, string $id): void
    {
        $camiseta = Camiseta::find((int) $id);

        if ($camiseta === null) {
            Response::error(404, 'Camiseta no encontrada.');
            return;
        }

        $camiseta['tallas'] = CamisetaTalla::tallasDe((int) $camiseta['id']);

        Response::success($camiseta);
    }

    /**
     * GET /api/camisetas/sku/{sku}
     * Muestra una camiseta por su SKU (codigo de producto unico).
     *
     * @param Request $request
     * @param string  $sku
     * @return void
     */
    public static function showBySku(Request $request, string $sku): void
    {
        $camiseta = Camiseta::findBySku(urldecode($sku));

        if ($camiseta === null) {
            Response::error(404, 'Camiseta no encontrada.');
            return;
        }

        $camiseta['tallas'] = CamisetaTalla::tallasDe((int) $camiseta['id']);

        Response::success($camiseta);
    }

    /**
     * POST /api/camisetas
     * Crea una camiseta validando campos obligatorios, SKU unico y tallas.
     *
     * @param Request $request
     * @return void
     */
    public static function store(Request $request): void
    {
        $data      = self::payload($request);
        $validator = self::validate($data);

        if ($validator->fails()) {
            Response::error(400, 'Datos invalidos.', $validator->errors());
            return;
        }

        if (Camiseta::existsSku($data['sku'])) {
            Response::error(409, 'El SKU ya existe.');
            return;
        }

        if (!Talla::existenTodas($data['tallas'])) {
            Response::error(400, 'Una o mas tallas no existen.');
            return;
        }

        $id = Database::transaction(static function () use ($data): int {
            $id = Camiseta::create($data);
            CamisetaTalla::sync($id, $data['tallas']);

            return $id;
        });

        $camiseta           = Camiseta::find($id);
        $camiseta['tallas'] = CamisetaTalla::tallasDe($id);

        Response::success($camiseta, 'Camiseta creada correctamente.', 201);
    }

    /**
     * PUT /api/camisetas/{id}
     * Actualiza una camiseta existente.
     *
     * @param Request $request
     * @param string  $id
     * @return void
     */
    public static function update(Request $request, string $id): void
    {
        $camisetaId = (int) $id;

        if (Camiseta::find($camisetaId) === null) {
            Response::error(404, 'Camiseta no encontrada.');
            return;
        }

        $data      = self::payload($request);
        $validator = self::validate($data);

        if ($validator->fails()) {
            Response::error(400, 'Datos invalidos.', $validator->errors());
            return;
        }

        if (Camiseta::existsSku($data['sku'], $camisetaId)) {
            Response::error(409, 'El SKU ya existe.');
            return;
        }

        if (!Talla::existenTodas($data['tallas'])) {
            Response::error(400, 'Una o mas tallas no existen.');
            return;
        }

        Database::transaction(static function () use ($camisetaId, $data): void {
            Camiseta::update($camisetaId, $data);
            CamisetaTalla::sync($camisetaId, $data['tallas']);
        });

        $camiseta           = Camiseta::find($camisetaId);
        $camiseta['tallas'] = CamisetaTalla::tallasDe($camisetaId);

        Response::success($camiseta, 'Camiseta actualizada correctamente.');
    }

    /**
     * DELETE /api/camisetas/{id}
     * Elimina una camiseta si no esta asociada a clientes.
     *
     * @param Request $request
     * @param string  $id
     * @return void
     */
    public static function destroy(Request $request, string $id): void
    {
        $camisetaId = (int) $id;

        if (Camiseta::find($camisetaId) === null) {
            Response::error(404, 'Camiseta no encontrada.');
            return;
        }

        if (Camiseta::tieneClientes($camisetaId)) {
            Response::error(409, 'No se puede eliminar una camiseta asociada a clientes.');
            return;
        }

        Camiseta::delete($camisetaId);

        Response::success(null, 'Camiseta eliminada correctamente.');
    }

    /**
     * Extrae y normaliza el cuerpo de la solicitud.
     *
     * @param Request $request
     * @return array
     */
    private static function payload(Request $request): array
    {
        return [
            'titulo'        => trim((string) $request->input('titulo', '')),
            'club'          => trim((string) $request->input('club', '')),
            'pais'          => trim((string) $request->input('pais', '')),
            'tipo'          => (string) $request->input('tipo', ''),
            'color'         => trim((string) $request->input('color', '')),
            'precio'        => $request->input('precio'),
            'detalles'      => $request->input('detalles'),
            'sku'           => trim((string) $request->input('sku', '')),
            'tallas'        => $request->input('tallas', []),
        ];
    }

    /**
     * Valida el payload de una camiseta.
     *
     * @param array $data
     * @return Validator
     */
    private static function validate(array $data): Validator
    {
        $validator = (new Validator($data))
            ->required('titulo', 'titulo')
            ->required('club', 'club')
            ->required('pais', 'pais')
            ->required('tipo', 'tipo')
            ->required('color', 'color')
            ->required('sku', 'sku')
            ->in('tipo', self::TIPOS, 'tipo')
            ->integer('precio', 1, 'precio')
            ->arrayOfIds('tallas', 'tallas');

        if (!array_key_exists('precio', $data) || $data['precio'] === null || $data['precio'] === '') {
            $validator->add('precio', 'El campo precio es obligatorio.');
        }

        return $validator;
    }
}
