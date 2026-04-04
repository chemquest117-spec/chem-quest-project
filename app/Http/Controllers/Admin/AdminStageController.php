<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Stage;
use Illuminate\Http\Request;

class AdminStageController extends Controller
{
    public function index()
    {
        $stages = Stage::orderBy('order')->withCount('questions')->get();

        return view('admin.stages.index', compact('stages'));
    }

    public function create()
    {
        $nextOrder = (Stage::max('order') ?? 0) + 1;

        return view('admin.stages.create', compact('nextOrder'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'required|integer|min:1|unique:stages,order',
            'time_limit_minutes' => 'required|integer|min:1|max:120',
            'passing_percentage' => 'required|integer|min:1|max:100',
            'points_reward' => 'required|integer|min:0',
        ]);

        Stage::create($validated);

        return redirect()->route('admin.stages.index')
            ->with('success', 'Stage created successfully!');
    }

    public function edit(Stage $stage)
    {
        return view('admin.stages.edit', compact('stage'));
    }

    public function update(Request $request, Stage $stage)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'required|integer|min:1|unique:stages,order,'.$stage->id,
            'time_limit_minutes' => 'required|integer|min:1|max:120',
            'passing_percentage' => 'required|integer|min:1|max:100',
            'points_reward' => 'required|integer|min:0',
        ]);

        $stage->update($validated);

        return redirect()->route('admin.stages.index')
            ->with('success', 'Stage updated successfully!');
    }

    public function destroy(Stage $stage)
    {
        $stage->delete();

        return redirect()->route('admin.stages.index')
            ->with('success', 'Stage deleted successfully!');
    }
}
