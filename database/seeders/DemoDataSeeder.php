<?php

namespace Database\Seeders;

use App\Models\BlackoutDate;
use App\Models\Booking;
use App\Models\Favorite;
use App\Models\Image;
use App\Models\MaintenanceRequest;
use App\Models\Message;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Review;
use App\Models\Specialty;
use App\Models\TechnicianProfile;
use App\Models\TechnicianSpecialization;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'full_name' => 'مدير النظام',
            'phone' => '0910000001',
            'password' => bcrypt('password'),
            'user_type' => 'admin',
            'phone_verified_at' => now(),
        ]);

        $owners = [];
        $ownerNames = [
            ['full_name' => 'أحمد المنصوري', 'phone' => '0910000002'],
            ['full_name' => 'سارة التميمي', 'phone' => '0910000003'],
            ['full_name' => 'خالد بن غربية', 'phone' => '0910000004'],
        ];
        foreach ($ownerNames as $i => $data) {
            $user = User::create([
                'full_name' => $data['full_name'],
                'phone' => $data['phone'],
                'password' => bcrypt('password'),
                'user_type' => 'owner',
                'phone_verified_at' => now(),
            ]);
            $owners[] = $user;
        }

        $tenants = [];
        $tenantNames = [
            ['full_name' => 'فاطمة الزهراني', 'phone' => '0910000005'],
            ['full_name' => 'مصطفى العقوري', 'phone' => '0910000006'],
            ['full_name' => 'نورة السويحلي', 'phone' => '0910000007'],
            ['full_name' => 'عمر الفيتوري', 'phone' => '0910000008'],
            ['full_name' => 'هند الجفري', 'phone' => '0910000009'],
        ];
        foreach ($tenantNames as $i => $data) {
            $user = User::create([
                'full_name' => $data['full_name'],
                'phone' => $data['phone'],
                'password' => bcrypt('password'),
                'user_type' => 'tenant',
                'phone_verified_at' => now(),
            ]);
            $tenants[] = $user;
        }

        $technicians = [];
        $techNames = [
            ['full_name' => 'محمود البركي', 'phone' => '0910000010'],
            ['full_name' => 'علي الشريف', 'phone' => '0910000011'],
        ];
        foreach ($techNames as $i => $data) {
            $user = User::create([
                'full_name' => $data['full_name'],
                'phone' => $data['phone'],
                'password' => bcrypt('password'),
                'user_type' => 'technician',
                'phone_verified_at' => now(),
            ]);
            $technicians[] = $user;

            TechnicianProfile::create([
                'user_id' => $user->id,
                'bio' => 'فني صيانة متخصص',
                'experience_years' => rand(3, 10),
            ]);
        }

        $propertyData = [
            [
                'owner_idx' => 0,
                'location' => 'طرابلس، وسط البلد، شارع الاستقلال',
                'title' => 'فيلا فاخرة أمام البحر',
                'property_type' => 'villa',
                'price' => 850,
                'rooms_count' => 5, 'bathrooms_count' => 4,
            ],
            [
                'owner_idx' => 0,
                'location' => 'طرابلس، أبو سليم، شارع المطار',
                'title' => 'شقة عصرية في أبو سليم',
                'property_type' => 'apartment',
                'price' => 250,
                'rooms_count' => 3, 'bathrooms_count' => 2,
            ],
            [
                'owner_idx' => 0,
                'location' => 'الخمس، شاطئ الخمس، الشارع الساحلي',
                'title' => 'استوديو بإطلالة بحرية',
                'property_type' => 'apartment',
                'price' => 180,
                'rooms_count' => 1, 'bathrooms_count' => 1,
            ],
            [
                'owner_idx' => 1,
                'location' => 'طرابلس، جنزور، الطريق الساحلي',
                'title' => 'فيلا حديقة خاصة',
                'property_type' => 'villa',
                'price' => 650,
                'rooms_count' => 4, 'bathrooms_count' => 3,
            ],
            [
                'owner_idx' => 1,
                'location' => 'بنغازي، وسط البلد، شارع عمر المختار',
                'title' => 'شقة مفروشة في بنغازي',
                'property_type' => 'apartment',
                'price' => 220,
                'rooms_count' => 2, 'bathrooms_count' => 1,
            ],
            [
                'owner_idx' => 1,
                'location' => 'بنغازي، الكويفية، شارع السكة',
                'title' => 'مكتب إداري في الكويفية',
                'property_type' => 'office',
                'price' => 300,
                'rooms_count' => 1, 'bathrooms_count' => 1,
            ],
            [
                'owner_idx' => 2,
                'location' => 'مصراتة، المركز، شارع طرابلس',
                'title' => 'محل تجاري في مصراتة',
                'property_type' => 'shop',
                'price' => 400,
                'rooms_count' => 0, 'bathrooms_count' => 1,
            ],
            [
                'owner_idx' => 2,
                'location' => 'طرابلس، وسط البلد، شارع الاستقلال',
                'title' => 'شقة دوبلكس فاخرة',
                'property_type' => 'apartment',
                'price' => 420,
                'rooms_count' => 4, 'bathrooms_count' => 3,
            ],
            [
                'owner_idx' => 2,
                'location' => 'طرابلس، أبو سليم، شارع المطار',
                'title' => 'مستودع تخزين كبير',
                'property_type' => 'warehouse',
                'price' => 200,
                'rooms_count' => 0, 'bathrooms_count' => 1,
            ],
            [
                'owner_idx' => 0,
                'location' => 'طرابلس، جنزور، الطريق الساحلي',
                'title' => 'أرض استثمارية في جنزور',
                'property_type' => 'land',
                'price' => 500,
                'rooms_count' => 0, 'bathrooms_count' => 0,
            ],
        ];

        $properties = [];
        foreach ($propertyData as $i => $pd) {
            $prop = Property::create([
                'owner_id' => $owners[$pd['owner_idx']]->id,
                'title' => $pd['title'],
                'description' => $pd['title'] . ' — وصف تفصيلي للعقار. يتميز هذا العقار بموقعه الممتاز وتشطيباته العصرية.',
                'location' => $pd['location'],
                'price' => $pd['price'],
                'property_type' => $pd['property_type'],
                'rooms_count' => $pd['rooms_count'],
                'bathrooms_count' => $pd['bathrooms_count'],
                'status' => 'pending',
            ]);
            $properties[] = $prop;
        }

        $now = Carbon::now();

        $imagePaths = [
            'properties/villa1.jpg', 'properties/villa2.jpg', 'properties/apartment1.jpg',
            'properties/apartment2.jpg', 'properties/studio1.jpg', 'properties/office1.jpg',
            'properties/shop1.jpg', 'properties/duplex1.jpg', 'properties/warehouse1.jpg',
            'properties/land1.jpg',
        ];
        foreach ($properties as $i => $prop) {
            Image::create([
                'property_id' => $prop->id,
                'image_path' => $imagePaths[$i % count($imagePaths)],
                'added_at' => $now,
            ]);
            if ($i % 2 === 0) {
                Image::create([
                    'property_id' => $prop->id,
                    'image_path' => 'properties/gallery' . ($i + 1) . '.jpg',
                    'added_at' => $now,
                ]);
            }
        }

        foreach ($properties as $i => $prop) {
            if ($i % 3 === 0) {
                BlackoutDate::create([
                    'property_id' => $prop->id,
                    'date' => (clone $now)->addDays(15 + $i),
                    'status' => 'blocked',
                ]);
                BlackoutDate::create([
                    'property_id' => $prop->id,
                    'date' => (clone $now)->addDays(16 + $i),
                    'status' => 'blocked',
                ]);
            }
        }

        $specialtyIds = Specialty::pluck('id')->toArray();
        foreach ($technicians as $i => $tech) {
            $profile = TechnicianProfile::where('user_id', $tech->id)->first();
            if ($profile && !empty($specialtyIds)) {
                TechnicianSpecialization::create([
                    'profile_id' => $profile->id,
                    'specialization_id' => $specialtyIds[$i % count($specialtyIds)],
                ]);
                if (isset($specialtyIds[($i + 2) % count($specialtyIds)])) {
                    TechnicianSpecialization::create([
                        'profile_id' => $profile->id,
                        'specialization_id' => $specialtyIds[($i + 2) % count($specialtyIds)],
                    ]);
                }
            }
        }

        $bookingData = [
            ['tenant_idx' => 0, 'prop_idx' => 0, 'start' => -30, 'end' => -26, 'price' => 3400, 'status' => 'completed'],
            ['tenant_idx' => 0, 'prop_idx' => 1, 'start' => -14, 'end' => -10, 'price' => 1000, 'status' => 'completed'],
            ['tenant_idx' => 1, 'prop_idx' => 3, 'start' => -21, 'end' => -17, 'price' => 2600, 'status' => 'completed'],
            ['tenant_idx' => 1, 'prop_idx' => 0, 'start' => -7, 'end' => -3, 'price' => 3400, 'status' => 'completed'],
            ['tenant_idx' => 2, 'prop_idx' => 2, 'start' => -10, 'end' => -7, 'price' => 540, 'status' => 'completed'],
            ['tenant_idx' => 2, 'prop_idx' => 7, 'start' => -3, 'end' => 0, 'price' => 1260, 'status' => 'confirmed'],
            ['tenant_idx' => 3, 'prop_idx' => 1, 'start' => -5, 'end' => -1, 'price' => 1000, 'status' => 'completed'],
            ['tenant_idx' => 3, 'prop_idx' => 3, 'start' => 3, 'end' => 7, 'price' => 2600, 'status' => 'pending'],
            ['tenant_idx' => 4, 'prop_idx' => 7, 'start' => 1, 'end' => 5, 'price' => 1680, 'status' => 'confirmed'],
            ['tenant_idx' => 4, 'prop_idx' => 0, 'start' => -60, 'end' => -55, 'price' => 4250, 'status' => 'completed'],
            ['tenant_idx' => 0, 'prop_idx' => 4, 'start' => 5, 'end' => 8, 'price' => 660, 'status' => 'pending'],
            ['tenant_idx' => 1, 'prop_idx' => 7, 'start' => -1, 'end' => 2, 'price' => 1260, 'status' => 'confirmed'],
            ['tenant_idx' => 2, 'prop_idx' => 4, 'start' => -20, 'end' => -17, 'price' => 660, 'status' => 'cancelled'],
            ['tenant_idx' => 3, 'prop_idx' => 7, 'start' => -16, 'end' => -12, 'price' => 1680, 'status' => 'completed'],
            ['tenant_idx' => 4, 'prop_idx' => 1, 'start' => -25, 'end' => -22, 'price' => 750, 'status' => 'completed'],
            ['tenant_idx' => 0, 'prop_idx' => 5, 'start' => -2, 'end' => 0, 'price' => 600, 'status' => 'confirmed'],
            ['tenant_idx' => 1, 'prop_idx' => 2, 'start' => 7, 'end' => 10, 'price' => 540, 'status' => 'pending'],
            ['tenant_idx' => 2, 'prop_idx' => 0, 'start' => -45, 'end' => -41, 'price' => 3400, 'status' => 'completed'],
            // Archived (eligible bookings: completed + end_date > 6 months ago)
            ['tenant_idx' => 0, 'prop_idx' => 0, 'start' => -450, 'end' => -445, 'price' => 3400, 'status' => 'completed'],
            ['tenant_idx' => 1, 'prop_idx' => 1, 'start' => -400, 'end' => -396, 'price' => 1000, 'status' => 'completed'],
        ];

        $bookings = [];
        foreach ($bookingData as $bd) {
            $start = (clone $now)->addDays($bd['start']);
            $end = (clone $now)->addDays($bd['end']);
            $booking = Booking::create([
                'user_id' => $tenants[$bd['tenant_idx']]->id,
                'property_id' => $properties[$bd['prop_idx']]->id,
                'start_date' => $start,
                'end_date' => $end,
                'total_price' => $bd['price'],
                'status' => $bd['status'],
            ]);
            $bookings[] = $booking;
        }

        foreach ($bookings as $booking) {
            if (in_array($booking->status, ['completed', 'confirmed'])) {
                Payment::create([
                    'booking_id' => $booking->id,
                    'amount' => $booking->total_price,
                    'payment_type' => $booking->status === 'completed' ? 'cash' : 'electronic',
                    'status' => $booking->status === 'completed' ? 'completed' : 'pending',
                    'paid_at' => $booking->status === 'completed' ? (clone $booking->start_date)->subDays(1) : null,
                ]);
            }
        }

        $maintenanceData = [
            ['prop_idx' => 0, 'tech_idx' => 0, 'problem' => 'تسريب مياه في الحمام', 'status' => 'completed'],
            ['prop_idx' => 3, 'tech_idx' => 1, 'problem' => 'عطل في مكيف الصالة', 'status' => 'in_progress'],
            ['prop_idx' => 7, 'tech_idx' => 0, 'problem' => 'لمبة الكهرباء لا تعمل', 'status' => 'assigned'],
            ['prop_idx' => 1, 'tech_idx' => null, 'problem' => 'باب الغرفة لا يقفل', 'status' => 'pending'],
            ['prop_idx' => 2, 'tech_idx' => 1, 'problem' => 'تصليح سخان المياه', 'status' => 'in_progress'],
            ['prop_idx' => 7, 'tech_idx' => 0, 'problem' => 'صيانة دورية للمكيفات', 'status' => 'completed'],
            ['prop_idx' => 4, 'tech_idx' => null, 'problem' => 'تغيير قفل الباب الرئيسي', 'status' => 'pending'],
            ['prop_idx' => 0, 'tech_idx' => null, 'problem' => 'رائحة غريبة من المجاري', 'status' => 'pending'],
        ];

        $catMap = ['plumbing' => 2, 'electricity' => 1, 'air_conditioning' => 3, 'painting' => 4, 'carpentry' => 5, 'other' => 6];
        foreach ($maintenanceData as $md) {
            $cat = fake()->randomElement(['plumbing', 'electricity', 'air_conditioning', 'painting', 'carpentry', 'other']);
            MaintenanceRequest::create([
                'property_id' => $properties[$md['prop_idx']]->id,
                'tenant_id' => $tenants[array_rand($tenants)]->id,
                'technician_id' => $md['tech_idx'] !== null ? $technicians[$md['tech_idx']]->id : null,
                'problem_description' => $md['problem'],
                'ai_category' => $cat,
                'ai_accuracy' => fake()->randomFloat(2, 0.7, 0.99),
                'category' => $cat,
                'category_id' => $catMap[$cat] ?? 6,
                'priority' => fake()->randomElement(['low', 'medium', 'high', 'urgent']),
                'status' => $md['status'],
            ]);
        }

        $messagePairs = [
            ['sender_idx' => 0, 'sender_group' => 'tenant', 'receiver_idx' => 0, 'receiver_group' => 'owner', 'msg' => 'السلام عليكم، أرغب في الاستفسار عن الفيلا المتاحة على البحر'],
            ['sender_idx' => 0, 'sender_group' => 'owner', 'receiver_idx' => 0, 'receiver_group' => 'tenant', 'msg' => 'وعليكم السلام، الفيلا متاحة للحجز حالياً'],
            ['sender_idx' => 0, 'sender_group' => 'tenant', 'receiver_idx' => 0, 'receiver_group' => 'owner', 'msg' => 'هل يوجد مواقف خاصة للسيارات؟'],
            ['sender_idx' => 0, 'sender_group' => 'owner', 'receiver_idx' => 0, 'receiver_group' => 'tenant', 'msg' => 'نعم، يوجد موقف خاص يتسع لسيارتين'],
            ['sender_idx' => 0, 'sender_group' => 'tenant', 'receiver_idx' => 0, 'receiver_group' => 'owner', 'msg' => 'كم سعر الليلة الواحدة؟'],
            ['sender_idx' => 0, 'sender_group' => 'owner', 'receiver_idx' => 0, 'receiver_group' => 'tenant', 'msg' => 'سعر الليلة 850 دينار، مع خصم 15% للحجوزات الأسبوعية'],
            ['sender_idx' => 1, 'sender_group' => 'tenant', 'receiver_idx' => 1, 'receiver_group' => 'owner', 'msg' => 'هل الشقة لا تزال متاحة؟'],
            ['sender_idx' => 1, 'sender_group' => 'owner', 'receiver_idx' => 1, 'receiver_group' => 'tenant', 'msg' => 'نعم، الشقة متاحة ويمكنك زيارتها في أي وقت'],
            ['sender_idx' => 2, 'sender_group' => 'tenant', 'receiver_idx' => 0, 'receiver_group' => 'owner', 'msg' => 'أريد تمديد فترة الحجز ليومين إضافيين'],
            ['sender_idx' => 0, 'sender_group' => 'owner', 'receiver_idx' => 2, 'receiver_group' => 'tenant', 'msg' => 'بالتأكيد، تم تمديد الحجز بنجاح'],
        ];

        $userMap = [
            'tenant' => $tenants,
            'owner' => $owners,
        ];

        foreach ($messagePairs as $i => $mp) {
            Message::create([
                'sender_id' => $userMap[$mp['sender_group']][$mp['sender_idx']]->id,
                'receiver_id' => $userMap[$mp['receiver_group']][$mp['receiver_idx']]->id,
                'message_text' => $mp['msg'],
                'sent_at' => (clone $now)->subHours(48 - $i * 2),
            ]);
        }

        $reviewData = [
            ['tenant_idx' => 0, 'prop_idx' => 0, 'stars' => 5, 'comment' => 'تجربة رائعة جداً، الفيلا جميلة ونظيفة والموقع ممتاز'],
            ['tenant_idx' => 0, 'prop_idx' => 1, 'stars' => 4, 'comment' => 'شقة مريحة وقريبة من الخدمات. أنصح بها'],
            ['tenant_idx' => 1, 'prop_idx' => 3, 'stars' => 5, 'comment' => 'فيلا رائعة، الحديقة جميلة والمسبح نظيف'],
            ['tenant_idx' => 1, 'prop_idx' => 0, 'stars' => 4, 'comment' => 'فيلا ممتازة، لكن يحتاج تحسين في سرعة الإنترنت'],
            ['tenant_idx' => 2, 'prop_idx' => 2, 'stars' => 5, 'comment' => 'استوديو مريح جداً والإطلالة رائعة'],
            ['tenant_idx' => 4, 'prop_idx' => 0, 'stars' => 5, 'comment' => 'أفضل فيلا حجزتها في طرابلس'],
            ['tenant_idx' => 3, 'prop_idx' => 7, 'stars' => 4, 'comment' => 'شقة دوبلكس جميلة وفاخرة'],
            ['tenant_idx' => 4, 'prop_idx' => 1, 'stars' => 3, 'comment' => 'الشقة مقبولة ولكن تحتاج إلى بعض الصيانة'],
        ];
        foreach ($reviewData as $rd) {
            Review::create([
                'user_id' => $tenants[$rd['tenant_idx']]->id,
                'property_id' => $properties[$rd['prop_idx']]->id,
                'stars' => $rd['stars'],
                'comment' => $rd['comment'],
            ]);
        }

        $favorites = [
            ['tenant_idx' => 0, 'prop_idx' => 0],
            ['tenant_idx' => 0, 'prop_idx' => 3],
            ['tenant_idx' => 1, 'prop_idx' => 7],
            ['tenant_idx' => 2, 'prop_idx' => 1],
            ['tenant_idx' => 3, 'prop_idx' => 7],
            ['tenant_idx' => 4, 'prop_idx' => 0],
            ['tenant_idx' => 4, 'prop_idx' => 2],
        ];
        foreach ($favorites as $f) {
            Favorite::create([
                'user_id' => $tenants[$f['tenant_idx']]->id,
                'property_id' => $properties[$f['prop_idx']]->id,
            ]);
        }

        $notifItems = [
            ['user_idx' => 0, 'group' => 'tenant', 'title' => 'تأكيد الحجز', 'content' => 'تم تأكيد حجزك في فيلا أمام البحر'],
            ['user_idx' => 1, 'group' => 'owner', 'title' => 'تم الدفع', 'content' => 'تم استلام دفعة حجز العقار رقم 3'],
            ['user_idx' => 0, 'group' => 'technician', 'title' => 'تعيين فني', 'content' => 'تم تعيينك في طلب صيانة رقم 1'],
            ['user_idx' => 0, 'group' => 'admin', 'title' => 'ترحيب', 'content' => 'مرحباً بك في نظام مسكن'],
            ['user_idx' => 0, 'group' => 'owner', 'title' => 'حجز جديد', 'content' => 'تم إجراء حجز جديد على عقارك'],
            ['user_idx' => 1, 'group' => 'technician', 'title' => 'طلب صيانة', 'content' => 'تم تعيينك في طلب صيانة جديد'],
            ['user_idx' => 0, 'group' => 'admin', 'title' => 'تحديث النظام', 'content' => 'تم تحديث النظام إلى الإصدار الأحدث'],
        ];

        $groupMap = [
            'admin' => [$admin],
            'owner' => $owners,
            'tenant' => $tenants,
            'technician' => $technicians,
        ];

        foreach ($notifItems as $ni) {
            $users = $groupMap[$ni['group']];
            Notification::create([
                'user_id' => $users[$ni['user_idx']]->id,
                'title' => $ni['title'],
                'content' => $ni['content'],
            ]);
        }

        // Complaints stored in messages table
        $complaintData = [
            ['tenant_idx' => 0, 'text' => 'الضجيج من الجيران', 'status' => 'resolved'],
            ['tenant_idx' => 1, 'text' => 'انقطاع التيار الكهربائي', 'status' => 'pending'],
            ['tenant_idx' => 2, 'text' => 'مشكلة في عداد المياه', 'status' => 'in_review'],
        ];
        foreach ($complaintData as $cd) {
            Message::create([
                'sender_id' => $tenants[$cd['tenant_idx']]->id,
                'receiver_id' => $admin->id,
                'message_text' => $cd['text'],
                'type' => 'complaint',
                'complaint_status' => $cd['status'],
                'sent_at' => now(),
                'admin_response' => $cd['status'] === 'resolved' ? 'تمت معالجة الشكوى' : null,
                'responded_by' => $cd['status'] === 'resolved' ? $admin->id : null,
                'resolved_at' => $cd['status'] === 'resolved' ? now() : null,
            ]);
        }
    }
}
