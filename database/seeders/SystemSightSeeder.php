<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SystemSightSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create 3 Machines (Hard-coded)
        $demand = \DB::table('machines')->insertGetId([
            'name' => 'Demand',
            'slug' => 'demand',
            'description' => 'Creates leads',
            'icon' => '🎯',
            'color' => '#fbbf24',
            'order' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $sales = \DB::table('machines')->insertGetId([
            'name' => 'Sales',
            'slug' => 'sales',
            'description' => 'Converts leads to cash',
            'icon' => '💰',
            'color' => '#10b981',
            'order' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $delivery = \DB::table('machines')->insertGetId([
            'name' => 'Delivery',
            'slug' => 'delivery',
            'description' => 'Delivery smooth',
            'icon' => '🚀',
            'color' => '#f97316',
            'order' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Create Subsystems for Demand
        $contentEngine = \DB::table('subsystems')->insertGetId([
            'machine_id' => $demand,
            'name' => 'Content Engine',
            'slug' => 'content-engine',
            'description' => 'Creates content',
            'icon' => '📝',
            'color' => '#fbbf24',
            'order' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $distributionEngine = \DB::table('subsystems')->insertGetId([
            'machine_id' => $demand,
            'name' => 'Distribution Engine',
            'slug' => 'distribution-engine',
            'description' => 'Shares content',
            'icon' => '📢',
            'color' => '#10b981',
            'order' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $leadCaptureEngine = \DB::table('subsystems')->insertGetId([
            'machine_id' => $demand,
            'name' => 'Lead Capture Engine',
            'slug' => 'lead-capture-engine',
            'description' => 'Captures leads',
            'icon' => '🎣',
            'color' => '#f97316',
            'order' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Create Components for Content Engine
        \DB::table('components')->insert([
            [
                'subsystem_id' => $contentEngine,
                'name' => 'Hooks',
                'slug' => 'hooks',
                'description' => 'Creates content',
                'icon' => '🪝',
                'health_status' => 'needs_love',
                'current_issue' => 'Hooks feel stale',
                'metric_value' => 5,
                'metric_label' => 'Hooks',
                'order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'subsystem_id' => $contentEngine,
                'name' => 'Scripts',
                'slug' => 'scripts',
                'description' => 'Shares content',
                'icon' => '📜',
                'health_status' => 'smooth',
                'current_issue' => 'Outlines sharp',
                'metric_value' => 6,
                'metric_label' => 'Scripts',
                'order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'subsystem_id' => $contentEngine,
                'name' => 'Filming',
                'slug' => 'filming',
                'description' => 'Slow filming process',
                'icon' => '🎥',
                'health_status' => 'on_fire',
                'current_issue' => 'Slow filming process',
                'metric_value' => 8,
                'metric_label' => 'Videos',
                'order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'subsystem_id' => $contentEngine,
                'name' => 'Editing',
                'slug' => 'editing',
                'description' => 'Editing on point',
                'icon' => '✂️',
                'health_status' => 'smooth',
                'current_issue' => 'Editing on point',
                'metric_value' => 12,
                'metric_label' => 'Posts',
                'order' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // 4. Create Components for Distribution Engine
        \DB::table('components')->insert([
            [
                'subsystem_id' => $distributionEngine,
                'name' => 'Social Media',
                'slug' => 'social-media',
                'description' => 'Post to social platforms',
                'icon' => '📱',
                'health_status' => 'smooth',
                'current_issue' => null,
                'metric_value' => 2400,
                'metric_label' => 'Views',
                'order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // 5. Create Components for Lead Capture
        \DB::table('components')->insert([
            [
                'subsystem_id' => $leadCaptureEngine,
                'name' => 'Landing Page',
                'slug' => 'landing-page',
                'description' => 'Capture leads',
                'icon' => '🎯',
                'health_status' => 'needs_love',
                'current_issue' => 'Lead magnet leaking',
                'metric_value' => 25,
                'metric_label' => 'Opt-Ins',
                'order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
