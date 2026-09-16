<?php

declare(strict_types=1);

/**
 * Controlador de clientes B2B y su relacion con camisetas.
 */
final class ClienteController
{
    private const CATEGORIAS = ['Regular', 'Preferencial'];

    /**
     * GET /api/clientes
     * Lista todos los clientes.
     *
     * @param Request $request
     * @return void
     */
    public static function index(Request $request): void
    {
        $clientes = Cliente::all();

        Response::success($clientes, null, 200, ['total' => count($clientes)]);
    }

    /**
     * GET /api/clientes/{id}
     * Muestra un cliente.
     *
     * @param Request $request
     * @param string  $id
     * @return void
     */
    public static function show(Request $request, string $id): void
    {
        $cliente = Cliente::find((int) $id);

        if ($cliente === null) {
            Response::error(404, 'Cliente no encontrado.');
            return;
        }

        Response::success($cliente);
    }

    /**
     * POST /api/clientes
     * Crea un cliente validando campos obligatorios y RUT unico.
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

        if (Cliente::existsRut($data['rut'])) {
            Response::error(409, 'El RUT del cliente ya existe.');
            return;
        }

        $id = Cliente::create($data);

        Response::success(Cliente::find($id), 'Cliente creado correctamente.', 201);
    }

    /**
     * PUT /api/clientes/{id}
     * Actualiza un cliente existente.
     *
     * @param Request $request
     * @param string  $id
     * @return void
     */
    public static function update(Request $request, string $id): void
    {
        $clienteId = (int) $id;

        if (Cliente::find($clienteId) === null) {
            Response::error(404, 'Cliente no encontrado.');
            return;
        }

        $data      = self::payload($request);
        $validator = self::validate($data);

        if ($validator->fails()) {
            Response::error(400, 'Datos invalidos.', $validator->errors());
            return;
        }

        if (Cliente::existsRut($data['rut'], $clienteId)) {
            Response::error(409, 'El RUT del cliente ya existe.');
            return;
        }

        Cliente::update($clienteId, $data);

        Response::success(Cliente::find($clienteId), 'Cliente actualizado correctamente.');
    }

    /**
     * DELETE /api/clientes/{id}
     * Elimina un cliente solo si no tiene camisetas asociadas.
     *
     * @param Request $request
     * @param string  $id
     * @return void
     */
    public static function destroy(Request $request, string $id): void
    {
        $clienteId = (int) $id;

        if (Cliente::find($clienteId) === null) {
            Response::error(404, 'Cliente no encontrado.');
            return;
        }

        if (Cliente::tieneCamisetas($clienteId)) {
            Response::error(409, 'No se puede eliminar un cliente que tiene camisetas asociadas.');
            return;
        }

        Cliente::delete($clienteId);

        Response::success(null, 'Cliente eliminado correctamente.');
    }

    /**
     * GET /api/clientes/{id}/camisetas
     * Lista las camisetas del cliente con su precio_final calculado.
     *
     * @param Request $request
     * @param string  $id
     * @return void
     */
    public static function camisetas(Request $request, string $id): void
    {
        $cliente = Cliente::find((int) $id);

        if ($cliente === null) {
            Response::error(404, 'Cliente no encontrado.');
            return;
        }

        $camisetas = ClienteCamiseta::camisetasDe((int) $cliente['id']);
        $camisetas = PrecioService::decorarColeccion($camisetas, $cliente);

        Response::success($camisetas, null, 200, ['total' => count($camisetas)]);
    }

    /**
     * GET /api/clientes/{id}/camisetas/{camisetaId}/precio
     * Devuelve el precio final de una camiseta para un cliente.
     *
     * @param Request $request
     * @param string  $id
     * @param string  $camisetaId
     * @return void
     */
    public static function precio(Request $request, string $id, string $camisetaId): void
    {
        $cliente = Cliente::find((int) $id);

        if ($cliente === null) {
            Response::error(404, 'Cliente no encontrado.');
            return;
        }

        $camiseta = Camiseta::find((int) $camisetaId);

        if ($camiseta === null) {
            Response::error(404, 'Camiseta no encontrada.');
            return;
        }

        Response::success([
            'cliente_id'   => (int) $cliente['id'],
            'camiseta_id'  => (int) $camiseta['id'],
            'sku'          => $camiseta['sku'],
            'categoria'    => $cliente['categoria'],
            'precio'       => (int) $camiseta['precio'],
            'precio_oferta' => $camiseta['precio_oferta'] === null ? null : (int) $camiseta['precio_oferta'],
            'precio_final' => PrecioService::calcular($camiseta, $cliente),
        ]);
    }

    /**
     * POST /api/clientes/{id}/camisetas
     * Asocia una camiseta al cliente.
     *
     * @param Request $request
     * @param string  $id
     * @return void
     */
    public static function asociar(Request $request, string $id): void
    {
        $cliente = Cliente::find((int) $id);

        if ($cliente === null) {
            Response::error(404, 'Cliente no encontrado.');
            return;
        }

        $camisetaId = (int) $request->input('camiseta_id', 0);
        $cantidad   = (int) $request->input('cantidad', 1);

        if ($camisetaId <= 0) {
            Response::error(400, 'El campo camiseta_id es obligatorio.');
            return;
        }

        if ($cantidad <= 0) {
            Response::error(400, 'El campo cantidad debe ser mayor que cero.');
            return;
        }

        if (Camiseta::find($camisetaId) === null) {
            Response::error(404, 'Camiseta no encontrada.');
            return;
        }

        ClienteCamiseta::attach((int) $cliente['id'], $camisetaId, $cantidad);

        Response::success([
            'cliente_id'  => (int) $cliente['id'],
            'camiseta_id' => $camisetaId,
            'cantidad'    => $cantidad,
        ], 'Camiseta asociada al cliente.', 201);
    }

    /**
     * DELETE /api/clientes/{id}/camisetas/{camisetaId}
     * Quita la asociacion entre cliente y camiseta.
     *
     * @param Request $request
     * @param string  $id
     * @param string  $camisetaId
     * @return void
     */
    public static function desasociar(Request $request, string $id, string $camisetaId): void
    {
        $clienteId = (int) $id;

        if (Cliente::find($clienteId) === null) {
            Response::error(404, 'Cliente no encontrado.');
            return;
        }

        if (!ClienteCamiseta::exists($clienteId, (int) $camisetaId)) {
            Response::error(404, 'La camiseta no esta asociada a este cliente.');
            return;
        }

        ClienteCamiseta::detach($clienteId, (int) $camisetaId);

        Response::success(null, 'Camiseta desasociada del cliente.');
    }

    /**
     * Extrae y normaliza el cuerpo de la solicitud.
     *
     * @param Request $request
     * @return array
     */
    private static function payload(Request $request): array
    {
        $porcentaje = $request->input('porcentaje_oferta', 0);

        return [
            'nombre_comercial'  => trim((string) $request->input('nombre_comercial', '')),
            'rut'               => trim((string) $request->input('rut', '')),
            'ciudad'            => trim((string) $request->input('ciudad', '')),
            'region'            => trim((string) $request->input('region', '')),
            'categoria'         => (string) $request->input('categoria', ''),
            'contacto_nombre'   => trim((string) $request->input('contacto_nombre', '')),
            'contacto_email'    => trim((string) $request->input('contacto_email', '')),
            'porcentaje_oferta' => $porcentaje === null || $porcentaje === '' ? 0 : $porcentaje,
        ];
    }

    /**
     * Valida el payload de un cliente.
     *
     * @param array $data
     * @return Validator
     */
    private static function validate(array $data): Validator
    {
        return (new Validator($data))
            ->required('nombre_comercial', 'nombre_comercial')
            ->required('rut', 'rut')
            ->required('ciudad', 'ciudad')
            ->required('region', 'region')
            ->required('categoria', 'categoria')
            ->required('contacto_nombre', 'contacto_nombre')
            ->required('contacto_email', 'contacto_email')
            ->in('categoria', self::CATEGORIAS, 'categoria')
            ->email('contacto_email', 'contacto_email')
            ->decimal('porcentaje_oferta', 0, 100, 'porcentaje_oferta');
    }
}
