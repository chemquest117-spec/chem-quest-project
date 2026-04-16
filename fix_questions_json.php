<?php

/**
 * Comprehensive fix for questions.json
 * 1. Convert essay → complete with ____ blanks and expected_answers array
 * 2. Convert text-only essays (no numeric answer) → MCQ
 * 3. Fix broken MCQs with options embedded in question text
 * 4. Normalize all fields to the new schema
 */
$jsonPath = __DIR__.'/database/seeders/questions.json';
$data = json_decode(file_get_contents($jsonPath), true);
$fixed = [];
$changeLog = [];

foreach ($data as $i => $q) {

    // ─── ESSAY → COMPLETE conversions ───
    if ($q['type'] === 'essay') {

        // LO1 Q1: N₂H₄ oxidation number
        if ($q['lo'] === 'LO1' && strpos($q['text'], "N\u{2082}H\u{2084}") !== false) {
            $q['type'] = 'complete';
            $q['text'] = "In the compound N\u{2082}H\u{2084}, the oxidation number of nitrogen is ____.";
            $q['options'] = [];
            $q['correct_answer'] = '';
            $q['expected_answer'] = '';
            $q['expected_answers'] = [['value' => -2, 'tolerance' => 0]];
            $q['explanation'] = "Hydrogen = +1\n4H = +4\n2N + 4 = 0\n2N = \u{2212}4\nN = \u{2212}2";
            $changeLog[] = "LO1 Q1: essay → complete (N\u{2082}H\u{2084})";
        }
        // LO1 Q2: CH₃COOH two carbons (MULTI-BLANK)
        elseif ($q['lo'] === 'LO1' && strpos($q['text'], "CH\u{2083}COOH") !== false) {
            $q['type'] = 'complete';
            $q['text'] = "In the acetic acid molecule (CH\u{2083}COOH), the oxidation number of the first carbon (CH\u{2083}) is ____, while the oxidation number of the second carbon (COOH) is ____.";
            $q['options'] = [];
            $q['correct_answer'] = '';
            $q['expected_answer'] = '';
            $q['expected_answers'] = [
                ['value' => -3, 'tolerance' => 0],
                ['value' => 3, 'tolerance' => 0],
            ];
            $q['explanation'] = "A- First carbon (CH\u{2083}):\n* 3H = +3\n* C + 3 = 0\n* C = \u{2212}3\nB- Second carbon (COOH):\n* 2O = \u{2212}4\n* H = +1\n* C \u{2212} 4 + 1 = 0\n* C = +3";
            $changeLog[] = "LO1 Q2: essay → complete multi-blank (CH\u{2083}COOH)";
        }
        // LO1 Q3: P₄O₁₀
        elseif ($q['lo'] === 'LO1' && strpos($q['text'], "P\u{2084}O\u{2081}\u{2080}") !== false) {
            $q['type'] = 'complete';
            $q['text'] = "In the compound P\u{2084}O\u{2081}\u{2080}, the oxidation number of phosphorus is ____.";
            $q['options'] = [];
            $q['correct_answer'] = '';
            $q['expected_answer'] = '';
            $q['expected_answers'] = [['value' => 5, 'tolerance' => 0]];
            $q['explanation'] = "Oxygen = \u{2212}2\n10O = \u{2212}20\n4P \u{2212} 20 = 0\n4P = +20\nP = +5";
            $changeLog[] = "LO1 Q3: essay → complete (P\u{2084}O\u{2081}\u{2080})";
        }
        // LO2 essay Q1: HCl vs CH₃COOH conductivity → MCQ
        elseif ($q['lo'] === 'LO2' && strpos($q['text'], 'HCl') !== false && strpos($q['text'], "CH\u{2083}COOH") !== false) {
            $q['type'] = 'mcq';
            $q['text'] = "Two solutions have the same concentration (0.1 M): HCl and CH\u{2083}COOH. Why does their electrical conductivity differ?";
            $q['options'] = [
                'Both conduct equally because concentration is the same',
                "HCl fully ionizes producing more ions, while CH\u{2083}COOH only partially ionizes",
                "CH\u{2083}COOH conducts better due to hydrogen bonding",
                'Neither conducts because they are acids',
            ];
            $q['correct_answer'] = 'b';
            $q['expected_answer'] = '';
            $q['explanation'] = "HCl is a strong electrolyte that fully ionizes into H\u{207a} and Cl\u{207b}, producing many ions. CH\u{2083}COOH is a weak electrolyte and only partially ionizes, so fewer ions are available for conductivity.";
            $changeLog[] = "LO2 essay Q1: essay → MCQ (HCl vs CH\u{2083}COOH)";
        }
        // LO2 essay Q2: NaCl volume/conductivity → MCQ
        elseif ($q['lo'] === 'LO2' && strpos($q['text'], 'NaCl') !== false && strpos($q['text'], 'volume') !== false) {
            $q['type'] = 'mcq';
            $q['text'] = 'You have an NaCl solution and increase the volume while keeping the number of moles constant. What happens to the electrical conductivity?';
            $q['options'] = [
                'Conductivity increases because more water means more ions',
                'Conductivity stays the same because the number of moles is constant',
                'Conductivity decreases because ion concentration decreases',
                'Conductivity doubles because volume doubles',
            ];
            $q['correct_answer'] = 'c';
            $q['expected_answer'] = '';
            $q['explanation'] = 'Increasing the volume while keeping moles constant decreases ion concentration. Since conductivity depends on ion concentration, it decreases.';
            $changeLog[] = 'LO2 essay Q2: essay → MCQ (NaCl volume)';
        }
        // LO2 essay Q3: aqueous NaCl vs molten → MCQ
        elseif ($q['lo'] === 'LO2' && strpos($q['text'], 'molten NaCl') !== false) {
            $q['type'] = 'mcq';
            $q['text'] = 'How is charge conducted in an aqueous NaCl solution compared to molten NaCl?';
            $q['options'] = [
                'In aqueous NaCl, electrons carry charge; in molten NaCl, ions carry charge',
                "In both cases, Na\u{207a} and Cl\u{207b} ions carry the charge; electrons do not move through the liquid",
                'In aqueous NaCl, only water molecules carry charge',
                'In molten NaCl, free electrons conduct; in aqueous NaCl, ions conduct',
            ];
            $q['correct_answer'] = 'b';
            $q['expected_answer'] = '';
            $q['explanation'] = "In both aqueous and molten NaCl, Na\u{207a} and Cl\u{207b} ions carry the charge. Ionic compounds conduct via mobile ions, unlike metals which conduct via delocalized electrons.";
            $changeLog[] = 'LO2 essay Q3: essay → MCQ (aq vs molten NaCl)';
        }
        // LO3 essay: empirical formula → MCQ
        elseif ($q['lo'] === 'LO3' && strpos($q['text'], 'empirical formula') !== false) {
            $q['type'] = 'mcq';
            $q['text'] = 'A compound contains: C = 52.2%, H = 13.0%, O = 34.8%. The molar mass is 88 g/mol. What is the molecular formula?';
            $q['options'] = [
                "C\u{2082}H\u{2086}O",
                "C\u{2083}H\u{2088}O",
                "C\u{2084}H\u{2081}\u{2080}O",
                "C\u{2085}H\u{2081}\u{2082}O",
            ];
            $q['correct_answer'] = 'c';
            $q['expected_answer'] = '';
            $q['explanation'] = "1. Assume 100 g: C = 52.2 g, H = 13.0 g, O = 34.8 g\n2. Moles: C: 52.2/12 = 4.35, H: 13/1 = 13, O: 34.8/16 = 2.175\n3. Divide by smallest (2.175): C = 2, H = 6, O = 1\n4. Empirical formula: C\u{2082}H\u{2086}O (molar mass = 46)\n5. Ratio: 88/46 \u{2248} 2, so molecular formula = C\u{2084}H\u{2081}\u{2080}O\nMultiple structural formulas are possible (alcohol or ether isomers).";
            $changeLog[] = 'LO3 essay Q1: essay → MCQ (empirical formula)';
        } else {
            // Catch-all: convert any remaining essay to complete with a blank
            $q['type'] = 'complete';
            if (strpos($q['text'], '___') === false) {
                $q['text'] .= ' ____';
            }
            $q['expected_answers'] = [['value' => 0, 'tolerance' => 0]];
            $q['expected_answer'] = '';
            $changeLog[] = 'Unknown essay → complete (fallback): '.substr($q['text'], 0, 50);
        }
    }

    // ─── FIX BROKEN MCQs (options in question text, empty options array) ───

    // LO4 Q6: Missing option D
    if ($q['lo'] === 'LO4' && $q['question_number'] === 6 && ($q['options'][3] ?? '') === '') {
        $q['options'] = ['C2H6', 'C3H8', 'C4H10', 'C5H12'];
        $changeLog[] = 'LO4 Q6: Added missing option D';
    }

    // LO4 Q7: Options mashed into option A
    if ($q['lo'] === 'LO4' && $q['question_number'] === 7 && strpos($q['options'][0] ?? '', '1 and 2 B') !== false) {
        $q['text'] = "Which properties of the different compounds in petroleum enable its separation into fractions?\n1. Boiling point\n2. Chain length\n3. Chemical reactivity\n4. Solubility in water";
        $q['options'] = [
            '1 and 2',
            '1 and 3',
            '2 and 4',
            '3 and 4',
        ];
        $q['correct_answer'] = 'a';
        $changeLog[] = 'LO4 Q7: Fixed mashed options';
    }

    // LO4 Q8: Options in text, empty array
    if ($q['lo'] === 'LO4' && $q['question_number'] === 8 && strpos($q['text'], 'bitumen') !== false) {
        $q['text'] = 'Which substance has a main constituent that contains only one carbon atom per molecule?';
        $q['options'] = ['Bitumen', 'Gasoline', 'Natural gas', 'Petroleum'];
        $q['correct_answer'] = 'c';
        $changeLog[] = 'LO4 Q8: Extracted options from text';
    }

    // LO4 Q9: Options in text
    if ($q['lo'] === 'LO4' && $q['question_number'] === 9 && strpos($q['text'], 'decane') !== false && strpos($q['text'], 'propane') !== false) {
        $q['text'] = 'What is the name of the process that could be used to produce propane (C3H8) from decane (C10H22)?';
        $q['options'] = ['Substitution', 'Reforming', 'Fractional distillation', 'Cracking'];
        $q['correct_answer'] = 'd';
        $changeLog[] = 'LO4 Q9: Extracted options from text';
    }

    // LO4 Q10: Options+answer in text
    if ($q['lo'] === 'LO4' && $q['question_number'] === 10 && strpos($q['text'], 'Thermal Cracking') !== false) {
        $q['text'] = 'Why does Thermal Cracking produce a higher percentage of Alkenes compared to Catalytic Cracking?';
        $q['options'] = [
            'High heat causes homolytic fission, creating free radicals that eliminate hydrogen',
            'High pressure forces molecules to form double bonds',
            'Catalysts are only used to produce Alkanes',
            'Thermal cracking only breaks C-H bonds, not C-C bonds',
        ];
        $q['correct_answer'] = 'a';
        $changeLog[] = 'LO4 Q10: Extracted options from text';
    }

    // LO4 Q11: Options+answer in text
    if ($q['lo'] === 'LO4' && $q['question_number'] === 11 && strpos($q['text'], 'Zeolite') !== false) {
        $q['text'] = 'What happens if a hydrocarbon molecule is larger than the pores of a Zeolite catalyst?';
        $q['options'] = [
            'The molecule will shrink automatically to fit',
            'The reaction rate will increase on the surface',
            'No cracking will occur because the molecule cannot reach the internal active sites',
            'The Zeolite will expand its rigid structure to accommodate it',
        ];
        $q['correct_answer'] = 'c';
        $changeLog[] = 'LO4 Q11: Extracted options from text';
    }

    // LO4 Q12: Options+answer in text
    if ($q['lo'] === 'LO4' && $q['question_number'] === 12 && strpos($q['text'], 'NOx') !== false) {
        $q['text'] = 'NOx gases are an environmental challenge in car engines because:';
        $q['options'] = [
            'The fuel contains high amounts of nitrogen',
            'High engine temperatures cause nitrogen and oxygen from the air to react',
            'They are the main product of incomplete combustion',
            'They are only produced when using leaded gasoline',
        ];
        $q['correct_answer'] = 'b';
        $changeLog[] = 'LO4 Q12: Extracted options from text';
    }

    // LO4 Q13: Options+answer in text
    if ($q['lo'] === 'LO4' && $q['question_number'] === 13 && strpos($q['text'], 'Undecane') !== false) {
        $q['text'] = 'If Undecane (C11H24) is cracked into one Propene (C3H6) and one Butene (C4H8), what is the third product?';
        $q['options'] = [
            'C4H10 (Butane)',
            'C4H8 (Butene)',
            'C5H12 (Pentane)',
            'H2 (Hydrogen gas)',
        ];
        $q['correct_answer'] = 'a';
        $q['explanation'] = 'Calculation: 11 - 3 - 4 = 4 Carbons | 24 - 6 - 8 = 10 Hydrogens → C4H10 (Butane)';
        $changeLog[] = 'LO4 Q13: Extracted options from text';
    }

    // LO4 Q14: Options+answer in text
    if ($q['lo'] === 'LO4' && $q['question_number'] === 14 && strpos($q['text'], 'Endothermic') !== false) {
        $q['text'] = 'Thermodynamically, why must heat be continuously supplied to the cracking process?';
        $q['options'] = [
            'To keep the catalyst from solidifying',
            'To prevent the formation of greenhouse gases',
            'To increase the density of the crude oil',
            'Because it is an Endothermic process where bond-breaking requires more energy than bond-making',
        ];
        $q['correct_answer'] = 'd';
        $changeLog[] = 'LO4 Q14: Extracted options from text';
    }

    // LO4 Q15: Options in text (a)-(d)
    if ($q['lo'] === 'LO4' && $q['question_number'] === 15 && strpos($q['text'], 'Bond length') !== false) {
        $q['text'] = 'Bond length of (I) ethane, (II) ethene, (III) acetylene and (IV) benzene follows the order:';
        $q['options'] = [
            'I > II > III > IV',
            'I > II > IV > III',
            'I > IV > II > III',
            'III > IV > II > I',
        ];
        $q['correct_answer'] = 'c';
        $q['explanation'] = 'Bond order is inversely proportional to bond length. Single bonds (ethane) are longest, triple bonds (acetylene) are shortest. Benzene has bond order 1.5, between single and double.';
        $changeLog[] = 'LO4 Q15: Extracted options from text';
    }

    // LO4 Q16: Options in text
    if ($q['lo'] === 'LO4' && $q['question_number'] === 16 && strpos($q['text'], 'Friedel') !== false) {
        $q['text'] = 'Which of the following can be used as the halide component of a Friedel-Craft reaction?';
        $q['options'] = [
            'Chlorobenzene',
            'Bromobenzene',
            'Chloroethene',
            'Isopropyl chloride',
        ];
        $q['correct_answer'] = 'd';
        $changeLog[] = 'LO4 Q16: Extracted options from text';
    }

    // LO4 Q19: Missing correct_answer, answer in option D text
    if ($q['lo'] === 'LO4' && $q['question_number'] === 19 && strpos($q['text'], 'albedo') !== false) {
        $q['text'] = 'How does albedo feedback in polar regions affect global warming?';
        $q['options'] = [
            'Melting ice increases albedo, which reflects heat and cools the atmosphere',
            'Melting ice decreases albedo, which increases heat absorption and accelerates melting',
            'Increased ice increases infrared absorption',
            'There is no relationship between ice and global warming',
        ];
        $q['correct_answer'] = 'b';
        $q['explanation'] = 'Reduced albedo (reflectivity) from melting ice leads to greater heat absorption by dark ocean water, creating a positive feedback loop that accelerates warming.';
        $changeLog[] = 'LO4 Q19: Fixed options and correct_answer (albedo)';
    }

    // ─── NORMALIZE ALL QUESTIONS ───

    // Ensure expected_answers is set for complete, null for mcq
    if ($q['type'] === 'complete' && ! isset($q['expected_answers'])) {
        $q['expected_answers'] = [['value' => 0, 'tolerance' => 0]];
    }
    if ($q['type'] === 'mcq') {
        unset($q['expected_answers']);
    }

    // Clean up legacy expected_answer field
    $q['expected_answer'] = '';

    $fixed[] = $q;
}

// Write fixed JSON
$json = json_encode($fixed, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
file_put_contents($jsonPath, $json);

echo '✅ Fixed '.count($changeLog)." questions:\n";
foreach ($changeLog as $log) {
    echo "  • $log\n";
}
echo "\nTotal questions in file: ".count($fixed)."\n";

// Count by type
$types = array_count_values(array_column($fixed, 'type'));
foreach ($types as $type => $count) {
    echo "  $type: $count\n";
}
