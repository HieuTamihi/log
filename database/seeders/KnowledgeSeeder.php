<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Folder;
use App\Models\Note;
use App\Models\NoteCard;

class KnowledgeSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        
        if (!$user) {
            $user = User::create([
                'username' => 'demo',
                'password' => bcrypt('password'),
            ]);
        }

        // Tạo folders
        $folder1 = Folder::create([
            'name' => 'Dự án',
            'user_id' => $user->id,
        ]);

        $folder2 = Folder::create([
            'name' => 'Ý tưởng',
            'user_id' => $user->id,
        ]);

        $subfolder = Folder::create([
            'name' => 'Web Development',
            'parent_id' => $folder1->id,
            'user_id' => $user->id,
        ]);

        // Tạo notes
        $note1 = Note::create([
            'name' => 'Laravel Tips',
            'content' => 'Các tips và tricks khi làm việc với Laravel framework',
            'folder_id' => $subfolder->id,
            'user_id' => $user->id,
        ]);

        $note2 = Note::create([
            'name' => 'Vue.js Basics',
            'content' => 'Kiến thức cơ bản về Vue.js và composition API',
            'folder_id' => $subfolder->id,
            'user_id' => $user->id,
        ]);

        $note3 = Note::create([
            'name' => 'App Ideas',
            'content' => 'Danh sách các ý tưởng ứng dụng mới',
            'folder_id' => $folder2->id,
            'user_id' => $user->id,
        ]);

        // Tạo cards
        $card1 = NoteCard::create([
            'note_id' => $note1->id,
            'user_id' => $user->id,
            'position_x' => 100,
            'position_y' => 100,
        ]);

        $card2 = NoteCard::create([
            'note_id' => $note2->id,
            'user_id' => $user->id,
            'position_x' => 400,
            'position_y' => 150,
        ]);

        // Tạo link giữa các cards
        $card1->linkedNotes()->attach($note2->id);
    }
}
