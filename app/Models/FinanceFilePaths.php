<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinanceFilePaths extends Model
{
    use HasFactory;

    /**
     * The payment that belong to the FinanceFilePaths
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function payment()
    {
        return $this->belongsToMany(Payment::class, 'payment_finance_file_paths', 'finance_file_paths_id', 'payment_id');
    }
}
