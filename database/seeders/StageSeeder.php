<?php

namespace Database\Seeders;

use App\Models\Stage;
use Illuminate\Database\Seeder;

class StageSeeder extends Seeder
{
    public function run(): void
    {
        $stages = [
            // [
            //      'title' => 'Atomic Structure',
            //      'description' => 'Explore the building blocks of matter — atoms, protons, neutrons, and electrons. Learn about atomic number, mass number, and electron configuration.',
            //      'order' => 1,
            //      'time_limit_minutes' => 10,
            //      'passing_percentage' => 75,
            //      'points_reward' => 100,
            //      'title_ar' => 'البنية الذرية',
            //      'description_ar' => 'استكشف اللبنات الأساسية للمادة — الذرات، البروتونات، النيوترونات، والإلكترونات. تعرف على العدد الذري، العدد الكتلي، والتوزيع الإلكتروني.',
            // ],
            // [
            //      'title' => 'Chemical Bonding',
            //      'description' => 'Understand how atoms bond together through ionic, covalent, and metallic bonds. Explore electronegativity and molecular geometry.',
            //      'order' => 2,
            //      'time_limit_minutes' => 12,
            //      'passing_percentage' => 75,
            //      'points_reward' => 120,
            //      'title_ar' => 'الروابط الكيميائية',
            //      'description_ar' => 'افهم كيف ترتبط الذرات معاً من خلال الروابط الأيونية، التساهمية، والمعدنية. استكشف الكهرسلبية والهندسة الجزيئية.',
            // ],
            // [
            //      'title' => 'Reactions & Equations',
            //      'description' => 'Master chemical reactions, balancing equations, types of reactions, and stoichiometry fundamentals.',
            //      'order' => 3,
            //      'time_limit_minutes' => 15,
            //      'passing_percentage' => 75,
            //      'points_reward' => 140,
            //      'title_ar' => 'التفاعلات والمعادلات',
            //      'description_ar' => 'أتقن التفاعلات الكيميائية، موازنة المعادلات، أنواع التفاعلات، وأساسيات الحسابات الكيميائية.',
            // ],
            // [
            //      'title' => 'Acids, Bases & pH',
            //      'description' => 'Dive into acids, bases, pH scale, neutralization reactions, and buffer solutions.',
            //      'order' => 4,
            //      'time_limit_minutes' => 12,
            //      'passing_percentage' => 75,
            //      'points_reward' => 150,
            //      'title_ar' => 'الأحماض والقواعد والرقم الهيدروجيني',
            //      'description_ar' => 'تعمق في الأحماض، القواعد، مقياس الرقم الهيدروجيني، تفاعلات التعادل، والمحاليل المنظمة.',
            // ],
            // [
            //      'title' => 'Organic Chemistry',
            //      'description' => 'Introduction to carbon compounds, hydrocarbons, functional groups, and naming conventions in organic chemistry.',
            //      'order' => 5,
            //      'time_limit_minutes' => 15,
            //      'passing_percentage' => 75,
            //      'points_reward' => 200,
            //      'title_ar' => 'الكيمياء العضوية',
            //      'description_ar' => 'مقدمة لمركبات الكربون، الهيدروكربونات، المجموعات الوظيفية، وقواعد التسمية في الكيمياء العضوية.',
            // ],
            // ── New LO Stages ──
            [
                'title' => 'LO1: Redox & Electrochemistry',
                'description' => 'Master oxidation numbers, redox reactions, balancing in acidic/basic solutions, electrolysis, Faraday\'s laws, electroplating, and corrosion prevention.',
                'order' => 6,
                'time_limit_minutes' => 20,
                'passing_percentage' => 70,
                'points_reward' => 250,
                'title_ar' => 'ن.ت.1: الأكسدة والاختزال والكيمياء الكهربائية',
                'description_ar' => 'أتقن أعداد الأكسدة، تفاعلات الأكسدة والاختزال، الموازنة في المحاليل الحمضية/القاعدية، التحليل الكهربائي، قوانين فاراداي، الطلاء الكهربائي، ومنع التآكل.',
            ],
            [
                'title' => 'LO2: Galvanic Cells & Conductivity',
                'description' => 'Explore galvanic vs electrolytic cells, standard electrode potentials, Nernst equation, battery chemistry, SHE, concentration cells, and electrolyte conductivity.',
                'order' => 7,
                'time_limit_minutes' => 20,
                'passing_percentage' => 70,
                'points_reward' => 250,
                'title_ar' => 'ن.ت.2: الخلايا الجلفانية والتوصيلية',
                'description_ar' => 'استكشف الخلايا الجلفانية مقابل خلايا التحليل الكهربائي، جهود الأقطاب القياسية، معادلة نيرنست، كيمياء البطاريات، قطب الهيدروجين القياسي، خلايا التركيز، وتوصيلية المحاليل الإلكتروليتية.',
            ],
            [
                'title' => 'LO3: Organic Nomenclature & Reactions',
                'description' => 'Learn IUPAC naming, constitutional isomers, functional groups, hybridization, electrophilic addition, aromatic chemistry, and alcohol oxidation.',
                'order' => 8,
                'time_limit_minutes' => 20,
                'passing_percentage' => 70,
                'points_reward' => 280,
                'title_ar' => 'ن.ت.3: تسمية المركبات العضوية وتفاعلاتها',
                'description_ar' => 'تعلم تسمية IUPAC، المتشكلات البنائية، المجموعات الوظيفية، التهجين، الإضافة الإلكتروفيلية، الكيمياء العطرية، وأكسدة الكحولات.',
            ],
            [
                'title' => 'LO4: Hydrocarbons & Petrochemistry',
                'description' => 'Understand petroleum fractionation, combustion, cracking (thermal vs catalytic), zeolite catalysts, environmental impacts, and Friedel-Crafts reactions.',
                'order' => 9,
                'time_limit_minutes' => 20,
                'passing_percentage' => 70,
                'points_reward' => 280,
                'title_ar' => 'ن.ت.4: الهيدروكربونات والبتروكيمياء',
                'description_ar' => 'افهم التقطير التجزيئي للنفط، الاحتراق، التكسير (الحراري مقابل التحفيزي)، محفزات الزيوليت، التأثيرات البيئية، وتفاعلات فريدل-كرافتس.',
            ],
            [
                'title' => 'LO5: Functional Groups, Electrolysis & Isomerism',
                'description' => 'Master organic functional groups (esters, ketones, alcohols), electrolysis processes, Faraday\'s law calculations, structural & geometric isomerism, chirality, corrosion prevention, and electrochemical cell potentials.',
                'order' => 10,
                'time_limit_minutes' => 25,
                'passing_percentage' => 70,
                'points_reward' => 300,
                'title_ar' => 'ن.ت.5: المجموعات الوظيفية والتحليل الكهربائي والتشكل',
                'description_ar' => 'أتقن المجموعات الوظيفية العضوية (الإسترات، الكيتونات، الكحولات)، عمليات التحليل الكهربائي، حسابات قانون فاراداي، التشكل البنائي والهندسي، الكيرالية، منع التآكل، وجهود الخلايا الكهروكيميائية.',
            ],
        ];

        foreach ($stages as $stage) {
            Stage::updateOrCreate(
                ['order' => $stage['order']],
                $stage
            );
        }
    }
}
