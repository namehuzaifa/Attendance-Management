<?php

namespace App\Http\Controllers;

use App\Models\Designation;
use Illuminate\Http\Request;

class DesignationController extends Controller
{
    public function index() {
        $designations = Designation::all();
        return view('modules.admin.designation.list', compact('designations'));
    }

    public function store(Request $r) {
        $r->validate(['name'=>'required|unique:designations']);
        $data = Designation::create($r->only('name'));
        return response()->json(['status' => true, 'message' => "Designation added successfully", 'data' => $data]);
    }

    public function update(Request $r, $id) {
        $r->validate(['name'=>'required|unique:designations,name,'.$id]);
        $d = Designation::findOrFail($id);
        $d->update($r->only('name'));
        return $d;
    }

    public function destroy(Request $r) {
        Designation::findOrFail($r->id)->delete();
        return response()->json(['status' => true, 'message' => "Designation Deleted successfully"]);
    }
}
