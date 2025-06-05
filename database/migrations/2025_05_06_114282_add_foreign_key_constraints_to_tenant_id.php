<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tenant = app($this->getTenant());

        Schema::table('filament_filter_sets', function (Blueprint $table) use ($tenant) {
            $table->unsignedBigInteger('tenant_id')->nullable()->after('user_id')->change();

            $table->foreign('tenant_id')->references('id')->on($tenant->getTable());
        });

        Schema::table('filament_filter_sets_managed_preset_views', function (Blueprint $table) use ($tenant) {
            $table->unsignedBigInteger('tenant_id')->nullable()->after('user_id')->change();

            $table->foreign('tenant_id')->references('id')->on($tenant->getTable());
        });
    }

    public function down(): void
    {
        Schema::table('filament_filter_sets', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
        });

        Schema::table('filament_filter_sets_managed_preset_views', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
        });
    }

    protected function getTenant()
    {
        if ($tenant = config('advanced-tables.tenancy.tenant', null)) {
            return $tenant;
        }

        return collect(filament()->getPanels())
            ->filter(function ($panel) {
                return
                    $panel->hasTenancy() ||
                    ($panel->hasPlugin('advanced-tables') && filled($panel->getPlugin('advanced-tables')->getTenantModel()));
            })
            ->first()
            ->getTenantModel();
    }
};
