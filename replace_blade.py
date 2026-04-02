import os
import re

path = 'resources/views/quiz/result.blade.php'
with open(path, 'r', encoding='utf-8') as f:
    content = f.read()

pattern = r'(<p class="flex items-center gap-1 text-xs text-slate-500 mt-1 italic">\s*<x-icon name="x-circle" class="w-3 h-3" />\s*\{\{\s*__\(\'quiz\.not_answered\'\)\s*\}\}\s*</p>\s*@endif)'

replacement = r'''\1

                                              @if($answer->question->getTranslatedExplanation())
                                                   <div class="mt-4 p-4 rounded-xl {{ $answer->is_correct ? 'bg-emerald-500/10 border border-emerald-500/20' : 'bg-blue-500/10 border border-blue-500/20' }}">
                                                        <h4 class="text-sm font-semibold {{ $answer->is_correct ? 'text-emerald-400' : 'text-blue-400' }} mb-2 flex items-center gap-1">
                                                             <x-icon name="information-circle" class="w-4 h-4" /> {{ __('quiz.explanation') ?? 'Explanation' }}
                                                        </h4>
                                                        <p class="text-sm text-slate-300 leading-relaxed whitespace-pre-wrap">{{ $answer->question->getTranslatedExplanation() }}</p>
                                                   </div>
                                              @endif'''

new_content, count = re.subn(pattern, replacement, content)

if count > 0:
    with open(path, 'w', encoding='utf-8') as f:
        f.write(new_content)
    print("Replaced successfully")
else:
    print("Pattern not found")
