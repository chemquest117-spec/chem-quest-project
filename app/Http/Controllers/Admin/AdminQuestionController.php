<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Stage;
use App\Services\AIQuestionService;
use Illuminate\Http\Request;

class AdminQuestionController extends Controller
{
     public function index(Stage $stage)
     {
          $questions = $stage->questions()->orderBy('difficulty')->get();
          return view('admin.questions.index', compact('stage', 'questions'));
     }

     public function create(Stage $stage)
     {
          return view('admin.questions.create', compact('stage'));
     }

     public function store(Request $request, Stage $stage)
     {
          $validated = $request->validate([
               'question_text' => 'required|string',
               'option_a' => 'required|string|max:500',
               'option_b' => 'required|string|max:500',
               'option_c' => 'required|string|max:500',
               'option_d' => 'required|string|max:500',
               'correct_answer' => 'required|in:a,b,c,d',
               'difficulty' => 'required|in:easy,medium,hard',
          ]);

          $stage->questions()->create($validated);

          return redirect()->route('admin.stages.questions.index', $stage)
               ->with('success', 'Question added successfully!');
     }

     public function edit(Stage $stage, Question $question)
     {
          return view('admin.questions.edit', compact('stage', 'question'));
     }

     public function update(Request $request, Stage $stage, Question $question)
     {
          $validated = $request->validate([
               'question_text' => 'required|string',
               'option_a' => 'required|string|max:500',
               'option_b' => 'required|string|max:500',
               'option_c' => 'required|string|max:500',
               'option_d' => 'required|string|max:500',
               'correct_answer' => 'required|in:a,b,c,d',
               'difficulty' => 'required|in:easy,medium,hard',
          ]);

          $question->update($validated);

          return redirect()->route('admin.stages.questions.index', $stage)
               ->with('success', 'Question updated successfully!');
     }

     public function destroy(Stage $stage, Question $question)
     {
          $question->delete();

          return redirect()->route('admin.stages.questions.index', $stage)
               ->with('success', 'Question deleted successfully!');
     }

     /**
      * Generate AI-powered questions for a stage.
      */
     public function generate(Stage $stage, AIQuestionService $aiService)
     {
          $created = $aiService->generateQuestions($stage, 5);

          $count = count($created);

          if ($count > 0) {
               return redirect()->route('admin.stages.questions.index', $stage)
                    ->with('success', "✨ AI generated {$count} new questions successfully!");
          }

          return redirect()->route('admin.stages.questions.index', $stage)
               ->with('error', 'Could not generate questions. Please try again.');
     }
}
