<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DepositRequest;
use App\Http\Requests\TransferRequest;
use App\Http\Requests\WithdrawRequest;
use App\Services\PaymentService;
use RuntimeException;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(private PaymentService $svc) {}

    public function deposit(DepositRequest $req)
    {
        try {
            $userId = (int) $req->input("user_id");
            $amount = (float) $req->input("amount");

            $tx = $this->svc->deposit($userId, $amount);
            return response()->json($tx, 201);
        } catch (RuntimeException $e) {
            return response()->json(["error"=>$e->getMessage()], 422);
        }
    }

    public function withdraw(WithdrawRequest $req)
    {
        try {
            $userId = (int) $req->input("user_id");
            $amount = (float) $req->input("amount");

            $tx = $this->svc->withdraw($userId, $amount);
            return response()->json($tx, 201);
        } catch (RuntimeException $e) {
            return response()->json(["error"=>$e->getMessage()], 422);
        }
    }

    public function transfer(TransferRequest $req)
    {
        try {
            $senderId   = (int) $req->input("sender_id");
            $receiverId = (int) $req->input("receiver_id");
            $amount     = (float) $req->input("amount");

            $tx = $this->svc->transfer($senderId, $receiverId, $amount);
            return response()->json($tx, 201);
        } catch (RuntimeException $e) {
            return response()->json(["error"=>$e->getMessage()], 422);
        }
    }

    public function reverse(int $id, Request $req)
    {
        try {
            $byUserId = (int) $req->input("by_user_id");
            $tx = $this->svc->reverse($id, $byUserId);
            return response()->json($tx, 201);
        } catch (RuntimeException $e) {
            return response()->json(["error"=>$e->getMessage()], 422);
        }
    }
}
