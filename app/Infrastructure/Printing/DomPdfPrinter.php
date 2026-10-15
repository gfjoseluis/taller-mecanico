<?php

namespace App\Infrastructure\Printing;

use App\Domain\Contracts\PrinterInterface;
use App\Models\WorkOrder;
use Barryvdh\DomPDF\Facade\Pdf;

class DomPdfPrinter implements PrinterInterface
{
    public function comprobante(string $tipo, int $id, string $destino = 'pdf'): mixed
    {
        $ot = WorkOrder::with(['client', 'vehicle'])->findOrFail($id);
        $pdf = Pdf::loadView('print.ot', ['ot' => $ot]);
        return $destino === 'download' ? $pdf->download("OT-{$ot->numero}.pdf") : $pdf->stream("OT-{$ot->numero}.pdf");
    }
}
