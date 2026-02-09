<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first();

        if (!$user) {
            $this->command->warn('No users found. Please create a user first.');
            return;
        }

        // Sample notifications
        $notifications = [
            [
                'user_id' => $user->id,
                'type' => 'upgrade_shipped',
                'title' => '🎉 Upgrade Shipped!',
                'message' => 'You shipped "3-step hook writing process" for Content Engine',
                'data' => ['component_name' => 'Content Engine'],
                'is_read' => false,
                'created_at' => now()->subMinutes(5),
            ],
            [
                'user_id' => $user->id,
                'type' => 'streak_milestone',
                'title' => '🔥 Streak Milestone!',
                'message' => 'Congratulations! You reached a 3-week shipping streak',
                'data' => ['streak' => 3],
                'is_read' => false,
                'created_at' => now()->subHours(2),
            ],
            [
                'user_id' => $user->id,
                'type' => 'component_on_fire',
                'title' => '🔥 Component Needs Attention',
                'message' => 'Hooks component in Content Engine is on fire and needs an upgrade',
                'data' => ['component_name' => 'Hooks'],
                'is_read' => true,
                'created_at' => now()->subDays(1),
            ],
            [
                'user_id' => $user->id,
                'type' => 'weekly_reminder',
                'title' => '📅 Weekly Check-in',
                'message' => 'Time to review your Business Machine and ship some upgrades!',
                'data' => [],
                'is_read' => true,
                'created_at' => now()->subDays(3),
            ],
        ];

        foreach ($notifications as $notification) {
            Notification::create($notification);
        }

        $this->command->info('Sample notifications created successfully!');
    }
}
