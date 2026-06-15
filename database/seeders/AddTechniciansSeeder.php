<?php

namespace Database\Seeders;

use App\Models\MaintenanceRequest;
use App\Models\Property;
use App\Models\Specialty;
use App\Models\TechnicianProfile;
use App\Models\TechnicianSpecialization;
use App\Models\User;
use Illuminate\Database\Seeder;

class AddTechniciansSeeder extends Seeder
{
    public function run(): void
    {
        $techs = [
            ['full_name' => 'كريم النفاتي', 'phone' => '0910000012', 'specialty' => 'electricity', 'bio' => 'فني كهرباء محترف'],
            ['full_name' => 'سالم المبروك',  'phone' => '0910000013', 'specialty' => 'plumbing', 'bio' => 'فني سباكة خبرة 12 سنة'],
            ['full_name' => 'مفتاح القذافي', 'phone' => '0910000014', 'specialty' => 'air_conditioning', 'bio' => 'فني تكييف وتبريد'],
            ['full_name' => 'عبدالسلام الزوي', 'phone' => '0910000015', 'specialty' => 'painting', 'bio' => 'فني دهان وديكور'],
            ['full_name' => 'الصديق الكيش',  'phone' => '0910000016', 'specialty' => 'carpentry', 'bio' => 'نجار متخصص في الأثاث'],
            ['full_name' => 'نوري العباني',  'phone' => '0910000017', 'specialty' => 'other', 'bio' => 'فني صيانة عامة'],
        ];

        $createdTechs = [];
        $specialtyMap = Specialty::pluck('id', 'name')->toArray();

        foreach ($techs as $data) {
            $user = User::create([
                'full_name' => $data['full_name'],
                'phone' => $data['phone'],
                'password' => bcrypt('password'),
                'user_type' => 'technician',
                'phone_verified_at' => now(),
            ]);

            $profile = TechnicianProfile::create([
                'user_id' => $user->id,
                'bio' => $data['bio'],
                'experience_years' => rand(4, 15),
            ]);

            if (isset($specialtyMap[$data['specialty']])) {
                TechnicianSpecialization::create([
                    'profile_id' => $profile->id,
                    'specialization_id' => $specialtyMap[$data['specialty']],
                ]);
            }

            $createdTechs[] = ['user' => $user, 'specialty' => $data['specialty']];
        }

        $properties = Property::inRandomOrder()->take(8)->get();
        $tenants = User::where('user_type', 'tenant')->get();
        $statuses = ['assigned', 'assigned', 'in_progress', 'pending'];
        $catMap = ['plumbing' => 2, 'electricity' => 1, 'air_conditioning' => 3, 'painting' => 4, 'carpentry' => 5, 'other' => 6];

        $requests = [
            ['specialty' => 'electricity', 'problem' => 'قاطع الكهرباء العام يفصل باستمرار', 'priority' => 'urgent'],
            ['specialty' => 'electricity', 'problem' => 'لمبة المطبخ لا تعمل', 'priority' => 'low'],
            ['specialty' => 'plumbing', 'problem' => 'حوض المغسلة مسدود', 'priority' => 'medium'],
            ['specialty' => 'plumbing', 'problem' => 'تسريب مياه من السخان', 'priority' => 'high'],
            ['specialty' => 'air_conditioning', 'problem' => 'المكيف لا يبرد', 'priority' => 'high'],
            ['specialty' => 'air_conditioning', 'problem' => 'صوت غريب في المكيف', 'priority' => 'medium'],
            ['specialty' => 'painting', 'problem' => 'طلاء جدران الصالة بحاجة لتجديد', 'priority' => 'low'],
            ['specialty' => 'painting', 'problem' => 'تشققات في سقف غرفة النوم', 'priority' => 'medium'],
            ['specialty' => 'carpentry', 'problem' => 'باب الخزانة لا يقفل', 'priority' => 'low'],
            ['specialty' => 'carpentry', 'problem' => 'شباك المطبخ عالق', 'priority' => 'medium'],
            ['specialty' => 'other', 'problem' => 'تركيب رفوف في المخزن', 'priority' => 'low'],
            ['specialty' => 'other', 'problem' => 'تغيير زجاج نافذة مكسور', 'priority' => 'medium'],
        ];

        foreach ($requests as $i => $req) {
            $tech = collect($createdTechs)->firstWhere('specialty', $req['specialty']);
            $status = $statuses[$i % count($statuses)];
            $cat = $req['specialty'];

            MaintenanceRequest::create([
                'property_id' => $properties[$i % count($properties)]->id,
                'tenant_id' => $tenants[$i % count($tenants)]->id,
                'technician_id' => $status !== 'pending' ? $tech['user']->id : null,
                'problem_description' => $req['problem'],
                'ai_category' => $cat,
                'ai_accuracy' => rand(70, 99) / 100,
                'category' => $cat,
                'category_id' => $catMap[$cat] ?? 6,
                'priority' => $req['priority'],
                'status' => $status,
            ]);
        }
    }
}
