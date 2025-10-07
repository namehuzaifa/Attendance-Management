<?php

namespace App\Http\Controllers;

use App\Models\Coaching;
use App\Models\Department;
use App\Models\Designation;
use App\Models\ShiftTiming;
use App\Models\User;
use App\Models\UserRelation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules;

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::all()->except(Auth::user()->id);
        return view('modules.admin.users.list', compact('users'));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $isEdit = false;
        $designations = Designation::all();
        $departments = Department::all();
        $ShiftTimings = ShiftTiming::all();
        $managers = User::where('user_role', 'admin')->get();

        return view('modules.admin.users.forms', compact('isEdit', 'designations', 'departments', 'ShiftTimings', 'managers'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', Rules\Password::defaults()],
        ]);

        DB ::beginTransaction();
        try {

            $request['password']  = Hash::make($request->password);
            $user = User::create(['full_name' => $request->name, 'email' => $request->email, 'password' => $request->password,
            'phone' => $request->phone, 'user_role' => $request->user_role]);

            if ($request->user_role == 'user') {
                UserRelation::create([
                    'user_id' => $user->id,
                    'department_id' => $request->department_id,
                    'designation_id' => $request->designation_id,
                    'shift_timing_id' => $request->shift_timing_id,
                    'manager_id' => $request->manager_id,
                ]);
            }

            DB::commit();
            return redirect()->route('user-list')->with(['status' => 'success', 'message' => "User add successfully"]);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with(['status' => 'failed', 'message' => $e->getMessage() ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $isEdit = true;
        $user = User::findOrFail($id);
        return view('modules.admin.users.forms', compact('id','user','isEdit'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $isEdit = true;
        $user = (Auth::user()->user_role == 'admin') ? User::findOrFail($id) : Auth::user();
        $designations = Designation::all();
        $departments = Department::all();
        $ShiftTimings = ShiftTiming::all();
        $managers = User::where('user_role', 'admin')->get();
        return view('modules.admin.users.forms', compact('id', 'user', 'isEdit','designations', 'departments', 'ShiftTimings', 'managers'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'max:255'],
            'password'  => ['sometimes', 'nullable', Rules\Password::defaults()],
        ]);

        try {
            if (!empty($request->password)) {
                $request['password']  = Hash::make($request->password);
            }

            $requestFiltered = collect([
                'full_name' => $request->name,
                'email' => $request->email,
                'password' => $request->password,
                'phone' => $request->phone,
                'user_role' => $request->user_role
            ])->filter()->all();

            User::where('id', $id)->update($requestFiltered);

            if ($request->user_role == 'user') {
                UserRelation::create([
                    'user_id' => $id,
                    'department_id' => $request->department_id,
                    'designation_id' => $request->designation_id,
                    'shift_timing_id' => $request->shift_timing_id,
                    'manager_id' => $request->manager_id,
                ]);
            }
            // User_metas::where('id', $id)->update($request->except('_token','name','email','password'));


            return redirect()->route('user-list')->with(['status' => 'success', 'message' => "User update successfully"]);

        } catch (\Exception $e) {
            return redirect()->route('user-list')->with(['status' => 'failed', 'message' => $e->getMessage() ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        User::where('id', $id)->delete();
        return redirect()->back()->with(['status' => 'success', 'message' => "User Delete successfully"]);
    }
}
