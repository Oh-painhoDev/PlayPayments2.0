<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class WhiteLabelAnnouncementController extends Controller
{
    /**
     * Display the announcements page.
     */
    public function index()
    {
        // Verificar se a tabela existe
        if (!Schema::hasTable('announcements')) {
            return view('admin.white-label.announcements', [
                'announcements' => collect([]),
                'migration_warning' => true,
            ]);
        }

        $announcements = DB::table('announcements')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.white-label.announcements', [
            'announcements' => $announcements,
        ]);
    }

    /**
     * Store a new announcement.
     */
    public function store(Request $request)
    {
        // Verificar se a tabela existe
        if (!Schema::hasTable('announcements')) {
            return redirect()->back()
                ->with('error', 'A tabela de avisos não existe. Execute as migrations primeiro: php artisan migrate');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|in:info,success,warning,error',
            'is_active' => 'boolean',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after:starts_at',
        ]);

        try {
            DB::table('announcements')->insert([
                'title' => $request->title,
                'message' => $request->message,
                'type' => $request->type,
                'is_active' => $request->has('is_active') ? 1 : 0,
                'starts_at' => $request->starts_at ? date('Y-m-d H:i:s', strtotime($request->starts_at)) : null,
                'ends_at' => $request->ends_at ? date('Y-m-d H:i:s', strtotime($request->ends_at)) : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return redirect()->route('admin.white-label.announcements')
                ->with('success', 'Aviso criado com sucesso!');

        } catch (\Exception $e) {
            Log::error('Error creating announcement: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erro ao criar aviso: ' . $e->getMessage());
        }
    }

    /**
     * Update an announcement.
     */
    public function update(Request $request, $id)
    {
        // Verificar se a tabela existe
        if (!Schema::hasTable('announcements')) {
            return redirect()->back()
                ->with('error', 'A tabela de avisos não existe. Execute as migrations primeiro: php artisan migrate');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|in:info,success,warning,error',
            'is_active' => 'boolean',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after:starts_at',
        ]);

        try {
            DB::table('announcements')
                ->where('id', $id)
                ->update([
                    'title' => $request->title,
                    'message' => $request->message,
                    'type' => $request->type,
                    'is_active' => $request->has('is_active') ? 1 : 0,
                    'starts_at' => $request->starts_at ? date('Y-m-d H:i:s', strtotime($request->starts_at)) : null,
                    'ends_at' => $request->ends_at ? date('Y-m-d H:i:s', strtotime($request->ends_at)) : null,
                    'updated_at' => now(),
                ]);

            return redirect()->route('admin.white-label.announcements')
                ->with('success', 'Aviso atualizado com sucesso!');

        } catch (\Exception $e) {
            Log::error('Error updating announcement: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erro ao atualizar aviso: ' . $e->getMessage());
        }
    }

    /**
     * Delete an announcement.
     */
    public function destroy($id)
    {
        // Verificar se a tabela existe
        if (!Schema::hasTable('announcements')) {
            return redirect()->back()
                ->with('error', 'A tabela de avisos não existe. Execute as migrations primeiro: php artisan migrate');
        }

        try {
            DB::table('announcements')->where('id', $id)->delete();

            return redirect()->route('admin.white-label.announcements')
                ->with('success', 'Aviso excluído com sucesso!');

        } catch (\Exception $e) {
            Log::error('Error deleting announcement: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Erro ao excluir aviso: ' . $e->getMessage());
        }
    }
}
