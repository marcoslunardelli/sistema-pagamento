<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_comum_transfers_and_receiver_can_reverse()
    {
        $sender   = User::factory()->create(['type'=>'comum','balance'=>200]);
        $receiver = User::factory()->create(['type'=>'lojista','balance'=>50]);

        // transfer
        $resp = $this->postJson('/api/transfer', [
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'amount' => 25.50,
        ]);
        $resp->assertCreated()->assertJsonStructure(['id']);

        $txId = $resp->json('id');

        // reverse by receiver
        $rev = $this->postJson("/api/transactions/{$txId}/reverse", [
            'by_user_id' => $receiver->id,
        ]);
        $rev->assertCreated()->assertJson(['type'=>'reversal']);
    }
}
