<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Stage;
use App\Support\StageSchemaCache;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AdminStageController extends Controller
{
    public function index()
    {
        try {
            $stages = Stage::orderBy('order')->withCount('questions')->get();

            return view('admin.stages.index', compact('stages'));
        } catch (ValidationException $e) {
            throw $e;
        } catch (HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            return back()->withInput()->with('error', 'Failed to load stages. Please try again.');
        }
    }

    public function create()
    {
        try {
            $nextOrder = (Stage::max('order') ?? 0) + 1;

            return view('admin.stages.create', compact('nextOrder'));
        } catch (ValidationException $e) {
            throw $e;
        } catch (HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            return back()->withInput()->with('error', 'Failed to load create stage page. Please try again.');
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'order' => 'required|integer|min:1|unique:stages,order',
                'time_limit_minutes' => 'required|integer|min:1|max:120',
                'passing_percentage' => 'required|integer|min:1|max:100',
                'points_reward' => 'required|integer|min:0',
            ]);

            Stage::create($validated);

            StageSchemaCache::bump();

            return redirect()->route('admin.stages.index')
                ->with('success', 'Stage created successfully!');
        } catch (ValidationException $e) {
            throw $e;
        } catch (HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e); // Log the error internally (to Sentry/Log)

            return back()
                ->withInput()
                ->with('error', 'We encountered an unexpected error while creating stage. Please try again, or contact support if the problem persists.');
        }
    }

    public function edit(Stage $stage)
    {
        try {
            return view('admin.stages.edit', compact('stage'));
        } catch (ValidationException $e) {
            throw $e;
        } catch (HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            return back()->withInput()->with('error', 'Failed to load edit stage page. Please try again.');
        }
    }

    public function update(Request $request, Stage $stage)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'order' => 'required|integer|min:1|unique:stages,order,'.$stage->id,
                'time_limit_minutes' => 'required|integer|min:1|max:120',
                'passing_percentage' => 'required|integer|min:1|max:100',
                'points_reward' => 'required|integer|min:0',
            ]);

            $stage->update($validated);

            StageSchemaCache::bump();

            return redirect()->route('admin.stages.index')
                ->with('success', 'Stage updated successfully!');
        } catch (\Throwable $e) {
            report($e); // Log the error internally (to Sentry/Log)

            return back()
                ->withInput()
                ->with('error', 'We encountered an unexpected error while updating stage. Please try again, or contact support if the problem persists.');
        }
    }

    public function destroy(Stage $stage)
    {
        try {
            StageSchemaCache::bump();

            $stage->delete();

            return redirect()->route('admin.stages.index')
                ->with('success', 'Stage deleted successfully!');
        } catch (ValidationException $e) {
            throw $e;
        } catch (HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e); // Log the error internally (to Sentry/Log)

            return back()
                ->withInput()
                ->with('error', 'We encountered an unexpected error while deleting stage. Please try again, or contact support if the problem persists.');
        }
    }
}
