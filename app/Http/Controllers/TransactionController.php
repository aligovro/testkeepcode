<?php

namespace App\Http\Controllers;

use App\Http\Requests\RentRequest;
use App\Models\Product;
use App\Models\Transaction;
use App\Services\TransactionService;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class TransactionController extends Controller
{
    public function __construct(
        protected TransactionService $transactionService,
        protected UserService $userService
    ) {}

    public function index(): JsonResponse
    {
        $userId = auth('api')->id();

        $transactions = Cache::remember("user_{$userId}_transactions", 600, function () use ($userId) {
            return Transaction::where('user_id', $userId)
                ->with('product')
                ->latest()
                ->paginate(10);
        });
        //return response()->json(['data' => $transactions], Response::HTTP_OK);
        return response()->json(['data' => $transactions->items()], Response::HTTP_OK);
    }

    public function buy(Product $product): JsonResponse
    {
        $user = auth('api')->user();

        if (!$this->userService->hasSufficientBalance($user, $product->price)) {
            return response()->json(['error' => 'Insufficient balance'], Response::HTTP_FORBIDDEN);
        }

        $transaction = $this->transactionService->createPurchaseTransaction($user, $product);

        $this->userService->deductBalance($user, $product->price);
        $product->decrement('quantity');

        return response()->json($transaction, Response::HTTP_CREATED);
    }

    public function rent(Product $product, RentRequest $request): JsonResponse
    {
        $user = auth('api')->user();
        $rentalTime = $request->validated()['hours'];

        if (!$this->userService->hasSufficientBalance($user, $product->rental_price)) {
            return response()->json(['error' => 'Insufficient balance'], Response::HTTP_FORBIDDEN);
        }

        $transaction = $this->transactionService->createRentalTransaction($user, $product, $rentalTime);
        $this->userService->deductBalance($user, $product->rental_price);

        return response()->json($transaction, Response::HTTP_CREATED);
    }

    public function extend(Transaction $transaction, RentRequest $request): JsonResponse
    {
        $additionalHours = $request->validated()['hours'];

        try {
            $transaction = $this->transactionService->extendRentalTransaction($transaction, $additionalHours);
            return response()->json($transaction, Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
