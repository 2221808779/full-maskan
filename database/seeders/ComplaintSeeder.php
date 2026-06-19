<?php

namespace Database\Seeders;

use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Seeder;

class ComplaintSeeder extends Seeder
{
    public function run(): void
    {
        Message::where('type', 'complaint')->delete();

        $admin = User::where('user_type', 'admin')->first();
        $tenants = User::where('user_type', 'tenant')->get();

        if (!$admin || $tenants->isEmpty()) {
            $this->command->warn('المستخدمون غير متوفرين، قم بتشغيل DemoDataSeeder أولاً');
            return;
        }

        $complaints = [
            ['text' => 'صوت المكيف مزعج جداً في الليل ويؤرق النوم، أرجو صيانته أو تغييره', 'status' => 'pending'],
            ['text' => 'المصعد لا يعمل منذ ثلاثة أيام، أرجو إصلاحه في أقرب وقت', 'status' => 'in_review'],
            ['text' => 'هناك تسريب مياه من السقف في غرفة النوم الرئيسية', 'status' => 'pending'],
            ['text' => 'عداد الكهرباء يتوقف عن العمل بشكل متكرر', 'status' => 'resolved'],
            ['text' => 'الجيران يصدرون ضجيجاً عالياً بعد منتصف الليل', 'status' => 'in_review'],
        ];

        foreach ($complaints as $i => $c) {
            $tenant = $tenants[$i % $tenants->count()];
            Message::create([
                'sender_id' => $tenant->id,
                'receiver_id' => $admin->id,
                'message_text' => $c['text'],
                'type' => 'complaint',
                'complaint_status' => $c['status'],
                'sent_at' => now()->subHours(rand(1, 168)),
                'admin_response' => $c['status'] === 'resolved' ? 'تم إصلاح العطل بنجاح. شكراً لتواصلك معنا.' : null,
                'responded_by' => $c['status'] === 'resolved' ? $admin->id : null,
                'resolved_at' => $c['status'] === 'resolved' ? now() : null,
            ]);
        }

        $this->command->info('✓ تم حذف الشكاوى القديمة وإنشاء 5 شكاوى جديدة بالعربية');
    }
}
