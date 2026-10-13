<?php

namespace App\Adapters\Inbound\Http\Controllers;

use App\Application\Errors\ApplicationError;
use App\Application\Ports\Outbound\IdGenerator;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** HU-05: socios (api-contract.md §3.5). Los socios no se eliminan (CA-05.3). */
final class MembersController
{
    private const MESSAGES = [
        'required' => 'Es obligatorio.',
        'string' => 'Debe ser texto.',
        'max' => 'Supera la longitud máxima.',
        'integer' => 'Debe ser un número entero.',
        'min' => 'Debe ser al menos :min.',
    ];

    /** GET /members?q=&page=&size= — busca por nombre o documento, paginado (CA-05.2). */
    public function index(Request $request): JsonResponse
    {
        $data = $request->validate([
            'q' => ['nullable', 'string', 'max:200'],
            'page' => ['nullable', 'integer', 'min:1'],
            'size' => ['nullable', 'integer', 'min:1', 'max:50'],
        ], self::MESSAGES);

        $page = (int) ($data['page'] ?? 1);
        $size = (int) ($data['size'] ?? 20);
        $query = DB::table('library.member');

        if (! empty($data['q'])) {
            $term = '%'.$data['q'].'%';
            $query->where(fn ($w) => $w->where('full_name', 'ilike', $term)->orWhere('document_number', 'ilike', $term));
        }

        $total = (clone $query)->count();
        $items = $query->orderBy('full_name')->offset(($page - 1) * $size)->limit($size)
            ->get(['id', 'full_name', 'document_number']);

        return response()->json(['items' => $items, 'page' => $page, 'size' => $size, 'total' => $total]);
    }

    /** GET /members/{id} — detalle con active_loans (líneas sin devolver). */
    public function show(string $id): JsonResponse
    {
        $member = preg_match('/^[0-9a-f-]{36}$/i', $id)
            ? DB::table('library.member')->where('id', $id)->first(['id', 'full_name', 'document_number'])
            : null;

        if ($member === null) {
            throw new ApplicationError('NOT_FOUND', 404, 'El socio no existe.');
        }

        $member->active_loans = DB::table('library.loan_item')
            ->join('library.loan', 'loan.id', '=', 'loan_item.loan_id')
            ->where('loan.member_id', $id)
            ->whereNull('loan_item.returned_at')
            ->count();

        return response()->json($member);
    }

    /** POST /members — crea con full_name y document_number; documento repetido: 409 (CA-05.1). */
    public function store(Request $request, IdGenerator $ids): JsonResponse
    {
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:200'],
            'document_number' => ['required', 'string', 'max:30'],
        ], self::MESSAGES);

        $member = [
            'id' => $ids->next(),
            'full_name' => trim($data['full_name']),
            'document_number' => trim($data['document_number']),
        ];

        try {
            DB::table('library.member')->insert($member);
        } catch (QueryException $e) {
            if ($e->getCode() === '23505') {
                throw new ApplicationError('DOCUMENT_TAKEN', 409, 'Ya existe un socio con ese documento.');
            }
            throw $e;
        }

        return response()->json($member, 201);
    }
}
