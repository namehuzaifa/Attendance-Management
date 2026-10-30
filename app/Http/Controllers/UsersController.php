<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Designation;
use App\Models\ShiftTiming;
use App\Models\User;
use App\Models\UserRelation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $search = $request->get('q');
        $role   = $request->get('role');

        $users = User::with('relation.department', 'relation.designation', 'relation.shiftTiming')
            ->where('id', '!=', Auth::id())
            ->when($search, fn ($q) => $q->where(fn ($w) => $w
                ->where('full_name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('employee_code', 'like', "%{$search}%")))
            ->when($role, fn ($q) => $q->where('user_role', $role))
            ->orderBy('full_name')
            ->paginate(20)
            ->appends($request->except('page'));

        return view('modules.admin.users.list', compact('users', 'search', 'role'));
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
            'name'            => ['required', 'string', 'max:255'],
            'email'           => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'employee_code'   => ['nullable', 'string', 'max:40', 'unique:users,employee_code'],
            'phone'           => ['nullable', 'string', 'max:30'],
            'joining_date'    => ['nullable', 'date'],
            'user_role'       => ['required', Rule::in(['user', 'manager', 'admin'])],
            'password'        => ['required', 'confirmed', Rules\Password::defaults()],
            'department_id'   => ['required_if:user_role,user', 'nullable', 'exists:departments,id'],
            'designation_id'  => ['required_if:user_role,user', 'nullable', 'exists:designations,id'],
            'shift_timing_id' => ['required_if:user_role,user', 'nullable', 'exists:shift_timings,id'],
            'manager_id'      => ['required_if:user_role,user', 'nullable', 'exists:users,id'],
        ]);

        DB::beginTransaction();

        try {
            $user = User::create([
                'full_name'     => $request->name,
                'employee_code' => $request->employee_code,
                'email'         => $request->email,
                'password'      => Hash::make($request->password),
                'phone'         => $request->phone,
                'joining_date'  => $request->joining_date ?: null,
                'user_role'     => $request->user_role,
                'is_active'     => true,
            ]);

            if ($request->user_role === 'user') {
                UserRelation::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'department_id'   => $request->department_id,
                        'designation_id'  => $request->designation_id,
                        'shift_timing_id' => $request->shift_timing_id,
                        'manager_id'      => $request->manager_id,
                    ]
                );
            }

            DB::commit();

            return redirect()->route('user-list')->with(['status' => 'success', 'message' => 'User added successfully']);
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->withInput()->with(['status' => 'failed', 'message' => $e->getMessage()]);
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
        $user = Auth::user()->isAdmin() ? User::findOrFail($id) : Auth::user();

        // The form posts to this id, so keep it tied to the record actually
        // being shown rather than whatever was typed in the URL.
        $id = $user->id;

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
        $actor = Auth::user();

        // Anyone signed in could previously post to this route with another
        // id — editing a colleague, or handing themselves the admin role.
        if (!$actor->isAdmin() && (int) $id !== (int) $actor->id) {
            abort(403);
        }

        $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($id)],
            'phone'     => ['nullable', 'string', 'max:30'],
            'password'  => ['sometimes', 'nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        DB::beginTransaction();

        try {
            $user = User::findOrFail($id);

            $payload = [
                'full_name' => $request->name,
                'email'     => $request->email,
                'phone'     => $request->phone,
            ];

            if (!empty($request->password)) {
                $payload['password'] = Hash::make($request->password);
            }

            // Role, posting and activation are admin-only decisions.
            if ($actor->isAdmin()) {
                $payload['user_role']     = $request->user_role ?: $user->user_role;
                $payload['employee_code'] = $request->employee_code;
                $payload['joining_date']  = $request->joining_date ?: null;
                $payload['is_active']     = $request->boolean('is_active');

                if ($payload['user_role'] !== 'admin' && $user->isAdmin() && $this->isLastAdmin($user)) {
                    DB::rollBack();

                    return redirect()->back()->with([
                        'status'  => 'failed',
                        'message' => 'You cannot remove the last administrator.',
                    ]);
                }
            }

            $user->update($payload);

            if ($actor->isAdmin() && ($payload['user_role'] ?? $user->user_role) === 'user') {
                UserRelation::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'department_id'   => $request->department_id,
                        'designation_id'  => $request->designation_id,
                        'shift_timing_id' => $request->shift_timing_id,
                        'manager_id'      => $request->manager_id,
                    ]
                );
            }

            DB::commit();

            return redirect()->back()->with(['status' => 'success', 'message' => 'User updated successfully']);
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with(['status' => 'failed', 'message' => $e->getMessage()]);
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
        $user = User::findOrFail($id);

        if ((int) $user->id === (int) Auth::id()) {
            return redirect()->back()->with([
                'status'  => 'failed',
                'message' => 'You cannot delete your own account.',
            ]);
        }

        if ($user->isAdmin() && $this->isLastAdmin($user)) {
            return redirect()->back()->with([
                'status'  => 'failed',
                'message' => 'You cannot delete the last administrator.',
            ]);
        }

        $user->delete();

        return redirect()->back()->with(['status' => 'success', 'message' => 'User deleted successfully']);
    }

    /**
     * Deleting or demoting the only admin would leave the portal with nobody
     * able to administer it.
     */
    private function isLastAdmin(User $user): bool
    {
        return User::where('user_role', 'admin')->where('id', '!=', $user->id)->doesntExist();
    }
}
