<?php

namespace App\Exports;

use App\Models\Ledger;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;


class LedgerExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */
    protected $filters;
    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
         if($this->filters['report'] == 'today'){
            $ledgers = Ledger::whereDate('created_at', now()->toDateString());
        }elseif($this->filters['report'] == 'last7'){
             $ledgers = Ledger::whereBetween('created_at', [now()->subDays(6)->startOfDay(), now()->endOfDay()]);
        }elseif($this->filters['report'] == 'thisMonth'){
            $ledgers = Ledger::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year);
        }elseif($this->filters['report'] == 'lastMonth'){
            $startOfLastMonth = now()->subMonth()->startOfMonth();
            $endOfLastMonth = now()->subMonth()->endOfMonth();
            $ledgers = Ledger::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth]);
        }elseif($this->filters['report'] == 'custom'){
            $start = $this->filters['start'];
            $end = $this->filters['end'];
            $ledgers = Ledger::whereBetween('created_at', [$start, $end]);
        }else{
            $ledgers = Ledger::all();
        }
        return $ledgers->get();
    }

    public function headings(): array
    {
        return [
            'Date',
            'Reference No',
            'Narration',
            'Debit(DR)',
            'Credit(CR)',
            'Balance',
            'Status',
        ];
    }

    public function map($ledger): array
    {
        return [
            $ledger->created_at->format('Y-m-d'),
            $ledger->ref_no,
            $ledger->narration,
            $ledger->debit,
            $ledger->credit,
            $ledger->balance,
            $ledger->status,
        ];
    }
}
