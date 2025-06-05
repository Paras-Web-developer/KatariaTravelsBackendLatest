<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Drop columns only if they exist
            $drops = [
                'supplier',
                'cost',
                'sold_fare',
                'amex_card',
                'cibc_card',
                'td_busness_visa_card',
                'bmo_master_card',
                'rajni_mam',
                'td_fc_visa',
                'ticket_number',
                'fnu',
            ];

            foreach ($drops as $column) {
                if (Schema::hasColumn('invoices', $column)) {
                    $table->dropColumn($column);
                }
            }

            // Add new columns
            if (!Schema::hasColumn('invoices', 'invoice_holder_name')) {
                $table->string('invoice_holder_name')->nullable()->after('invoice_number');
            }

            if (!Schema::hasColumn('invoices', 'tickets')) {
                $table->json('tickets')->nullable()->after('invoice_holder_name');
            }
            if (!Schema::hasColumn('invoices', 'transaction_type_agency_id')) {
                $table->foreignId('transaction_type_agency_id')->nullable()->constrained('transaction_types')->cascadeOnUpdate()->noActionOnDelete()->after('agent_user_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Restore dropped columns (optional)
            $table->string('supplier')->nullable();
            $table->double('cost')->nullable();
            $table->double('sold_fare')->nullable();
            $table->double('amex_card')->nullable();
            $table->double('cibc_card')->nullable();
            $table->double('td_busness_visa_card')->nullable();
            $table->double('bmo_master_card')->nullable();
            $table->double('rajni_mam')->nullable();
            $table->double('td_fc_visa')->nullable();
            $table->string('ticket_number')->nullable();
            $table->string('fnu')->nullable();
            $table->dropForeign(index: ['transaction_type_agency_id']);
            $table->dropColumn(['invoice_holder_name', 'tickets' , 'transaction_type_agency_id']);
        });
    }
};
