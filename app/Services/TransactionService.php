<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Product;
use Carbon\Carbon;

class TransactionService
{
    public function createPurchaseTransaction(User $user, Product $product): Transaction
    {
        return $user->transactions()->create([
            'product_id' => $product->id,
            'type' => 'purchase',
            'unique_code' => str()->uuid()->toString(),
        ]);
    }

    public function createRentalTransaction(User $user, Product $product, int $rentalTime): Transaction
    {
        return $user->transactions()->create([
            'product_id' => $product->id,
            'type' => 'rental',
            'expires_at' => now()->addHours($rentalTime),
            'unique_code' => str()->uuid()->toString(),
        ]);
    }

    public function extendRentalTransaction(Transaction $transaction, int $additionalHours): Transaction
    {
        if ($transaction->type !== 'rental') {
            throw new \InvalidArgumentException('Transaction is not a rental');
        }

        $currentExpiry = Carbon::parse($transaction->expires_at);
        $maxExpiry = Carbon::parse($transaction->created_at)->addHours(24);
        $newExpiry = $currentExpiry->addHours($additionalHours);

        if ($newExpiry > $maxExpiry) {
            throw new \InvalidArgumentException('Rental extension exceeds the maximum allowed duration of 24 hours');
        }

        $transaction->update(['expires_at' => $newExpiry]);

        return $transaction;
    }
}
