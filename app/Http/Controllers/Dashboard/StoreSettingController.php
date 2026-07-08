<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\StoreSetting;
use App\Services\WhatsappNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StoreSettingController extends Controller
{
    public function edit()
    {
        return view('settings.store', [
            'setting' => StoreSetting::current(),
        ]);
    }

    public function update(Request $request)
    {
        $setting = StoreSetting::current();

        $validated = $request->validate([
            'store_name' => 'required|string|max:150',
            'address' => 'nullable|string|max:1000',
            'phone' => 'nullable|string|max:50',
            'logo' => 'nullable|image|max:1024',
            'default_tax_rate' => 'nullable|numeric|min:0|max:100',
            'currency' => 'required|string|max:10',
            'whatsapp_enabled' => 'nullable|boolean',
            'whatsapp_api_version' => 'nullable|string|max:20',
            'whatsapp_phone_number_id' => 'nullable|string|max:100',
            'whatsapp_access_token' => 'nullable|string',
            'whatsapp_invoice_base_url' => 'nullable|url|max:255',
            'whatsapp_payment_instructions' => 'nullable|string|max:2000',
        ]);

        if ($request->hasFile('logo')) {
            if ($setting->logo) {
                Storage::disk('public')->delete($setting->logo);
            }
            $validated['logo'] = $request->file('logo')->store('store', 'public');
        }

        $validated['default_tax_rate'] = $validated['default_tax_rate'] ?? 0;
        $validated['whatsapp_enabled'] = (bool) $request->boolean('whatsapp_enabled');
        $validated['whatsapp_api_version'] = $validated['whatsapp_api_version'] ?? 'v20.0';
        if (!$request->filled('whatsapp_access_token')) {
            unset($validated['whatsapp_access_token']);
        }

        $setting->update($validated);

        return redirect()->route('settings.store.edit')->with('success', 'Pengaturan toko berhasil diperbarui.');
    }

    public function testWhatsapp(Request $request, WhatsappNotificationService $whatsapp)
    {
        $validated = $request->validate([
            'test_phone' => 'required|string|max:30',
        ]);

        $log = $whatsapp->sendTestMessage($validated['test_phone']);

        if ($log->status === 'sent') {
            return redirect()->route('settings.store.edit')->with('success', 'Pesan test WhatsApp berhasil dikirim.');
        }

        return redirect()->route('settings.store.edit')->with('error', 'Pesan test WhatsApp gagal: ' . $log->error_message);
    }
}
