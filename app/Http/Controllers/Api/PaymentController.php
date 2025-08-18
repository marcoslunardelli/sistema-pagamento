<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\DepositRequest;
use App\Http\Requests\TransferRequest;
use App\Http\Requests\WithdrawRequest;
use App\Services\PaymentService;
use RuntimeException;

class PaymentController extends Controller
{
    public function __construct(private PaymentService $svc)
    {
    }

    public function deposit(DepositRequest $req)
    {
        try {
            $tx = $this->svc->deposit($req->integer('user_id'), (float)$req->input('amount'));
            return response()->json($tx, 201);
        } catch (RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function withdraw(WithdrawRequest $req)
    {
        try {
            $tx = $this->svc->withdraw($req->integer('user_id'), (float)$req->input('amount'));
            return response()->json($tx, 201);
        } catch (RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function transfer(TransferRequest $req)
    {
        try {
            $tx = $this->svc->transfer(
                $req->integer('sender_id'),
                $req->integer('receiver_id'),
                (float)$req->input('amount')
            );
            return response()->json($tx, 201);
        } catch (RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function reverse(int $id, Request $req)
    {
        try {
            $tx = $this->svc->reverse($id, (int)$req->input('by_user_id'));
            return response()->json($tx, 201);
        } catch (RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
