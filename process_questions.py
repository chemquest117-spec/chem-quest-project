import os
import re
import json

def parse_questions(filename):
    with open(filename, 'r', encoding='utf-8') as f:
        content = f.read()

    # Let's split by "Q" followed by number and dot/colon/hyphen
    pattern = r'(?m)^Q(\d+)[\.\-\:]?\s*(.*?)(?=^Q\d+[\.\-\:]?\s*|\Z)'
    raw_qs = re.finditer(pattern, content, re.DOTALL)
    
    questions = []
    
    current_lo_index = 0
    lo_counts = {}
    next_is_essay = False
    
    for match in raw_qs:
        num_str = match.group(1)
        q_num = int(num_str)
        
        block = match.group(2).strip()
        
        # When restarting from 1, start a new LO, UNLESS it's just the essay section of the SAME LO
        is_new_lo = (q_num == 1)
        
        if is_new_lo and next_is_essay:
            is_new_lo = False
            
        if is_new_lo:
            current_lo_index += 1
            lo_counts[f"LO{current_lo_index}"] = {'mcq': 0, 'essay': 0}
            
        # Check if the text ending this block indicates an essay section for the next block
        next_is_essay = "Part 2" in block or "Essay/Analytical Questions" in block
        
        current_lo = f"LO{current_lo_index}"
        
        # Clean block from markers
        block = re.sub(r'(First Lo|Second Lo|Third Lo|Fourth Lo|Fifth Lo|Part 2: Essay/Analytical Questions)', '', block).strip()
        
        if not block: continue
        

        is_essay = 'Answer:' in block and not any(opt in block for opt in ['A)', 'B)', 'C)', 'D)', 'a)', 'b)', 'c)', 'd)'])
        
        
        q_type = 'essay' if is_essay else 'mcq'
        lo_counts[current_lo][q_type] += 1
        
        # Extract fields
        q_obj = {
            'lo': current_lo,
            'question_number': q_num,
            'text': '',
            'type': q_type,
            'options': [],
            'correct_answer': '',
            'explanation': '',
            'expected_answer': ''
        }
        
        if is_essay:
            # Format: Question... Answer: ... Explanation: ...
            parts = re.split(r'Answer:|Explanation:', block)
            q_obj['text'] = parts[0].strip()
            q_obj['expected_answer'] = parts[1].strip() if len(parts) > 1 else ''
            q_obj['explanation'] = parts[2].strip() if len(parts) > 2 else ''
        else:
            # Format: Question... A) ... B) ... C) ... D) ... Answer: ... Explanation: ...
            
            # Simple heuristic
            lines = block.split('\n')
            text_lines = []
            opts = []
            ans = ''
            expl = ''
            
            in_opts = False
            for line in lines:
                line_stripped = line.strip()
                if not line_stripped: continue
                
                lower_line = line_stripped.lower()
                if lower_line.startswith('answer:') or lower_line.startswith('correct answer:'):
                    ans = line_stripped.split(':', 1)[1].strip()
                elif lower_line.startswith('explanation:') or lower_line.startswith('explaintion:'):
                    expl = line_stripped.split(':', 1)[1].strip()
                elif re.match(r'^[A-Da-d][\)\.]', line_stripped) or line_stripped.startswith('• A)') or line_stripped.startswith('• B)'):
                    opts.append(re.sub(r'^[•\s]*[A-Da-d][\)\.]\s*', '', line_stripped))
                elif not ans and not expl and len(opts) == 0:
                    text_lines.append(line_stripped)
                elif not ans and not expl and len(opts) > 0:
                    opts[-1] += " " + line_stripped
                elif expl:
                    expl += "\n" + line_stripped
                elif ans and not expl:
                    ans += " " + line_stripped
                    
            q_obj['text'] = '\n'.join(text_lines)
            
            # fill options missing
            while len(opts) < 4: opts.append('')
            q_obj['options'] = opts[:4]
            
            # correct answer letter
            m = re.search(r'([A-Da-d])[\)\.]?', ans)
            if m:
                q_obj['correct_answer'] = m.group(1).lower()
            
            # extract explanation from Ans if embedded
            if not expl and 'Explanation' in ans:
                parts = ans.split('Explanation:', 1)
                ans = parts[0].strip()
                expl = parts[1].strip()
                
            q_obj['explanation'] = expl
            
        questions.append(q_obj)
        
    with open('database/seeders/questions.json', 'w', encoding='utf-8') as f:
        json.dump(questions, f, indent=2)

    total_qs = len(questions)
    total_mcq = sum(c['mcq'] for c in lo_counts.values())
    total_essay = sum(c['essay'] for c in lo_counts.values())

    print(f"Successfully parsed {total_qs} total questions ({total_mcq} MCQ/Choose, {total_essay} Essay).\n")
    print("LO Breakdown:")
    for lo, count in lo_counts.items():
        total = count['mcq'] + count['essay']
        print(f"{lo}: {total} questions ({count['mcq']} MCQ, {count['essay']} Essay)")

if __name__ == '__main__':
    parse_questions('database/questions.txt')
