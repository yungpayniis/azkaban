<?php

namespace App\Http\Controllers;

use App\Models\JobClass;
use Illuminate\Http\Request;

class JobClassController extends Controller
{
    public function index()
    {
        $jobClasses = JobClass::with('parent')
            ->orderBy('tier')
            ->orderBy('name')
            ->get();

        return view('job-classes.index', compact('jobClasses'));
    }

    public function create()
    {
        $parents = JobClass::orderBy('tier')->orderBy('name')->get();

        return view('job-classes.create', compact('parents'));
    }

    public function store(Request $request)
    {
        $data = $this->validatePayload($request);

        if ((int) $data['tier'] === 1) {
            $data['parent_id'] = null;
        }

        JobClass::create($data);

        return redirect()
            ->route('job-classes.index')
            ->with('status', 'เพิ่มอาชีพเรียบร้อย');
    }

    public function show(JobClass $jobClass)
    {
        $jobClass->load('parent', 'children');

        return view('job-classes.show', compact('jobClass'));
    }

    public function edit(JobClass $jobClass)
    {
        $parents = JobClass::where('id', '!=', $jobClass->id)
            ->orderBy('tier')
            ->orderBy('name')
            ->get();

        return view('job-classes.edit', compact('jobClass', 'parents'));
    }

    public function update(Request $request, JobClass $jobClass)
    {
        $data = $this->validatePayload($request, $jobClass->id);

        if ((int) $data['tier'] === 1) {
            $data['parent_id'] = null;
        }

        $jobClass->update($data);

        return redirect()
            ->route('job-classes.index')
            ->with('status', 'อัปเดตอาชีพเรียบร้อย');
    }

    public function destroy(JobClass $jobClass)
    {
        $jobClass->delete();

        return redirect()
            ->route('job-classes.index')
            ->with('status', 'ลบอาชีพเรียบร้อย');
    }

    private function validatePayload(Request $request, ?int $ignoreId = null): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'tier' => ['required', 'integer', 'between:1,4'],
            'parent_id' => ['nullable', 'integer', 'exists:job_classes,id'],
            'color' => ['nullable', 'string', 'max:15', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'force_dark_text' => ['sometimes', 'boolean'],
        ];

        return $request->validate($rules);
    }
}
