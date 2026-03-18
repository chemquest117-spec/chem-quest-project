<?php

namespace App\Services;

use App\Models\Question;
use App\Models\Stage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIQuestionService
{
     /**
      * Generate questions for a stage — uses OpenAI if key exists, otherwise demo mode.
      */
     public function generateQuestions(Stage $stage, int $count = 5): array
     {
          $apiKey = config('services.openai.key');

          if ($apiKey) {
               return $this->generateWithOpenAI($stage, $count, $apiKey);
          }

          return $this->generateDemo($stage, $count);
     }

     /**
      * Generate questions using OpenAI GPT-4.
      */
     private function generateWithOpenAI(Stage $stage, int $count, string $apiKey): array
     {
          try {
               $response = Http::withToken($apiKey)
                    ->timeout(30)
                    ->post('https://api.openai.com/v1/chat/completions', [
                         'model' => 'gpt-4',
                         'messages' => [
                              [
                                   'role' => 'system',
                                   'content' => "You are a chemistry teacher. Generate exactly {$count} multiple-choice questions."
                              ],
                              [
                                   'role' => 'user',
                                   'content' => "Topic: {$stage->title}. Generate {$count} chemistry MCQ questions. 
                            Return ONLY a JSON array, no markdown. Each object must have:
                            {\"question_text\": \"...\", \"option_a\": \"...\", \"option_b\": \"...\", \"option_c\": \"...\", \"option_d\": \"...\", \"correct_answer\": \"a|b|c|d\", \"difficulty\": \"easy|medium|hard\"}"
                              ]
                         ],
                         'temperature' => 0.8,
                    ]);

               $content = $response->json('choices.0.message.content');
               $questions = json_decode($content, true);

               if (!is_array($questions)) {
                    throw new \Exception('Invalid JSON response from AI');
               }

               return $this->saveQuestions($stage, $questions);
          } catch (\Exception $e) {
               Log::error('OpenAI question generation failed', ['error' => $e->getMessage()]);
               // Fallback to demo mode
               return $this->generateDemo($stage, $count);
          }
     }

     /**
      * Generate realistic demo questions from a curated chemistry question bank.
      * Simulates AI generation with a 1-2s delay for realism.
      */
     private function generateDemo(Stage $stage, int $count): array
     {
          $bank = $this->getQuestionBank();
          $topic = strtolower($stage->title);

          // Match the best question pool to the stage topic
          $pool = $bank['general'];
          foreach ($bank as $key => $questions) {
               if (str_contains($topic, $key)) {
                    $pool = $questions;
                    break;
               }
          }

          // Filter out questions that already exist in this stage
          $existingTexts = $stage->questions()->pluck('question_text')->map(fn($t) => strtolower(trim($t)))->toArray();
          $available = array_filter($pool, fn($q) => !in_array(strtolower(trim($q['question_text'])), $existingTexts));
          $available = array_values($available);

          // If not enough unique questions, use all available
          if (count($available) < $count) {
               $available = $pool;
          }

          // Pick random questions
          shuffle($available);
          $selected = array_slice($available, 0, $count);

          return $this->saveQuestions($stage, $selected);
     }

     /**
      * Save generated questions to the database.
      */
     private function saveQuestions(Stage $stage, array $questions): array
     {
          $created = [];

          foreach ($questions as $q) {
               if (
                    empty($q['question_text']) || empty($q['option_a']) ||
                    empty($q['option_b']) || empty($q['option_c']) ||
                    empty($q['option_d']) || empty($q['correct_answer'])
               ) {
                    continue;
               }

               $created[] = Question::create([
                    'stage_id' => $stage->id,
                    'question_text' => $q['question_text'],
                    'option_a' => $q['option_a'],
                    'option_b' => $q['option_b'],
                    'option_c' => $q['option_c'],
                    'option_d' => $q['option_d'],
                    'correct_answer' => $q['correct_answer'],
                    'difficulty' => $q['difficulty'] ?? 'medium',
               ]);
          }

          return $created;
     }

     /**
      * Curated question bank organized by chemistry topic.
      */
     private function getQuestionBank(): array
     {
          return [
               'atom' => [
                    ['question_text' => 'What is the charge of a proton?', 'option_a' => 'Negative', 'option_b' => 'Positive', 'option_c' => 'Neutral', 'option_d' => 'Variable', 'correct_answer' => 'b', 'difficulty' => 'easy'],
                    ['question_text' => 'Which scientist proposed the plum pudding model?', 'option_a' => 'Rutherford', 'option_b' => 'Bohr', 'option_c' => 'Thomson', 'option_d' => 'Dalton', 'correct_answer' => 'c', 'difficulty' => 'medium'],
                    ['question_text' => 'What determines the chemical properties of an element?', 'option_a' => 'Number of neutrons', 'option_b' => 'Atomic mass', 'option_c' => 'Number of protons', 'option_d' => 'Number of valence electrons', 'correct_answer' => 'd', 'difficulty' => 'medium'],
                    ['question_text' => 'An atom with more neutrons than protons is called:', 'option_a' => 'An ion', 'option_b' => 'An isotope', 'option_c' => 'An allotrope', 'option_d' => 'A molecule', 'correct_answer' => 'b', 'difficulty' => 'medium'],
                    ['question_text' => 'The Heisenberg uncertainty principle states that:', 'option_a' => 'Electrons orbit in fixed paths', 'option_b' => 'We cannot know both position and momentum simultaneously', 'option_c' => 'Energy is always conserved', 'option_d' => 'Atoms are indivisible', 'correct_answer' => 'b', 'difficulty' => 'hard'],
                    ['question_text' => 'How many orbitals are in the d subshell?', 'option_a' => '1', 'option_b' => '3', 'option_c' => '5', 'option_d' => '7', 'correct_answer' => 'c', 'difficulty' => 'hard'],
                    ['question_text' => 'Which quantum number describes the shape of an orbital?', 'option_a' => 'Principal (n)', 'option_b' => 'Angular momentum (l)', 'option_c' => 'Magnetic (ml)', 'option_d' => 'Spin (ms)', 'correct_answer' => 'b', 'difficulty' => 'hard'],
                    ['question_text' => 'Carbon-14 is used for radioactive dating because it has:', 'option_a' => '6 protons and 6 neutrons', 'option_b' => '6 protons and 8 neutrons', 'option_c' => '7 protons and 7 neutrons', 'option_d' => '8 protons and 6 neutrons', 'correct_answer' => 'b', 'difficulty' => 'medium'],
               ],
               'bond' => [
                    ['question_text' => 'What type of bond holds water molecules to each other?', 'option_a' => 'Ionic bond', 'option_b' => 'Covalent bond', 'option_c' => 'Hydrogen bond', 'option_d' => 'Metallic bond', 'correct_answer' => 'c', 'difficulty' => 'medium'],
                    ['question_text' => 'Which compound has the strongest intermolecular forces?', 'option_a' => 'CH₄', 'option_b' => 'H₂O', 'option_c' => 'CO₂', 'option_d' => 'N₂', 'correct_answer' => 'b', 'difficulty' => 'medium'],
                    ['question_text' => 'A coordinate (dative) bond is formed when:', 'option_a' => 'Electrons are transferred', 'option_b' => 'Both shared electrons come from one atom', 'option_c' => 'Electrons are shared equally', 'option_d' => 'Electrons are delocalized', 'correct_answer' => 'b', 'difficulty' => 'hard'],
                    ['question_text' => 'Van der Waals forces are strongest in molecules with:', 'option_a' => 'Small molecular mass', 'option_b' => 'Large molecular mass', 'option_c' => 'Ionic bonds', 'option_d' => 'Low boiling points', 'correct_answer' => 'b', 'difficulty' => 'medium'],
                    ['question_text' => 'The bond angle in water (H₂O) is approximately:', 'option_a' => '90°', 'option_b' => '104.5°', 'option_c' => '120°', 'option_d' => '180°', 'correct_answer' => 'b', 'difficulty' => 'hard'],
                    ['question_text' => 'Sigma bonds are formed by:', 'option_a' => 'Sideways overlap of p orbitals', 'option_b' => 'Head-on overlap of orbitals', 'option_c' => 'Transfer of electrons', 'option_d' => 'Delocalization', 'correct_answer' => 'b', 'difficulty' => 'hard'],
                    ['question_text' => 'Graphite conducts electricity because:', 'option_a' => 'It has ionic bonds', 'option_b' => 'Each carbon has one delocalized electron', 'option_c' => 'It has metallic bonds', 'option_d' => 'It contains free ions', 'correct_answer' => 'b', 'difficulty' => 'medium'],
                    ['question_text' => 'The octet rule states that atoms tend to:', 'option_a' => 'Gain 8 electrons always', 'option_b' => 'Have 8 electrons in their outer shell', 'option_c' => 'Form 8 bonds', 'option_d' => 'Lose 8 electrons', 'correct_answer' => 'b', 'difficulty' => 'easy'],
               ],
               'reaction' => [
                    ['question_text' => 'Which factor does NOT affect reaction rate?', 'option_a' => 'Temperature', 'option_b' => 'Concentration', 'option_c' => 'Color of reactants', 'option_d' => 'Surface area', 'correct_answer' => 'c', 'difficulty' => 'easy'],
                    ['question_text' => 'Le Chatelier\'s principle applies to:', 'option_a' => 'All reactions', 'option_b' => 'Reversible reactions at equilibrium', 'option_c' => 'Only exothermic reactions', 'option_d' => 'Only gas-phase reactions', 'correct_answer' => 'b', 'difficulty' => 'medium'],
                    ['question_text' => 'In a redox reaction, the reducing agent:', 'option_a' => 'Gains electrons', 'option_b' => 'Loses electrons', 'option_c' => 'Gains protons', 'option_d' => 'Loses protons', 'correct_answer' => 'b', 'difficulty' => 'medium'],
                    ['question_text' => 'Activation energy is:', 'option_a' => 'Energy released in a reaction', 'option_b' => 'Minimum energy needed to start a reaction', 'option_c' => 'Total energy of products', 'option_d' => 'Energy stored in bonds', 'correct_answer' => 'b', 'difficulty' => 'easy'],
                    ['question_text' => 'The rate of reaction can be measured by:', 'option_a' => 'Change in temperature only', 'option_b' => 'Change in mass, volume, or concentration over time', 'option_c' => 'Change in the container size', 'option_d' => 'Change in the number of atoms', 'correct_answer' => 'b', 'difficulty' => 'medium'],
                    ['question_text' => 'An enzyme is a type of:', 'option_a' => 'Reactant', 'option_b' => 'Product', 'option_c' => 'Biological catalyst', 'option_d' => 'Inhibitor', 'correct_answer' => 'c', 'difficulty' => 'easy'],
                    ['question_text' => 'Collision theory states reactions occur when particles:', 'option_a' => 'Are heated', 'option_b' => 'Collide with sufficient energy and correct orientation', 'option_c' => 'Are dissolved in water', 'option_d' => 'Are in the gas phase', 'correct_answer' => 'b', 'difficulty' => 'medium'],
                    ['question_text' => 'The equilibrium constant Kc is affected by:', 'option_a' => 'Concentration', 'option_b' => 'Pressure', 'option_c' => 'Temperature', 'option_d' => 'Catalysts', 'correct_answer' => 'c', 'difficulty' => 'hard'],
               ],
               'acid' => [
                    ['question_text' => 'According to Brønsted-Lowry theory, an acid is a:', 'option_a' => 'Proton acceptor', 'option_b' => 'Proton donor', 'option_c' => 'Electron donor', 'option_d' => 'Electron acceptor', 'correct_answer' => 'b', 'difficulty' => 'medium'],
                    ['question_text' => 'A weak acid in water is:', 'option_a' => 'Fully dissociated', 'option_b' => 'Partially dissociated', 'option_c' => 'Not dissociated', 'option_d' => 'Completely neutralized', 'correct_answer' => 'b', 'difficulty' => 'medium'],
                    ['question_text' => 'Antacids work by:', 'option_a' => 'Adding more acid', 'option_b' => 'Neutralizing excess stomach acid', 'option_c' => 'Increasing pH to 14', 'option_d' => 'Removing all water', 'correct_answer' => 'b', 'difficulty' => 'easy'],
                    ['question_text' => 'The conjugate base of HCl is:', 'option_a' => 'H⁺', 'option_b' => 'Cl⁻', 'option_c' => 'OH⁻', 'option_d' => 'HClO', 'correct_answer' => 'b', 'difficulty' => 'medium'],
                    ['question_text' => 'Ka is the acid dissociation constant. A larger Ka means:', 'option_a' => 'Weaker acid', 'option_b' => 'Stronger acid', 'option_c' => 'Neutral solution', 'option_d' => 'More basic', 'correct_answer' => 'b', 'difficulty' => 'hard'],
                    ['question_text' => 'Rainwater is naturally slightly acidic due to:', 'option_a' => 'Dissolved NaCl', 'option_b' => 'Dissolved CO₂ forming carbonic acid', 'option_c' => 'Dissolved oxygen', 'option_d' => 'Dissolved nitrogen', 'correct_answer' => 'b', 'difficulty' => 'medium'],
                    ['question_text' => 'A strong base is one that:', 'option_a' => 'Partially ionizes in water', 'option_b' => 'Completely ionizes in water', 'option_c' => 'Does not dissolve in water', 'option_d' => 'Has a pH of 7', 'correct_answer' => 'b', 'difficulty' => 'easy'],
                    ['question_text' => 'The pH scale is logarithmic, meaning each unit represents:', 'option_a' => 'A doubling of H⁺ concentration', 'option_b' => 'A tenfold change in H⁺ concentration', 'option_c' => 'A linear change', 'option_d' => 'No change', 'correct_answer' => 'b', 'difficulty' => 'hard'],
               ],
               'organic' => [
                    ['question_text' => 'Fractional distillation separates crude oil based on:', 'option_a' => 'Color', 'option_b' => 'Boiling points', 'option_c' => 'Density only', 'option_d' => 'Molecular shape', 'correct_answer' => 'b', 'difficulty' => 'easy'],
                    ['question_text' => 'The functional group -OH identifies:', 'option_a' => 'An alkane', 'option_b' => 'An aldehyde', 'option_c' => 'An alcohol', 'option_d' => 'A ketone', 'correct_answer' => 'c', 'difficulty' => 'easy'],
                    ['question_text' => 'Ethene (C₂H₄) is an example of:', 'option_a' => 'An alkane', 'option_b' => 'An alkene', 'option_c' => 'An alkyne', 'option_d' => 'An alcohol', 'correct_answer' => 'b', 'difficulty' => 'easy'],
                    ['question_text' => 'Addition polymerization occurs in:', 'option_a' => 'Saturated hydrocarbons', 'option_b' => 'Unsaturated hydrocarbons', 'option_c' => 'Noble gases', 'option_d' => 'Ionic compounds', 'correct_answer' => 'b', 'difficulty' => 'medium'],
                    ['question_text' => 'Cracking is used to:', 'option_a' => 'Combine small molecules', 'option_b' => 'Break long-chain hydrocarbons into shorter ones', 'option_c' => 'Purify water', 'option_d' => 'Extract metals', 'correct_answer' => 'b', 'difficulty' => 'medium'],
                    ['question_text' => 'An ester is formed from the reaction of:', 'option_a' => 'Two acids', 'option_b' => 'An acid and a base', 'option_c' => 'An alcohol and a carboxylic acid', 'option_d' => 'Two alkanes', 'correct_answer' => 'c', 'difficulty' => 'hard'],
                    ['question_text' => 'Which test identifies an alkene?', 'option_a' => 'Flame test', 'option_b' => 'Bromine water test (decolorizes)', 'option_c' => 'Litmus test', 'option_d' => 'pH test', 'correct_answer' => 'b', 'difficulty' => 'medium'],
                    ['question_text' => 'Fermentation of glucose produces:', 'option_a' => 'Methanol and CO₂', 'option_b' => 'Ethanol and CO₂', 'option_c' => 'Propanol and H₂O', 'option_d' => 'Butanol and O₂', 'correct_answer' => 'b', 'difficulty' => 'medium'],
               ],
               'general' => [
                    ['question_text' => 'Which element is the most abundant in the Earth\'s crust?', 'option_a' => 'Iron', 'option_b' => 'Oxygen', 'option_c' => 'Silicon', 'option_d' => 'Aluminum', 'correct_answer' => 'b', 'difficulty' => 'medium'],
                    ['question_text' => 'Avogadro\'s number is approximately:', 'option_a' => '6.02 × 10²³', 'option_b' => '3.14 × 10⁸', 'option_c' => '1.60 × 10⁻¹⁹', 'option_d' => '9.81 × 10¹', 'correct_answer' => 'a', 'difficulty' => 'easy'],
                    ['question_text' => 'The periodic table is arranged by:', 'option_a' => 'Atomic mass', 'option_b' => 'Atomic number', 'option_c' => 'Electronegativity', 'option_d' => 'Date of discovery', 'correct_answer' => 'b', 'difficulty' => 'easy'],
                    ['question_text' => 'Noble gases are unreactive because they have:', 'option_a' => 'No electrons', 'option_b' => 'Full outer electron shells', 'option_c' => 'No neutrons', 'option_d' => 'Very high masses', 'correct_answer' => 'b', 'difficulty' => 'easy'],
                    ['question_text' => 'Which state of matter has a fixed volume but no fixed shape?', 'option_a' => 'Solid', 'option_b' => 'Liquid', 'option_c' => 'Gas', 'option_d' => 'Plasma', 'correct_answer' => 'b', 'difficulty' => 'easy'],
                    ['question_text' => 'Electroplating uses the process of:', 'option_a' => 'Distillation', 'option_b' => 'Electrolysis', 'option_c' => 'Filtration', 'option_d' => 'Chromatography', 'correct_answer' => 'b', 'difficulty' => 'medium'],
                    ['question_text' => 'The mole is defined as the amount of substance containing:', 'option_a' => '6.02 × 10²³ particles', 'option_b' => '1 gram of substance', 'option_c' => '1 liter of gas', 'option_d' => '100 atoms', 'correct_answer' => 'a', 'difficulty' => 'medium'],
                    ['question_text' => 'Allotropes are different forms of:', 'option_a' => 'Different elements', 'option_b' => 'The same element', 'option_c' => 'Different compounds', 'option_d' => 'Different mixtures', 'correct_answer' => 'b', 'difficulty' => 'medium'],
               ],
          ];
     }
}
