<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\StoreSetting;
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
}
