<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
     public function index() {
        $departments = Department::all();
        return view('modules.admin.department.list', compact('departments'));
    }

    public function store(Request $r) {
        $r->validate(['name'=>'required|unique:departments']);
        $data =  Department::create($r->only('name'));
        return response()->json(['status' => true, 'message' => "Department added successfully", 'data' => $data]);
    }

    public function update(Request $r, $id) {
        $r->validate(['name'=>'required|unique:departments,name,'.$id]);
        $d = Department::findOrFail($id);
        $d->update($r->only('name'));
        return $d;
    }

    public function destroy(Request $r) {
        Department::findOrFail($r->id)->delete();
        response()->json(['message'=>'Deleted']);
        return response()->json(['status' => true, 'message' => "Department Deleted successfully"]);
    }
}
