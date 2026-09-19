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
            'google_maps_url' => 'nullable|url|max:500',
            'logo' => 'nullable|image|max:1024',
            'default_tax_rate' => 'nullable|numeric|min:0|max:100',
            'currency' => 'required|in:IDR',
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

        $setting->update($validated);

        return redirect()->route('settings.store.edit')->with('success', 'Pengaturan toko berhasil diperbarui.');
    }

    public function testWhatsapp(Request $request, WhatsappNotificationService $whatsapp)
    {
        $validated = $request->validate([
            'test_phone' => 'required|string|max:30',
        ]);

        $log = $whatsapp->sendTestMessage($validated['test_phone']);

        if ($log->status === 'manual') {
            return redirect()->route('settings.store.edit')
                ->with('success', 'Link test WhatsApp siap. Pesan belum dikirim otomatis.')
                ->with('whatsapp_test_url', data_get($log->response_payload, 'url'));
        }

        return redirect()->route('settings.store.edit')->with('error', 'Nomor WhatsApp tidak valid.');
    }
}
