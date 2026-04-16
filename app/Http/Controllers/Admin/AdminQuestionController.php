<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Stage;
use App\Services\AIQuestionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AdminQuestionController extends Controller
{
    public function index(Stage $stage)
    {
        try {
            $questions = $stage->questions()->orderBy('difficulty')->get();

            return view('admin.questions.index', compact('stage', 'questions'));
        } catch (ValidationException $e) {
            throw $e;
        } catch (HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            return back()->withInput()->with('error', 'Failed to load questions. Please try again.');
        }
    }

    public function create(Stage $stage)
    {
        try {
            return view('admin.questions.create', compact('stage'));
        } catch (ValidationException $e) {
            throw $e;
        } catch (HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            return back()->withInput()->with('error', 'Failed to load create question page. Please try again.');
        }
    }

    public function store(Request $request, Stage $stage)
    {
        try {
            $rules = [
                'type' => 'required|in:mcq,complete',
                'question_text' => 'required|string|max:2000',
                'question_text_ar' => 'nullable|string|max:2000',
                'explanation' => 'nullable|string|max:5000',
                'explanation_ar' => 'nullable|string|max:5000',
                'difficulty' => 'required|in:easy,medium,hard',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ];

            if ($request->type === 'complete') {
                $rules['expected_answers'] = 'required|array|min:1';
                $rules['expected_answers.*.value'] = 'required|numeric';
                $rules['expected_answers.*.tolerance'] = 'nullable|numeric|min:0';
            } else {
                $rules['option_a'] = 'required|string|max:500';
                $rules['option_a_ar'] = 'nullable|string|max:500';
                $rules['option_b'] = 'required|string|max:500';
                $rules['option_b_ar'] = 'nullable|string|max:500';
                $rules['option_c'] = 'required|string|max:500';
                $rules['option_c_ar'] = 'nullable|string|max:500';
                $rules['option_d'] = 'required|string|max:500';
                $rules['option_d_ar'] = 'nullable|string|max:500';
                $rules['correct_answer'] = 'required|in:a,b,c,d';
            }

            $validated = $request->validate($rules);

            // Clean up expected_answers: cast values to float, set default tolerance
            if (isset($validated['expected_answers'])) {
                $validated['expected_answers'] = array_values(array_map(function ($item) {
                    return [
                        'value' => (float) $item['value'],
                        'tolerance' => isset($item['tolerance']) && $item['tolerance'] !== '' ? (float) $item['tolerance'] : 0,
                    ];
                }, $validated['expected_answers']));
            }

            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('questions', 'public');
                $validated['image'] = $path;
            }

            // Normalize correct_answer to lowercase if present
            if (isset($validated['correct_answer'])) {
                $validated['correct_answer'] = strtolower($validated['correct_answer']);
            }

            // Sync Arabic fields if they are not provided in the form
            $validated['question_text_ar'] = $validated['question_text'] ?? null;
            $validated['explanation_ar'] = $validated['explanation'] ?? null;
            if (isset($validated['option_a'])) {
                $validated['option_a_ar'] = $validated['option_a'];
            }
            if (isset($validated['option_b'])) {
                $validated['option_b_ar'] = $validated['option_b'];
            }
            if (isset($validated['option_c'])) {
                $validated['option_c_ar'] = $validated['option_c'];
            }
            if (isset($validated['option_d'])) {
                $validated['option_d_ar'] = $validated['option_d'];
            }

            $stage->questions()->create($validated);

            // Clear cached question IDs for this stage
            Cache::forget("stage_{$stage->id}_question_ids");

            return redirect()->route('admin.stages.questions.index', $stage)
                ->with('success', 'Question added successfully!');
        } catch (ValidationException $e) {
            throw $e;
        } catch (HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            return back()->withInput()->with('error', 'Failed to save question. Please ensure the image is valid and try again.');
        }
    }

    public function edit(Stage $stage, Question $question)
    {
        try {
            return view('admin.questions.edit', compact('stage', 'question'));
        } catch (ValidationException $e) {
            throw $e;
        } catch (HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            return back()->withInput()->with('error', 'Failed to load edit question page. Please try again.');
        }
    }

    public function update(Request $request, Stage $stage, Question $question)
    {
        try {
            $rules = [
                'type' => 'required|in:mcq,complete',
                'question_text' => 'required|string|max:2000',
                'question_text_ar' => 'nullable|string|max:2000',
                'difficulty' => 'required|in:easy,medium,hard',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ];

            if ($request->type === 'complete') {
                $rules['expected_answers'] = 'required|array|min:1';
                $rules['expected_answers.*.value'] = 'required|numeric';
                $rules['expected_answers.*.tolerance'] = 'nullable|numeric|min:0';
                // Nullify MCQ fields since type is changed to complete
                $validated_nulls = [
                    'option_a' => null, 'option_b' => null, 'option_c' => null, 'option_d' => null,
                    'option_a_ar' => null, 'option_b_ar' => null, 'option_c_ar' => null, 'option_d_ar' => null,
                    'correct_answer' => null,
                    'expected_answer' => null, 'expected_answer_ar' => null,
                ];
            } else {
                $rules['option_a'] = 'required|string|max:500';
                $rules['option_a_ar'] = 'nullable|string|max:500';
                $rules['option_b'] = 'required|string|max:500';
                $rules['option_b_ar'] = 'nullable|string|max:500';
                $rules['option_c'] = 'required|string|max:500';
                $rules['option_c_ar'] = 'nullable|string|max:500';
                $rules['option_d'] = 'required|string|max:500';
                $rules['option_d_ar'] = 'nullable|string|max:500';
                $rules['correct_answer'] = 'required|in:a,b,c,d';
                $validated_nulls = [
                    'expected_answer' => null, 'expected_answer_ar' => null,
                    'expected_answers' => null,
                ];
            }

            $validated = array_merge($request->validate($rules), $validated_nulls);

            // Clean up expected_answers: cast values to float, set default tolerance
            if (isset($validated['expected_answers']) && is_array($validated['expected_answers'])) {
                $validated['expected_answers'] = array_values(array_map(function ($item) {
                    return [
                        'value' => (float) $item['value'],
                        'tolerance' => isset($item['tolerance']) && $item['tolerance'] !== '' ? (float) $item['tolerance'] : 0,
                    ];
                }, $validated['expected_answers']));
            }

            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($question->image) {
                    Storage::disk('public')->delete($question->image);
                }

                $path = $request->file('image')->store('questions', 'public');
                $validated['image'] = $path;
            }

            // Normalize correct_answer if present
            if (isset($validated['correct_answer'])) {
                $validated['correct_answer'] = strtolower($validated['correct_answer']);
            }

            // Sync Arabic fields if they are not provided in the form,
            // ensuring English edits take effect in bilingual environments without direct UI tabs.
            $validated['question_text_ar'] = $validated['question_text'] ?? null;
            $validated['explanation_ar'] = $validated['explanation'] ?? null;
            if (isset($validated['option_a'])) {
                $validated['option_a_ar'] = $validated['option_a'];
            }
            if (isset($validated['option_b'])) {
                $validated['option_b_ar'] = $validated['option_b'];
            }
            if (isset($validated['option_c'])) {
                $validated['option_c_ar'] = $validated['option_c'];
            }
            if (isset($validated['option_d'])) {
                $validated['option_d_ar'] = $validated['option_d'];
            }

            $question->update($validated);

            return redirect()->route('admin.stages.questions.index', $stage)
                ->with('success', 'Question updated successfully!');
        } catch (ValidationException $e) {
            throw $e;
        } catch (HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            return back()->withInput()->with('error', 'Failed to update question. Please try again.');
        }
    }

    public function destroy(Stage $stage, Question $question)
    {
        try {
            if ($question->image) {
                Storage::disk('public')->delete($question->image);
            }

            $question->delete();

            // Clear cached question IDs for this stage
            Cache::forget("stage_{$stage->id}_question_ids");

            return redirect()->route('admin.stages.questions.index', $stage)
                ->with('success', 'Question deleted successfully!');
        } catch (ValidationException $e) {
            throw $e;
        } catch (HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            return back()->withInput()->with('error', 'Failed to delete question. Please try again.');
        }
    }

    /**
     * Generate AI-powered questions for a stage.
     * Rate-limited to 5 requests per minute to prevent API abuse.
     */
    public function generate(Request $request, Stage $stage, AIQuestionService $aiService)
    {
        try {
            // Rate limit: max 5 AI generations per minute per admin
            $key = 'ai-generate:'.$request->user()->id;

            if (RateLimiter::tooManyAttempts($key, 5)) {
                $seconds = RateLimiter::availableIn($key);

                return redirect()->route('admin.stages.questions.index', $stage)
                    ->with('error', "Too many requests. Please wait {$seconds} seconds before trying again.");
            }

            RateLimiter::hit($key, 60);

            $created = $aiService->generateQuestions($stage, 5);
            $count = count($created);

            // Clear cached question IDs since new questions were added
            Cache::forget("stage_{$stage->id}_question_ids");

            if ($count > 0) {
                return redirect()->route('admin.stages.questions.index', $stage)
                    ->with('success', "✨ AI generated {$count} new questions successfully!");
            }

            return redirect()->route('admin.stages.questions.index', $stage)
                ->with('error', 'Could not generate questions. Please try again.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            return back()->withInput()->with('error', 'Failed to generate questions. Please try again.');
        }
    }
}
