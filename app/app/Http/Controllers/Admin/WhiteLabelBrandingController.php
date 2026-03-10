<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class WhiteLabelBrandingController extends Controller
{
    /**
     * Display the branding settings page.
     */
    public function index()
    {
        // Verificar se a tabela existe
        if (!Schema::hasTable('white_label_settings')) {
            return view('admin.white-label.branding', [
                'favicon' => null,
                'primary_color' => '#21b3dd',
                'dashboard_banner' => null,
                'migration_warning' => true,
            ]);
        }

        $settings = DB::table('white_label_settings')
            ->whereIn('key', [
                'favicon',
                'primary_color',
                'dashboard_banner',
                'auth_banner',
                'auth_banner_active',
                'auth_banner_side',
                'logo',
            ])
            ->get()
            ->keyBy('key');

        // Helper function to get image URL
        $getImageUrl = function($setting) {
            if (!$setting || !$setting->value) {
                return null;
            }
            // Check if it's a URL
            if (filter_var($setting->value, FILTER_VALIDATE_URL)) {
                return $setting->value;
            }
            // It's a local file
            return asset('storage/' . $setting->value);
        };

        return view('admin.white-label.branding', [
            'favicon' => $getImageUrl($settings['favicon'] ?? null),
            'favicon_type' => isset($settings['favicon']) ? $settings['favicon']->type : null,
            'primary_color' => isset($settings['primary_color']) ? $settings['primary_color']->value : '#21b3dd',
            'dashboard_banner' => $getImageUrl($settings['dashboard_banner'] ?? null),
            'dashboard_banner_type' => isset($settings['dashboard_banner']) ? $settings['dashboard_banner']->type : null,
            'auth_banner' => $getImageUrl($settings['auth_banner'] ?? null),
            'auth_banner_type' => isset($settings['auth_banner']) ? $settings['auth_banner']->type : null,
            'auth_banner_active' => isset($settings['auth_banner_active']) ? (bool)$settings['auth_banner_active']->value : false,
            'auth_banner_side' => isset($settings['auth_banner_side']) ? $settings['auth_banner_side']->value : 'left',
            'logo' => $getImageUrl($settings['logo'] ?? null),
            'logo_type' => isset($settings['logo']) ? $settings['logo']->type : null,
        ]);
    }

    /**
     * Update branding settings.
     */
    public function update(Request $request)
    {
        // Verificar se a tabela existe
        if (!Schema::hasTable('white_label_settings')) {
            return redirect()->back()
                ->with('error', 'A tabela de configurações não existe. Execute as migrations primeiro: php artisan migrate');
        }

        $request->validate([
            'primary_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'favicon' => 'nullable',
            'favicon_file' => 'nullable|file|mimes:ico,png,svg,webp|max:2048',
            'favicon_url' => 'nullable|url|max:500',
            'dashboard_banner' => 'nullable',
            'dashboard_banner_file' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
            'dashboard_banner_url' => 'nullable|url|max:500',
            'auth_banner_file' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
            'auth_banner_url' => 'nullable|url|max:500',
            'auth_banner_side' => 'nullable|in:left,right',
            'auth_banner_active' => 'nullable|boolean',
            'logo_file' => 'nullable|image|mimes:jpeg,jpg,png,webp,svg|max:2048',
            'logo_url' => 'nullable|url|max:500',
        ]);

        try {
            DB::beginTransaction();

            // Update primary color
            if ($request->has('primary_color')) {
                DB::table('white_label_settings')
                    ->updateOrInsert(
                        ['key' => 'primary_color'],
                        ['value' => $request->primary_color, 'type' => 'color', 'updated_at' => now()]
                    );
            }

            // Handle favicon - can be file or URL
            if ($request->hasFile('favicon_file')) {
                $favicon = $request->file('favicon_file');
                $faviconPath = $favicon->store('white-label', 'public');
                
                // Delete old favicon if exists (only if it's a local file)
                $oldFavicon = DB::table('white_label_settings')->where('key', 'favicon')->value('value');
                if ($oldFavicon && !filter_var($oldFavicon, FILTER_VALIDATE_URL) && Storage::disk('public')->exists($oldFavicon)) {
                    Storage::disk('public')->delete($oldFavicon);
                }

                DB::table('white_label_settings')
                    ->updateOrInsert(
                        ['key' => 'favicon'],
                        ['value' => $faviconPath, 'type' => 'file', 'updated_at' => now()]
                    );
            } elseif ($request->filled('favicon_url')) {
                // Save URL instead of file
                DB::table('white_label_settings')
                    ->updateOrInsert(
                        ['key' => 'favicon'],
                        ['value' => $request->favicon_url, 'type' => 'url', 'updated_at' => now()]
                    );
            }

            // Handle dashboard banner - can be file or URL
            if ($request->hasFile('dashboard_banner_file')) {
                $banner = $request->file('dashboard_banner_file');
                $bannerPath = $banner->store('white-label', 'public');
                
                // Delete old banner if exists (only if it's a local file)
                $oldBanner = DB::table('white_label_settings')->where('key', 'dashboard_banner')->value('value');
                if ($oldBanner && !filter_var($oldBanner, FILTER_VALIDATE_URL) && Storage::disk('public')->exists($oldBanner)) {
                    Storage::disk('public')->delete($oldBanner);
                }

                DB::table('white_label_settings')
                    ->updateOrInsert(
                        ['key' => 'dashboard_banner'],
                        ['value' => $bannerPath, 'type' => 'file', 'updated_at' => now()]
                    );
            } elseif ($request->filled('dashboard_banner_url')) {
                // Save URL instead of file
                DB::table('white_label_settings')
                    ->updateOrInsert(
                        ['key' => 'dashboard_banner'],
                        ['value' => $request->dashboard_banner_url, 'type' => 'url', 'updated_at' => now()]
                    );
            }

            // Handle auth banner - can be file or URL
            $authBannerUpdated = false;
            if ($request->hasFile('auth_banner_file')) {
                $authBanner = $request->file('auth_banner_file');
                $authBannerPath = $authBanner->store('white-label', 'public');

                $oldAuthBanner = DB::table('white_label_settings')->where('key', 'auth_banner')->value('value');
                if ($oldAuthBanner && !filter_var($oldAuthBanner, FILTER_VALIDATE_URL) && Storage::disk('public')->exists($oldAuthBanner)) {
                    Storage::disk('public')->delete($oldAuthBanner);
                }

                DB::table('white_label_settings')
                    ->updateOrInsert(
                        ['key' => 'auth_banner'],
                        ['value' => $authBannerPath, 'type' => 'file', 'updated_at' => now()]
                    );
                $authBannerUpdated = true;
            } elseif ($request->filled('auth_banner_url')) {
                DB::table('white_label_settings')
                    ->updateOrInsert(
                        ['key' => 'auth_banner'],
                        ['value' => $request->auth_banner_url, 'type' => 'url', 'updated_at' => now()]
                    );
                $authBannerUpdated = true;
            }

            // Handle auth banner active flag - sempre salva no banco (mesmo quando desmarcado)
            // O checkbox envia "1" quando marcado, e o hidden envia "0" quando desmarcado
            $authBannerActive = false;
            if ($request->has('auth_banner_active')) {
                $authBannerActive = $request->input('auth_banner_active') === '1' || $request->boolean('auth_banner_active');
            }
            
            // Se um novo banner foi enviado e não foi especificado o estado, ativa automaticamente
            if ($authBannerUpdated && !$request->has('auth_banner_active')) {
                $authBannerActive = true;
            }
            
            // Sempre salva o estado no banco de dados
            DB::table('white_label_settings')
                ->updateOrInsert(
                    ['key' => 'auth_banner_active'],
                    ['value' => $authBannerActive ? '1' : '0', 'type' => 'boolean', 'updated_at' => now()]
                );

            // Handle auth banner side
            $authBannerSide = in_array($request->auth_banner_side, ['left', 'right']) ? $request->auth_banner_side : 'left';
            DB::table('white_label_settings')
                ->updateOrInsert(
                    ['key' => 'auth_banner_side'],
                    ['value' => $authBannerSide, 'type' => 'string', 'updated_at' => now()]
                );

            // Handle logo - can be file or URL
            if ($request->hasFile('logo_file')) {
                $logo = $request->file('logo_file');
                $logoPath = $logo->store('white-label', 'public');
                
                // Delete old logo if exists (only if it's a local file)
                $oldLogo = DB::table('white_label_settings')->where('key', 'logo')->value('value');
                if ($oldLogo && !filter_var($oldLogo, FILTER_VALIDATE_URL) && Storage::disk('public')->exists($oldLogo)) {
                    Storage::disk('public')->delete($oldLogo);
                }

                DB::table('white_label_settings')
                    ->updateOrInsert(
                        ['key' => 'logo'],
                        ['value' => $logoPath, 'type' => 'file', 'updated_at' => now()]
                    );
            } elseif ($request->filled('logo_url')) {
                // Save URL instead of file
                DB::table('white_label_settings')
                    ->updateOrInsert(
                        ['key' => 'logo'],
                        ['value' => $request->logo_url, 'type' => 'url', 'updated_at' => now()]
                    );
            }

            DB::commit();

            return redirect()->route('admin.white-label.branding')
                ->with('success', 'Configurações de personalização atualizadas com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating branding settings: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erro ao atualizar configurações: ' . $e->getMessage());
        }
    }
}
