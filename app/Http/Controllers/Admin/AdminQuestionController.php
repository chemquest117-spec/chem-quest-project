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
            $validated = $request->validate([
                'question_text' => 'required|string|max:2000',
                'question_text_ar' => 'nullable|string|max:2000',
                'option_a' => 'required|string|max:500',
                'option_a_ar' => 'nullable|string|max:500',
                'option_b' => 'required|string|max:500',
                'option_b_ar' => 'nullable|string|max:500',
                'option_c' => 'required|string|max:500',
                'option_c_ar' => 'nullable|string|max:500',
                'option_d' => 'required|string|max:500',
                'option_d_ar' => 'nullable|string|max:500',
                'correct_answer' => 'required|in:a,b,c,d',
                'difficulty' => 'required|in:easy,medium,hard',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);

            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('questions', 'public');
                $validated['image'] = $path;
            }

            // Normalize correct_answer to lowercase
            $validated['correct_answer'] = strtolower($validated['correct_answer']);

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
            $validated = $request->validate([
                'question_text' => 'required|string|max:2000',
                'question_text_ar' => 'nullable|string|max:2000',
                'option_a' => 'required|string|max:500',
                'option_a_ar' => 'nullable|string|max:500',
                'option_b' => 'required|string|max:500',
                'option_b_ar' => 'nullable|string|max:500',
                'option_c' => 'required|string|max:500',
                'option_c_ar' => 'nullable|string|max:500',
                'option_d' => 'required|string|max:500',
                'option_d_ar' => 'nullable|string|max:500',
                'correct_answer' => 'required|in:a,b,c,d',
                'difficulty' => 'required|in:easy,medium,hard',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);

            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($question->image) {
                    Storage::disk('public')->delete($question->image);
                }

                $path = $request->file('image')->store('questions', 'public');
                $validated['image'] = $path;
            }

            // Normalize correct_answer
            $validated['correct_answer'] = strtolower($validated['correct_answer']);

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
