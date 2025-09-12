<?php

namespace App\Exports;

use App\Models\CashBook;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class CashbookExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
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
            $cashbooks = CashBook::where('vote_book_id', $this->filters['vote_book_id'])->whereDate('created_at', now()->toDateString());
        }elseif($this->filters['report'] == 'last7'){
             $cashbooks = CashBook::where('vote_book_id', $this->filters['vote_book_id'])->whereBetween('created_at', [now()->subDays(6)->startOfDay(), now()->endOfDay()]);
        }elseif($this->filters['report'] == 'thisMonth'){
            $cashbooks = CashBook::where('vote_book_id', $this->filters['vote_book_id'])->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year);
        }elseif($this->filters['report'] == 'lastMonth'){
            $startOfLastMonth = now()->subMonth()->startOfMonth();
            $endOfLastMonth = now()->subMonth()->endOfMonth();
            $cashbooks = CashBook::where('vote_book_id', $this->filters['vote_book_id'])->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth]);
        }elseif($this->filters['report'] == 'custom'){
            $start = $this->filters['start'];
            $end = $this->filters['end'];
            $cashbooks = CashBook::where('vote_book_id', $this->filters['vote_book_id'])->whereBetween('created_at', [$start, $end]);
        }else{
            $cashbooks = CashBook::all();
        }
        return $cashbooks->get();
    }

    public function headings(): array
    {
        return [
            'Date',
            'Voucher No',
            'From',
            'To',
            'NCOA',
            'Treasury Receipt',
            'TSA/Bank',
            'DVBN',
            'Receipt Description',
            'payment Description',
        ];
    }

    public function map($cashbook): array
    {
        return [
            $cashbook->date,
            $cashbook->voucher_no,
            $cashbook->from,
            $cashbook->to,
            $cashbook->ncoa_code,
            $cashbook->treasury_receipt,
            $cashbook->tsa_bank,
            $cashbook->dvbn,
            $cashbook->receipt_description,
            $cashbook->payment_description,
        ];
    }
}
