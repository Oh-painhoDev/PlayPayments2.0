<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DisputeTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DisputeTemplateController extends Controller
{
    public function index(Request $request)
    {
        $query = DisputeTemplate::query();

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->has('dispute_type') && $request->dispute_type) {
            $query->where('dispute_type', $request->dispute_type);
        }

        if ($request->has('risk_level') && $request->risk_level) {
            $query->where('risk_level', $request->risk_level);
        }

        $query->orderBy('created_at', 'desc');

        $templates = $query->paginate(20);

        $stats = [
            'total' => DisputeTemplate::count(),
            'active' => DisputeTemplate::where('is_active', true)->count(),
            'inactive' => DisputeTemplate::where('is_active', false)->count(),
        ];

        return view('admin.dispute-templates.index', compact('templates', 'stats'));
    }

    public function create()
    {
        return view('admin.dispute-templates.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'dispute_type' => 'required|in:chargeback,fraud,unauthorized,not_received,defective,other',
            'risk_level' => 'required|in:LOW,MED,HIGH',
            'message_title' => 'nullable|string|max:255',
            'message_body' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        try {
            $template = DisputeTemplate::create([
                'name' => $request->name,
                'description' => $request->description,
                'dispute_type' => $request->dispute_type,
                'risk_level' => $request->risk_level,
                'message_title' => $request->message_title,
                'message_body' => $request->message_body,
                'is_active' => $request->has('is_active'),
            ]);

            Log::info('Dispute template created', [
                'template_id' => $template->id,
                'admin_id' => auth()->id()
            ]);

            return redirect()
                ->route('admin.setup.dispute-templates.index')
                ->with('success', 'Template criado com sucesso!');

        } catch (\Exception $e) {
            Log::error('Error creating dispute template', [
                'error' => $e->getMessage()
            ]);
            return back()
                ->withErrors(['error' => 'Erro ao criar template: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function edit(DisputeTemplate $disputeTemplate)
    {
        return view('admin.dispute-templates.edit', compact('disputeTemplate'));
    }

    public function update(Request $request, DisputeTemplate $disputeTemplate)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'dispute_type' => 'required|in:chargeback,fraud,unauthorized,not_received,defective,other',
            'risk_level' => 'required|in:LOW,MED,HIGH',
            'message_title' => 'nullable|string|max:255',
            'message_body' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        try {
            $disputeTemplate->update([
                'name' => $request->name,
                'description' => $request->description,
                'dispute_type' => $request->dispute_type,
                'risk_level' => $request->risk_level,
                'message_title' => $request->message_title,
                'message_body' => $request->message_body,
                'is_active' => $request->has('is_active'),
            ]);

            Log::info('Dispute template updated', [
                'template_id' => $disputeTemplate->id,
                'admin_id' => auth()->id()
            ]);

            return redirect()
                ->route('admin.setup.dispute-templates.index')
                ->with('success', 'Template atualizado com sucesso!');

        } catch (\Exception $e) {
            Log::error('Error updating dispute template', [
                'template_id' => $disputeTemplate->id,
                'error' => $e->getMessage()
            ]);
            return back()
                ->withErrors(['error' => 'Erro ao atualizar template: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function destroy(DisputeTemplate $disputeTemplate)
    {
        try {
            $disputeTemplate->delete();

            Log::info('Dispute template deleted', [
                'template_id' => $disputeTemplate->id,
                'admin_id' => auth()->id()
            ]);

            return redirect()
                ->route('admin.setup.dispute-templates.index')
                ->with('success', 'Template excluído com sucesso!');

        } catch (\Exception $e) {
            Log::error('Error deleting dispute template', [
                'template_id' => $disputeTemplate->id,
                'error' => $e->getMessage()
            ]);
            return back()
                ->withErrors(['error' => 'Erro ao excluir template: ' . $e->getMessage()]);
        }
    }

    public function toggle(DisputeTemplate $disputeTemplate)
    {
        try {
            $disputeTemplate->update([
                'is_active' => !$disputeTemplate->is_active
            ]);

            return response()->json([
                'success' => true,
                'is_active' => $disputeTemplate->is_active
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao alternar status'
            ], 500);
        }
    }
}
