<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class PaymentService
{
    private const ST_PENDING   = 'pending';
    private const ST_COMPLETED = 'completed';
    private const ST_REVERSED  = 'reversed';

    private const TP_TRANSFER = 'transfer';
    private const TP_DEPOSIT  = 'deposit';
    private const TP_WITHDRAW = 'withdraw';
    private const TP_REVERSAL = 'reversal';

    public function deposit(int $userId, float $amount): Transaction
    {
        if ($amount <= 0) throw new RuntimeException('Valor inválido');
        return DB::transaction(function() use ($userId, $amount) {
            $user = User::lockForUpdate()->findOrFail($userId);
            $user->balance += $amount;
            $user->save();

            return Transaction::create([
                'uuid' => (string) Str::uuid(),
                'type' => self::TP_DEPOSIT,
                'sender_id' => null,
                'receiver_id' => $user->id,
                'amount' => $amount,
                'status' => self::ST_COMPLETED,
                'original_id' => null,
            ]);
        });
    }

    public function withdraw(int $userId, float $amount): Transaction
    {
        if ($amount <= 0) throw new RuntimeException('Valor inválido');
        return DB::transaction(function() use ($userId, $amount) {
            $user = User::lockForUpdate()->findOrFail($userId);
            if ($user->balance < $amount) throw new RuntimeException('Saldo insuficiente');

            $user->balance -= $amount;
            $user->save();

            return Transaction::create([
                'uuid' => (string) Str::uuid(),
                'type' => self::TP_WITHDRAW,
                'sender_id' => $user->id,
                'receiver_id' => null,
                'amount' => $amount,
                'status' => self::ST_COMPLETED,
                'original_id' => null,
            ]);
        });
    }

    public function transfer(int $senderId, int $receiverId, float $amount): Transaction
    {
        if ($amount <= 0) throw new RuntimeException('Valor inválido');
        if ($senderId === $receiverId) throw new RuntimeException('Mesma conta');

        $sender = User::findOrFail($senderId);
        if ($sender->type === 'lojista') {
            throw new RuntimeException('Lojista não pode enviar');
        }

        if (!$this->authorizeExternal($senderId, $receiverId, $amount)) {
            throw new RuntimeException('Transação não autorizada');
        }

        return DB::transaction(function() use ($senderId, $receiverId, $amount) {
            $sender   = User::lockForUpdate()->findOrFail($senderId);
            $receiver = User::lockForUpdate()->findOrFail($receiverId);

            if ($sender->balance < $amount) throw new RuntimeException('Saldo insuficiente');

            $sender->balance   -= $amount;
            $receiver->balance += $amount;
            $sender->save();
            $receiver->save();

            $tx = Transaction::create([
                'uuid' => (string) Str::uuid(),
                'type' => self::TP_TRANSFER,
                'sender_id' => $sender->id,
                'receiver_id' => $receiver->id,
                'amount' => $amount,
                'status' => self::ST_COMPLETED,
                'original_id' => null,
            ]);

            $this->notify($sender, $receiver, $amount);

            return $tx;
        });
    }

    public function reverse(int $transactionId, int $byUserId): Transaction
    {
        return DB::transaction(function() use ($transactionId, $byUserId) {
            $original = Transaction::lockForUpdate()->findOrFail($transactionId);

            if ($original->status !== self::ST_COMPLETED) {
                throw new RuntimeException('Transação não está concluída');
            }
            if ($original->type !== self::TP_TRANSFER) {
                throw new RuntimeException('Apenas transferências podem ser estornadas');
            }
            if ($original->receiver_id !== $byUserId) {
                throw new RuntimeException('Somente o recebedor pode estornar');
            }

            $sender   = User::lockForUpdate()->findOrFail($original->sender_id);
            $receiver = User::lockForUpdate()->findOrFail($original->receiver_id);

            if ($receiver->balance < $original->amount) {
                throw new RuntimeException('Recebedor sem saldo para estorno');
            }

            $receiver->balance -= $original->amount;
            $sender->balance   += $original->amount;
            $receiver->save();
            $sender->save();

            $original->status = self::ST_REVERSED;
            $original->save();

            return Transaction::create([
                'uuid' => (string) Str::uuid(),
                'type' => self::TP_REVERSAL,
                'sender_id' => $receiver->id,
                'receiver_id' => $sender->id,
                'amount' => $original->amount,
                'status' => self::ST_COMPLETED,
                'original_id' => $original->id,
            ]);
        });
    }

    private function authorizeExternal(int $senderId, int $receiverId, float $amount): bool
    {
        return env('AUTHORIZER_MOCK', 'allow') === 'allow';
    }

    private function notify(User $sender, User $receiver, float $amount): void
    {
        Log::info('Pagamento recebido', [
            'from' => $sender->email,
            'to' => $receiver->email,
            'amount' => $amount,
        ]);
    }
}
