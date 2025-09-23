<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Task;
use App\Models\User;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil beberapa user berdasarkan role
        $leader   = User::where('role', 'leader')->first();
        $manager  = User::where('role', 'manager')->first();
        $admin    = User::where('role', 'admin')->first();
        $users    = User::where('role', 'user')->get();

        if (!$leader || !$manager || !$admin || $users->isEmpty()) {
            $this->command->warn('Seeder gagal: pastikan sudah ada user dengan role leader, manager, admin, dan user.');
            return;
        }

        // Buat task oleh leader
        Task::create([
            'created_by'  => $leader->id,
            'assigned_to' => $users->random()->id,
            'title'       => 'Task dari Leader',
            'description' => 'Task ini dibuat oleh leader untuk tim.',
            'status'      => 'todo',
            'division'    => $leader->Division,
            'team'        => $leader->Team,
        ]);

        // Buat task oleh manager
        Task::create([
            'created_by'  => $manager->id,
            'assigned_to' => $users->random()->id,
            'title'       => 'Task dari Manager',
            'description' => 'Task ini dibuat oleh manager untuk division.',
            'status'      => 'in_progress',
            'division'    => $manager->Division,
            'team'        => null,
        ]);

        // Buat task dummy tambahan (random)
        foreach (range(1, 5) as $i) {
            Task::create([
                'created_by'  => $admin->id,
                'assigned_to' => $users->random()->id,
                'title'       => "Dummy Task #$i",
                'description' => "Deskripsi untuk task dummy ke-$i",
                'status'      => collect(['todo', 'in_progress', 'review', 'done'])->random(),
                'division'    => $users->random()->Division,
                'team'        => $users->random()->Team,
            ]);
        }
    }
}
