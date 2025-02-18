<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\RolePolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ModuleController extends Controller
{
    private function getUserPermissions()
    {
        $userRole = auth()->user()->role;
        return RolePolicy::whereHas('role', function($query) use ($userRole) {
            $query->where('RoleName', $userRole);
        })->where('Module', 'Modules')->first();
    }

    public function index()
    {
        try {
            $userPermissions = $this->getUserPermissions();
            $modules = Module::orderBy('ModuleName')->get();

            return view('modules.index', [
                'modules' => $modules,
                'userPermissions' => $userPermissions
            ]);

        } catch (\Exception $e) {
            \Log::error('Error loading modules: ' . $e->getMessage());
            return back()->with('error', 'Error loading modules: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'ModuleName' => 'required|string|max:255|unique:modules,ModuleName'
            ]);

            Module::create([
                'ModuleName' => $request->ModuleName
            ]);

            return redirect()->route('modules.index')
                ->with('success', 'Module added successfully');

        } catch (\Exception $e) {
            \Log::error('Error adding module: ' . $e->getMessage());
            return back()->with('error', 'Failed to add module: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'ModuleName' => 'required|string|max:255|unique:modules,ModuleName,' . $id . ',ModuleId'
            ]);

            $module = Module::findOrFail($id);
            $module->update([
                'ModuleName' => $request->ModuleName
            ]);

            return redirect()->route('modules.index')
                ->with('success', 'Module updated successfully');

        } catch (\Exception $e) {
            \Log::error('Error updating module: ' . $e->getMessage());
            return back()->with('error', 'Failed to update module: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $module = Module::findOrFail($id);
            $module->delete();

            return redirect()->route('modules.index')
                ->with('success', 'Module deleted successfully');

        } catch (\Exception $e) {
            \Log::error('Error deleting module: ' . $e->getMessage());
            return back()->with('error', 'Failed to delete module: ' . $e->getMessage());
        }
    }
} 