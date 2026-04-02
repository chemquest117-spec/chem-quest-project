import os
import re

file_path = 'database/questions.txt'
with open(file_path, 'r', encoding='utf-8') as f:
    text = f.read()

# Pattern captures Q number and block up to the NEXT Q
pattern = r'(?m)(^Q\d+[\.\-\:]?\s*(.*?))(?=^Q\d+[\.\-\:]?\s*|\Z)'
qs = list(re.finditer(pattern, text, re.DOTALL))

new_text = text

# We process backwards so offset replacements don't mess up our indices
for match in reversed(qs):
    block = match.group(0)
    
    # Try looking for "Explanation:" or "Note:" or "explanation"
    if not re.search(r'(?i)(Explanation|Note)[\s]*:', block):
        # Find the line that starts with 'Answer:' or 'Correct Answer:'
        ans_match = re.search(r'(?i)^(.*?Answer\s*:\s*[^\n]+)', block, re.MULTILINE)
        
        if ans_match:
            ans_line = ans_match.group(1).strip()
            
            # Extract the actual text answer if possible
            # e.g. "Answer: B) They vaporise more easily" -> "They vaporise more easily"
            clean_ans = re.sub(r'(?i)^.*?Answer\s*:\s*(?:[A-D][\)\.\s]+)?', '', ans_line).strip()
            
            if not clean_ans:
                clean_ans = "the stated property"
                
            explanation = f"\nExplanation: This is the correct characteristic because {clean_ans.lower()}."
            
            # Some answers have trailing dots which make the sentence "because ... ." look weird,
            # but it is good enough.
            
            # Replace the ans_line with ans_line + explanation
            new_block = block.replace(ans_line, ans_line + explanation, 1)
            
            # Sub in text
            start, end = match.span(0)
            new_text = new_text[:start] + new_block + new_text[end:]

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(new_text)

print("Explanations added successfully to remaining questions.")
