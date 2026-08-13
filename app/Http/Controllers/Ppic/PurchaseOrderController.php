<?php

namespace App\Http\Controllers\Ppic;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Support\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurchaseOrderController extends Controller
{
    public function index(): View
    {
        return view('ppic.purchase-order');
    }

    public function data(Request $request): JsonResponse
    {
        $query = PurchaseOrder::with('user')->orderByDesc('tanggal');

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('nomor_po', 'like', "%{$search}%")
                    ->orWhere('jenis_po', 'like', "%{$search}%");
            });
        }

        $orders = $query->get()->map(fn (PurchaseOrder $po) => [
            'id'         => $po->id,
            'jenisPo'    => $po->jenis_po,
            'nomorPo'    => $po->nomor_po,
            'tanggal'    => $po->tanggal->format('Y-m-d'),
            'tanggalLabel' => $po->tanggal->format('d/m/Y'),
            'namaUser'   => $po->user->name ?? '-',
        ]);

        return response()->json($orders);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'jenis_po' => ['required', 'string', 'max:100'],
            'nomor_po' => ['required', 'string', 'max:100', 'unique:purchase_orders,nomor_po'],
            'tanggal'  => ['required', 'date'],
        ]);

        $user = $request->user('tally');

        PurchaseOrder::create([
            'jenis_po' => $data['jenis_po'],
            'nomor_po' => $data['nomor_po'],
            'tanggal'  => $data['tanggal'],
            'user_id'  => $user->id,
        ]);

        ActivityLogger::log(
            'ppic',
            'create',
            "{$user->employee_code} ({$user->name}) menambah PO baru: {$data['nomor_po']} ({$data['jenis_po']})",
            $user
        );

        return response()->json(['success' => true, 'message' => 'PO berhasil disimpan.']);
    }

    public function destroy(Request $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        $user = $request->user('tally');
        $nomorPo = $purchaseOrder->nomor_po;

        $purchaseOrder->delete();

        ActivityLogger::log(
            'ppic',
            'delete',
            "{$user->employee_code} ({$user->name}) menghapus PO: {$nomorPo}",
            $user
        );

        return response()->json(['success' => true]);
    }
}
