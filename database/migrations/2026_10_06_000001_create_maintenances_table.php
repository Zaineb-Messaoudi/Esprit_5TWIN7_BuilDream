<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Equipment belongs to another module and may be merged later.
        // MySQL can leave this table behind if a previous FK ALTER failed.
        $equipmentExists = Schema::hasTable('equipment');

        if (Schema::hasTable('maintenances')) {
            $expected = ['id', 'equipment_id', 'start_date', 'end_date', 'reason', 'cost', 'status', 'notes', 'created_at', 'updated_at'];
            $actual = Schema::getColumnListing('maintenances');
            if (array_diff($expected, $actual) || array_diff($actual, $expected)) {
                throw new RuntimeException('The existing maintenances table has a different schema; inspect it before resuming this migration.');
            }

            $hasIndex = collect(Schema::getIndexes('maintenances'))->contains(
                fn (array $index) => ($index['columns'] ?? []) === ['equipment_id', 'start_date']
            );
            if (! $hasIndex) {
                Schema::table('maintenances', fn (Blueprint $table) => $table->index(['equipment_id', 'start_date']));
            }

            $hasForeignKey = collect(Schema::getForeignKeys('maintenances'))->contains(
                fn (array $key) => in_array('equipment_id', $key['columns'] ?? [], true)
            );
            if ($equipmentExists && ! $hasForeignKey) {
                Schema::table('maintenances', fn (Blueprint $table) => $table->foreign('equipment_id')->references('id')->on('equipment')->restrictOnDelete());
            }

            return;
        }

        Schema::create('maintenances', function (Blueprint $table) use ($equipmentExists) {
            $table->id();
            $table->unsignedBigInteger('equipment_id');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('reason');
            $table->decimal('cost', 10, 2)->default(0);
            $table->string('status')->default('planned');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['equipment_id', 'start_date']);
            if ($equipmentExists) {
                $table->foreign('equipment_id')->references('id')->on('equipment')->restrictOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenances');
    }
};
