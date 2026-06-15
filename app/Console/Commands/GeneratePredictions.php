<?php

namespace App\Console\Commands;

use App\Models\MaintenanceRequest;
use App\Models\Property;
use App\Services\MaintenanceAIService;
use Illuminate\Console\Command;

/**
 * أمر إنشاء التنبؤات — تشغيل AI لتوليد تنبؤات الصيانة الوقائية لجميع العقارات
 */
class GeneratePredictions extends Command
{
    protected $signature = 'ai:predict {--property= : Specific property ID}';
    protected $description = 'Generate AI predictive maintenance for all properties with history';

    /**
     * Execute the console command.
     *
     * @param  \App\Services\MaintenanceAIService  $ai
     * @return void
     */
    public function handle(MaintenanceAIService $ai): void
    {
        $query = Property::query();

        if ($propertyId = $this->option('property')) {
            $query->where('id', $propertyId);
        }

        $properties = $query->whereHas('maintenanceRequests', function ($q) {
            $q->whereNotNull('completed_at');
        }, '>=', 3)->get();

        if ($properties->isEmpty()) {
            $this->warn('No properties with enough maintenance history (min 3 completed requests).');
            return;
        }

        $this->info('Generating predictions for ' . $properties->count() . ' properties...');

        $bar = $this->output->createProgressBar($properties->count());
        $bar->start();

        foreach ($properties as $property) {
            $category = MaintenanceRequest::where('property_id', $property->id)
                ->whereNotNull('completed_at')
                ->latest('completed_at')
                ->value('ai_category') ?? 'other';

            $ai->predictNextMaintenance($property->id, $category);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Done!');
    }
}
