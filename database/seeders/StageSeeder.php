<?php

namespace Database\Seeders;

use App\Models\Stage;
use Illuminate\Database\Seeder;

class StageSeeder extends Seeder
{
     public function run(): void
     {
          $stages = [
               [
                    'title' => 'Atomic Structure',
                    'description' => 'Explore the building blocks of matter — atoms, protons, neutrons, and electrons. Learn about atomic number, mass number, and electron configuration.',
                    'order' => 1,
                    'time_limit_minutes' => 10,
                    'passing_percentage' => 75,
                    'points_reward' => 100,
                    'title_ar' => 'البنية الذرية',
                    'description_ar' => 'استكشف اللبنات الأساسية للمادة — الذرات، البروتونات، النيوترونات، والإلكترونات. تعرف على العدد الذري، العدد الكتلي، والتوزيع الإلكتروني.',
                    
               ],
               [
                    'title' => 'Chemical Bonding',
                    'description' => 'Understand how atoms bond together through ionic, covalent, and metallic bonds. Explore electronegativity and molecular geometry.',
                    'order' => 2,
                    'time_limit_minutes' => 12,
                    'passing_percentage' => 75,
                    'points_reward' => 120,
                    'title_ar' => 'الروابط الكيميائية',
                    'description_ar' => 'افهم كيف ترتبط الذرات معاً من خلال الروابط الأيونية، التساهمية، والمعدنية. استكشف الكهرسلبية والهندسة الجزيئية.',
                    
               ],
               [
                    'title' => 'Reactions & Equations',
                    'description' => 'Master chemical reactions, balancing equations, types of reactions, and stoichiometry fundamentals.',
                    'order' => 3,
                    'time_limit_minutes' => 15,
                    'passing_percentage' => 75,
                    'points_reward' => 140,
                    'title_ar' => 'التفاعلات والمعادلات',
                    'description_ar' => 'أتقن التفاعلات الكيميائية، موازنة المعادلات، أنواع التفاعلات، وأساسيات الحسابات الكيميائية.',
                    
               ],
               [
                    'title' => 'Acids, Bases & pH',
                    'description' => 'Dive into acids, bases, pH scale, neutralization reactions, and buffer solutions.',
                    'order' => 4,
                    'time_limit_minutes' => 12,
                    'passing_percentage' => 75,
                    'points_reward' => 150,
                    'title_ar' => 'الأحماض والقواعد والرقم الهيدروجيني',
                    'description_ar' => 'تعمق في الأحماض، القواعد، مقياس الرقم الهيدروجيني، تفاعلات التعادل، والمحاليل المنظمة.',
                    
               ],
               [
                    'title' => 'Organic Chemistry',
                    'description' => 'Introduction to carbon compounds, hydrocarbons, functional groups, and naming conventions in organic chemistry.',
                    'order' => 5,
                    'time_limit_minutes' => 15,
                    'passing_percentage' => 75,
                    'points_reward' => 200,
                    'title_ar' => 'الكيمياء العضوية',
                    'description_ar' => 'مقدمة لمركبات الكربون، الهيدروكربونات، المجموعات الوظيفية، وقواعد التسمية في الكيمياء العضوية.',
                    
               ],
          ];

          foreach ($stages as $stage) {
               Stage::create($stage);
          }
     }
}
