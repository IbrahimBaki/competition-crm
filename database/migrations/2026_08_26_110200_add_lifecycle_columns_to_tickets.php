<?php

use App\Domains\Ticketing\Models\TicketStatusDefinition;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->foreignId('ticket_status_id')->nullable()->constrained('ticket_statuses')->nullOnDelete();
            $table->timestampTz('status_changed_at')->nullable();
            $table->timestampTz('resolved_at')->nullable();
            $table->timestampTz('reopen_deadline_at')->nullable();
            $table->unsignedInteger('reopened_count')->default(0);
            $table->foreignId('merged_into_ticket_id')->nullable()->constrained('tickets')->nullOnDelete();
            $table->timestampTz('merged_at')->nullable();
            $table->foreignId('parent_ticket_id')->nullable()->constrained('tickets')->nullOnDelete();
            $table->timestampTz('spam_marked_at')->nullable();

            $table->index('ticket_status_id');
            $table->index('merged_into_ticket_id');
            $table->index('parent_ticket_id');
        });

        $this->backfillTicketStatuses();
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeignIdFor(TicketStatusDefinition::class, 'ticket_status_id');
            $table->dropIndex(['ticket_status_id']);
            $table->dropIndex(['merged_into_ticket_id']);
            $table->dropIndex(['parent_ticket_id']);
            $table->dropColumn([
                'ticket_status_id',
                'status_changed_at',
                'resolved_at',
                'reopen_deadline_at',
                'reopened_count',
                'merged_into_ticket_id',
                'merged_at',
                'parent_ticket_id',
                'spam_marked_at',
            ]);
        });
    }

    private function backfillTicketStatuses(): void
    {
        $statuses = DB::table('ticket_statuses')
            ->pluck('id', 'key')
            ->toArray();

        DB::table('tickets')
            ->whereNull('ticket_status_id')
            ->chunkById(500, function ($tickets) use ($statuses) {
                foreach ($tickets as $ticket) {
                    $statusId = $statuses[$ticket->status] ?? null;

                    if ($statusId) {
                        DB::table('tickets')
                            ->where('id', $ticket->id)
                            ->update(['ticket_status_id' => $statusId]);
                    }
                }
            });
    }
};
