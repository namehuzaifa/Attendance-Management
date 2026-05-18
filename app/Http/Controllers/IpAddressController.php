<?php

namespace App\Http\Controllers;

use App\Models\IpAddress;
use App\Models\Setting;
use Illuminate\Http\Request;

class IpAddressController extends Controller
{
    public function index() {
        $ipAddresses = IpAddress::all();
        $ipRestrictionSetting = Setting::where('key', 'ip_restriction_status')->first();
        $isIpRestrictionOn = $ipRestrictionSetting ? $ipRestrictionSetting->value === 'on' : false;

        return view('modules.admin.ipAddress.list', compact('ipAddresses', 'isIpRestrictionOn'));
    }

    public function store(Request $r) {
        $r->validate([
            'ip_address' => 'required|string|unique:ip_addresses,ip_address',
            'description' => 'nullable|string'
        ]);

        IpAddress::create($r->only('ip_address', 'description'));

        return response()->json(['status' => true, 'message' => "IP Address added successfully"]);
    }

    public function update(Request $r, $id) {
        $r->validate([
            'ip_address' => 'required|string|unique:ip_addresses,ip_address,'.$id,
            'description' => 'nullable|string'
        ]);

        $ip = IpAddress::findOrFail($id);
        $ip->update($r->only('ip_address', 'description'));

        return response()->json(['status' => true, 'message' => "IP Address updated successfully"]);
    }

    public function destroy(Request $r) {
        IpAddress::findOrFail($r->id)->delete();
        return response()->json(['status' => true, 'message' => "IP Address deleted successfully"]);
    }

    public function toggleSetting(Request $r) {
        $r->validate([
            'status' => 'required|in:on,off'
        ]);

        Setting::updateOrCreate(
            ['key' => 'ip_restriction_status'],
            ['value' => $r->status]
        );

        return response()->json(['status' => true, 'message' => "IP Restriction setting updated"]);
    }
}
