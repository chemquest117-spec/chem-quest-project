<?php

namespace Database\Seeders;

use App\Models\Question;
use App\Models\Stage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Collection;

class QuestionSeeder extends Seeder
{
     public function run(): void
     {
          // $stages = Stage::orderBy('order')->get();

          // $this->seedOldQuestions($stages);
          
          // Seed the newly parsed JSON questions
          $this->seedJsonQuestions();
     }

     private function seedStage(int $stageId, array $questions): void
     {
          foreach ($questions as $q) {
               Question::firstOrCreate(
                    ['question_text' => $q[0], 'stage_id' => $stageId],
                    [
                         'option_a' => $q[1],
                         'option_b' => $q[2],
                         'option_c' => $q[3],
                         'option_d' => $q[4],
                         'correct_answer' => $q[5],
                         'difficulty' => $q[6],
                         'question_text_ar' => $q[7],
                         'option_a_ar' => $q[8],
                         'option_b_ar' => $q[9],
                         'option_c_ar' => $q[10],
                         'option_d_ar' => $q[11],
                         'correct_answer_ar' => $q[12],
                         'difficulty_ar' => $q[13],
                         'type' => 'mcq',
                    ]
               );
          }
     }

     private function seedJsonQuestions(): void
     {
          $jsonPath = database_path('seeders/questions.json');
          if (!File::exists($jsonPath)) {
               $this->command->warn('questions.json not found. Did you run process_questions.py?');
               return;
          }

          $data = json_decode(File::get($jsonPath), true);
          if (!$data) {
               $this->command->warn('questions.json is invalid.');
               return;
          }

          // Fetch the new LO stages
          $loStages = [
               'LO1' => Stage::where('title', 'LIKE', 'LO1%')->first()->id ?? null,
               'LO2' => Stage::where('title', 'LIKE', 'LO2%')->first()->id ?? null,
               'LO3' => Stage::where('title', 'LIKE', 'LO3%')->first()->id ?? null,
               'LO4' => Stage::where('title', 'LIKE', 'LO4%')->first()->id ?? null,
               'LO5' => Stage::where('title', 'LIKE', 'LO5%')->first()->id ?? null,
          ];

          foreach ($data as $q) {
               $loId = $loStages[$q['lo']] ?? null;
               if (!$loId) continue;

               // Parse difficulty from text if provided (not fully structured in text, default to medium)
               $difficulty = 'medium';
               if (str_contains(strtolower($q['text']), '(hard)')) $difficulty = 'hard';
               elseif (str_contains(strtolower($q['text']), '(easy)')) $difficulty = 'easy';
               
               $difficulty_ar = match($difficulty) {
                   'easy' => 'سهل',
                   'medium' => 'متوسط',
                   'hard' => 'صعب',
                   default => 'متوسط',
               };

               $correctAnswer = null;
               if ($q['type'] === 'mcq' && $q['correct_answer'] && in_array(strtolower($q['correct_answer']), ['a', 'b', 'c', 'd'])) {
                   $correctAnswer = strtolower($q['correct_answer']);
               } elseif ($q['type'] === 'mcq') {
                   // Default fallback if parsing missed it just in case
                   $correctAnswer = 'a';
               }

               Question::firstOrCreate(
                    [
                         'question_text' => $q['text'],
                         'stage_id' => $loId,
                    ],
                    [
                         'type' => $q['type'],
                         'option_a' => $q['options'][0] ?? null,
                         'option_b' => $q['options'][1] ?? null,
                         'option_c' => $q['options'][2] ?? null,
                         'option_d' => $q['options'][3] ?? null,
                         'correct_answer' => $correctAnswer,
                         'explanation' => $q['explanation'] ?? null,
                         'expected_answer' => $q['expected_answer'] ?? null,
                         'difficulty' => $difficulty,
                         'difficulty_ar' => $difficulty_ar,
                         // To enable Arabic bilingual seamlessly, we copy the English till translation happens
                         'question_text_ar' => $q['text'],
                         'option_a_ar' => $q['options'][0] ?? null,
                         'option_b_ar' => $q['options'][1] ?? null,
                         'option_c_ar' => $q['options'][2] ?? null,
                         'option_d_ar' => $q['options'][3] ?? null,
                         'explanation_ar' => $q['explanation'] ?? null,
                         'expected_answer_ar' => $q['expected_answer'] ?? null,
                    ]
               );
          }
     }



     private function seedOldQuestions(Collection $stages): void
     {
          // Stage 1: Atomic Structure
          $this->seedStage($stages[0]->id, [
               ['What is the atomic number of Carbon?', '4', '6', '8', '12', 'b', 'easy', 'ما هو العدد الذري للكربون؟', '4', '6', '8', '12', 'b', 'سهل'],
               ['Which subatomic particle has a negative charge?', 'Proton', 'Neutron', 'Electron', 'Photon', 'c', 'easy', 'أي جسيم دون ذري له شحنة سالبة؟', 'بروتون', 'نيوترون', 'إلكترون', 'فوتون', 'c', 'سهل'],
               ['The nucleus of an atom contains:', 'Electrons only', 'Protons and neutrons', 'Electrons and protons', 'Neutrons only', 'b', 'easy', 'نواة الذرة تحتوي على:', 'إلكترونات فقط', 'بروتونات ونيوترونات', 'إلكترونات وبروتونات', 'نيوترونات فقط', 'b', 'سهل'],
               ['What is the mass number of an atom?', 'Number of protons', 'Number of electrons', 'Protons + Neutrons', 'Protons + Electrons', 'c', 'medium', 'ما هو العدد الكتلي للذرة؟', 'عدد البروتونات', 'عدد الإلكترونات', 'البروتونات + النيوترونات', 'البروتونات + الإلكترونات', 'c', 'متوسط'],
               ['Isotopes differ in the number of:', 'Protons', 'Electrons', 'Neutrons', 'All subatomic particles', 'c', 'medium', 'تختلف النظائر في عدد:', 'البروتونات', 'الإلكترونات', 'النيوترونات', 'جميع الجسيمات دون الذرية', 'c', 'متوسط'],
               ['How many electrons can the first shell hold?', '1', '2', '8', '18', 'b', 'easy', 'كم عدد الإلكترونات التي يمكن أن يستوعبها المدار الأول؟', '1', '2', '8', '18', 'b', 'سهل'],
               ['What is the electron configuration of Sodium (Na)?', '2,8,2', '2,8,1', '2,7,2', '2,8,3', 'b', 'medium', 'ما هو التوزيع الإلكتروني للصوديوم (Na)؟', '2,8,2', '2,8,1', '2,7,2', '2,8,3', 'b', 'متوسط'],
               ['Which element has the atomic number 8?', 'Nitrogen', 'Carbon', 'Oxygen', 'Fluorine', 'c', 'easy', 'ما هو العنصر الذي عدده الذري 8؟', 'نيتروجين', 'كربون', 'أكسجين', 'فلور', 'c', 'سهل'],
               ['Rutherford\'s gold foil experiment proved:', 'Atoms are solid', 'Atoms have a dense positive nucleus', 'Electrons orbit in fixed paths', 'Atoms are mostly empty space with a nucleus', 'd', 'hard', 'أثبتت تجربة رذرفورد لرقاقة الذهب:', 'الذرات صلبة', 'الذرات لها نواة موجبة وكثيفة', 'الإلكترونات تدور في مسارات ثابتة', 'الذرات في الغالب مساحة فارغة تحتوي على نواة', 'd', 'صعب'],
               ['The maximum electrons in the third shell is:', '2', '8', '18', '32', 'c', 'hard', 'الحد الأقصى للإلكترونات في المدار الثالث هو:', '2', '8', '18', '32', 'c', 'صعب'],
          ]);

          // Stage 2: Chemical Bonding
          $this->seedStage($stages[1]->id, [
               ['Ionic bonds form between:', 'Two metals', 'Two non-metals', 'A metal and a non-metal', 'Noble gases', 'c', 'easy', 'تتكون الروابط الأيونية بين:', 'فلزين', 'لافلزين', 'فلز ولافلز', 'الغازات النبيلة', 'c', 'سهل'],
               ['In a covalent bond, electrons are:', 'Transferred', 'Shared', 'Destroyed', 'Created', 'b', 'easy', 'في الرابطة التساهمية، الإلكترونات:', 'تُنتقل', 'تُشارك', 'تُدمر', 'تُخلق', 'b', 'سهل'],
               ['NaCl is an example of:', 'Covalent bond', 'Ionic bond', 'Metallic bond', 'Hydrogen bond', 'b', 'easy', 'كلوريد الصوديوم (NaCl) هو مثال على:', 'رابطة تساهمية', 'رابطة أيونية', 'رابطة معدنية', 'رابطة هيدروجينية', 'b', 'سهل'],
               ['How many covalent bonds can Carbon form?', '1', '2', '3', '4', 'd', 'medium', 'كم عدد الروابط التساهمية التي يمكن أن يشكلها الكربون؟', '1', '2', '3', '4', 'd', 'متوسط'],
               ['Electronegativity increases across a period because:', 'Atomic radius increases', 'Nuclear charge increases', 'Electron shielding increases', 'Electrons decrease', 'b', 'medium', 'تزداد الكهرسلبية عبر الدورة بسبب:', 'زيادة نصف القطر الذري', 'زيادة الشحنة النووية', 'زيادة حجب الإلكترونات', 'نقصان الإلكترونات', 'b', 'متوسط'],
               ['Which molecule is non-polar?', 'H₂O', 'CO₂', 'HCl', 'NH₃', 'b', 'medium', 'أي جزيء غير قطبي؟', 'H₂O', 'CO₂', 'HCl', 'NH₃', 'b', 'متوسط'],
               ['Metallic bonds involve:', 'Sharing of electron pairs', 'Transfer of electrons', 'A sea of delocalized electrons', 'Van der Waals forces', 'c', 'medium', 'الروابط المعدنية تشمل:', 'مشاركة أزواج الإلكترونات', 'نقل الإلكترونات', 'بحر من الإلكترونات غير المتمركزة', 'قوى فان دير فالس', 'c', 'متوسط'],
               ['Diamond is hard because of:', 'Ionic bonds', 'Strong covalent bonds in a giant structure', 'Metallic bonds', 'Weak intermolecular forces', 'b', 'hard', 'الماس صلب بسبب:', 'الروابط الأيونية', 'روابط تساهمية قوية في هيكل ضخم', 'الروابط المعدنية', 'قوى بين جزيئية ضعيفة', 'b', 'صعب'],
               ['The shape of a methane (CH₄) molecule is:', 'Linear', 'Trigonal planar', 'Tetrahedral', 'Octahedral', 'c', 'hard', 'شكل جزيء الميثان (CH₄) هو:', 'خطي', 'مثلث مسطح', 'رباعي الأوجه', 'ثماني الأوجه', 'c', 'صعب'],
               ['Which has the highest melting point?', 'NaCl', 'CO₂', 'H₂O', 'O₂', 'a', 'hard', 'أي منها له أعلى درجة انصهار؟', 'NaCl', 'CO₂', 'H₂O', 'O₂', 'a', 'صعب'],
          ]);

          // Stage 3: Reactions & Equations
          $this->seedStage($stages[2]->id, [
               ['Balance: _ H₂ + _ O₂ → _ H₂O', '1,1,1', '2,1,2', '2,2,2', '1,2,2', 'b', 'easy', 'وازن: _ H₂ + _ O₂ ← _ H₂O', '1,1,1', '2,1,2', '2,2,2', '1,2,2', 'b', 'سهل'],
               ['A combustion reaction always produces:', 'Water only', 'CO₂ only', 'CO₂ and H₂O', 'Oxygen', 'c', 'easy', 'تفاعل الاحتراق ينتج دائماً:', 'ماء فقط', 'ثاني أكسيد الكربون فقط', 'ثاني أكسيد الكربون وماء', 'أكسجين', 'c', 'سهل'],
               ['Which is a decomposition reaction?', '2H₂ + O₂ → 2H₂O', '2H₂O → 2H₂ + O₂', 'NaOH + HCl → NaCl + H₂O', 'Zn + CuSO₄ → ZnSO₄ + Cu', 'b', 'medium', 'أي مما يلي هو تفاعل تحلل؟', '2H₂ + O₂ → 2H₂O', '2H₂O → 2H₂ + O₂', 'NaOH + HCl → NaCl + H₂O', 'Zn + CuSO₄ → ZnSO₄ + Cu', 'b', 'متوسط'],
               ['In an exothermic reaction, energy is:', 'Absorbed', 'Released', 'Neither', 'Destroyed', 'b', 'easy', 'في التفاعل الطارد للحرارة، الطاقة:', 'تُمتص', 'تنبعث', 'لا شيء', 'تُدمر', 'b', 'سهل'],
               ['What type of reaction is Zn + CuSO₄ → ZnSO₄ + Cu?', 'Decomposition', 'Synthesis', 'Single displacement', 'Double displacement', 'c', 'medium', 'ما نوع تفاعل Zn + CuSO₄ ← ZnSO₄ + Cu؟', 'تحلل', 'تكوين', 'إحلال بسيط', 'إحلال مزدوج', 'c', 'متوسط'],
               ['The law of conservation of mass states:', 'Mass can be created', 'Mass can be destroyed', 'Mass is neither created nor destroyed', 'Mass always increases', 'c', 'easy', 'قانون حفظ الكتلة ينص على:', 'يمكن خلق الكتلة', 'يمكن تدمير الكتلة', 'الكتلة لا تفنى ولا تستحدث من العدم', 'الكتلة تزداد دائماً', 'c', 'سهل'],
               ['Catalysts work by:', 'Increasing temperature', 'Lowering activation energy', 'Adding more reactants', 'Changing products', 'b', 'medium', 'تعمل المحفزات عن طريق:', 'زيادة درجة الحرارة', 'خفض طاقة التنشيط', 'إضافة المزيد من المتفاعلات', 'تغيير النواتج', 'b', 'متوسط'],
               ['The molar mass of H₂O is approximately:', '16 g/mol', '18 g/mol', '20 g/mol', '2 g/mol', 'b', 'medium', 'الكتلة المولية للماء (H₂O) تقريباً:', '16 g/mol', '18 g/mol', '20 g/mol', '2 g/mol', 'b', 'متوسط'],
               ['How many moles of O₂ are needed to react with 4 moles of H₂?', '1', '2', '3', '4', 'b', 'hard', 'كم مول من O₂ يلزم للتفاعل مع 4 مول من H₂؟', '1', '2', '3', '4', 'b', 'صعب'],
               ['An endothermic reaction graph shows:', 'Products lower than reactants', 'Products higher than reactants', 'Equal energy levels', 'No activation energy', 'b', 'hard', 'يوضح الرسم البياني للتفاعل الماص للحرارة:', 'النواتج أقل من المتفاعلات', 'النواتج أعلى من المتفاعلات', 'مستويات طاقة متساوية', 'لا توجد طاقة تنشيط', 'b', 'صعب'],
          ]);

          // Stage 4: Acids, Bases & pH
          $this->seedStage($stages[3]->id, [
               ['A pH of 3 indicates:', 'Strong base', 'Weak base', 'Strong acid', 'Neutral', 'c', 'easy', 'درجة الحموضة 3 تشير إلى:', 'قاعدة قوية', 'قاعدة ضعيفة', 'حمض قوي', 'متعادل', 'c', 'سهل'],
               ['Which is a strong acid?', 'CH₃COOH', 'HCl', 'H₂CO₃', 'C₆H₈O₇', 'b', 'easy', 'أي مما يلي حمض قوي؟', 'CH₃COOH', 'HCl', 'H₂CO₃', 'C₆H₈O₇', 'b', 'سهل'],
               ['Bases taste:', 'Sour', 'Bitter', 'Sweet', 'Salty', 'b', 'easy', 'مذاق القواعد:', 'حامض', 'مر', 'حلو', 'مالح', 'b', 'سهل'],
               ['Neutralization produces:', 'Acid + Base', 'Salt + Water', 'Gas + Water', 'Metal + Salt', 'b', 'easy', 'تفاعل التعادل ينتج:', 'حمض + قاعدة', 'ملح + ماء', 'غاز + ماء', 'فلز + ملح', 'b', 'سهل'],
               ['The pH of pure water is:', '0', '5', '7', '14', 'c', 'easy', 'درجة الحموضة للماء النقي هي:', '0', '5', '7', '14', 'c', 'سهل'],
               ['Which indicator turns pink in a base?', 'Methyl orange', 'Litmus', 'Phenolphthalein', 'Bromothymol blue', 'c', 'medium', 'أي كاشف يتحول إلى اللون الوردي في القاعدة؟', 'برتقالي الميثيل', 'تباع الشمس', 'فينول فثالين', 'أزرق بروموثيمول', 'c', 'متوسط'],
               ['Acids produce which ion in solution?', 'OH⁻', 'H⁺', 'Na⁺', 'Cl⁻', 'b', 'medium', 'تنتج الأحماض أي أيون في المحلول؟', 'OH⁻', 'H⁺', 'Na⁺', 'Cl⁻', 'b', 'متوسط'],
               ['A buffer solution:', 'Changes pH rapidly', 'Resists changes in pH', 'Is always neutral', 'Contains only acid', 'b', 'hard', 'المحلول المنظم:', 'يغير درجة الحموضة بسرعة', 'يقاوم التغيرات في درجة الحموضة', 'يكون متعادلاً دائماً', 'يحتوي فقط على حمض', 'b', 'صعب'],
               ['Sulfuric acid (H₂SO₄) is:', 'Monoprotic', 'Diprotic', 'Triprotic', 'Non-acidic', 'b', 'hard', 'حمض الكبريتيك (H₂SO₄) هو:', 'أحادي البروتون', 'ثنائي البروتون', 'ثلاثي البروتون', 'غير حمضي', 'b', 'صعب'],
               ['If pH = 2, the pOH is:', '2', '7', '12', '14', 'c', 'hard', 'إذا كانت درجة الحموضة (pH) = 2، فإن pOH هو:', '2', '7', '12', '14', 'c', 'صعب'],
          ]);

          // Stage 5: Organic Chemistry
          $this->seedStage($stages[4]->id, [
               ['Organic chemistry is the study of:', 'All elements', 'Carbon compounds', 'Metals', 'Noble gases', 'b', 'easy', 'الكيمياء العضوية هي دراسة:', 'جميع العناصر', 'مركبات الكربون', 'الفلزات', 'الغازات النبيلة', 'b', 'سهل'],
               ['The simplest hydrocarbon is:', 'Ethane', 'Methane', 'Propane', 'Butane', 'b', 'easy', 'أبسط هيدروكربون هو:', 'إيثان', 'ميثان', 'بروبان', 'بيوتان', 'b', 'سهل'],
               ['Alkanes have what type of bonds?', 'Double', 'Triple', 'Single', 'Ionic', 'c', 'easy', 'ما نوع الروابط في الألكانات؟', 'مزدوجة', 'ثلاثية', 'أحادية', 'أيونية', 'c', 'سهل'],
               ['The general formula for alkanes is:', 'CnH2n', 'CnH2n+2', 'CnH2n-2', 'CnHn', 'b', 'medium', 'الصيغة العامة للألكانات هي:', 'CnH2n', 'CnH2n+2', 'CnH2n-2', 'CnHn', 'b', 'متوسط'],
               ['Ethanol belongs to which functional group?', 'Aldehyde', 'Ketone', 'Alcohol', 'Carboxylic acid', 'c', 'medium', 'ينتمي الإيثانول إلى أي مجموعة وظيفية؟', 'ألدهيد', 'كيتون', 'كحول', 'حمض كربوكسيلي', 'c', 'متوسط'],
               ['Isomers have the same:', 'Structure', 'Molecular formula', 'Physical properties', 'Functional groups', 'b', 'medium', 'تمتلك المتشكلات نفس:', 'الهيكل', 'الصيغة الجزيئية', 'الخصائص الفيزيائية', 'المجموعات الوظيفية', 'b', 'متوسط'],
               ['The IUPAC name for CH₃CH₂CH₂CH₃ is:', 'Methane', 'Ethane', 'Propane', 'Butane', 'd', 'medium', 'اسم IUPAC لـ CH₃CH₂CH₂CH₃ هو:', 'ميثان', 'إيثان', 'بروبان', 'بيوتان', 'd', 'متوسط'],
               ['Unsaturated hydrocarbons contain:', 'Only single bonds', 'Double or triple bonds', 'Ionic bonds', 'No carbon', 'b', 'medium', 'الهيدروكربونات غير المشبعة تحتوي على:', 'روابط أحادية فقط', 'روابط مزدوجة أو ثلاثية', 'روابط أيونية', 'لا تحتوي على كربون', 'b', 'متوسط'],
               ['Polymerization is:', 'Breaking large molecules', 'Joining small molecules into large chains', 'A combustion reaction', 'A neutralization reaction', 'b', 'hard', 'البلمرة هي:', 'تكسير الجزيئات الكبيرة', 'ربط جزيئات صغيرة في سلاسل كبيرة', 'تفاعل احتراق', 'تفاعل تعادل', 'b', 'صعب'],
               ['The functional group -COOH is:', 'Alcohol', 'Amine', 'Carboxylic acid', 'Ester', 'c', 'hard', 'المجموعة الوظيفية -COOH هي:', 'كحول', 'أمين', 'حمض كربوكسيلي', 'إستر', 'c', 'صعب'],
          ]);
     }
}
