<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->string('loanable_type');
            $table->string('loanable_id');

            $table->enum('direction', ['lend', 'borrow']);
            // lend = Raj Travels gave money (Receivable)
            // borrow = Raj Travels took money (Payable)

            $table->decimal('amount', 12, 2);
            $table->decimal('paid_amount', 12, 2)->default(0);

            $table->date('date');
            $table->string('status')->default('unpaid');

            $table->string('description')->nullable();
            $table->timestamps();

            $table->index([
                'loanable_type',
                'loanable_id',
                'direction',
                'status',
                'date'
            ]);

            $table->unique([
                'loanable_type',
                'loanable_id',
                'direction'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};

//  public function lend(): HasOne
//     {
//         return $this->hasOne(Loan::class)->where('direction', 'lend');
//     }

//     // One Borrow Record (User → Raj Travels)
//     public function borrow(): HasOne
//     {
//         return $this->hasOne(Loan::class)->where('direction', 'borrow');
//     }