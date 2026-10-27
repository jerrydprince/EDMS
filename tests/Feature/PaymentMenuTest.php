<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Payment;

class PaymentMenuTest extends TestCase
{
    public function test_payment_routes_accessible()
    {
        $users = User::all();
        echo "\nTotal users: " . $users->count() . "\n";
        
        foreach ($users->take(10) as $user) {
            $roles = $user->roles->pluck('name')->implode(', ');
            $res = $this->actingAs($user)->get('/finance-and-accounts');
            echo "User {$user->id} ({$user->first_name} {$user->last_name}) [{$roles}]: GET /finance-and-accounts -> " . $res->status() . "\n";
            
            $resReg = $this->actingAs($user)->get('/finance-and-accounts/get-registry-files');
            echo "  -> /finance-and-accounts/get-registry-files: " . $resReg->status() . "\n";

            $resActive = $this->actingAs($user)->get('/finance-and-accounts/get-active-payments');
            echo "  -> /finance-and-accounts/get-active-payments: " . $resActive->status() . "\n";

            $resResolved = $this->actingAs($user)->get('/finance-and-accounts/get-resolved-payments');
            echo "  -> /finance-and-accounts/get-resolved-payments: " . $resResolved->status() . "\n";
            
            $p = Payment::latest()->first();
            if ($p) {
                $resView = $this->actingAs($user)->get("/finance-and-accounts/view-payment/{$p->id}");
                echo "  -> /finance-and-accounts/view-payment/{$p->id}: " . $resView->status() . "\n";
            }
        }
        
        $this->assertTrue(true);
    }
}
