<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminRequest;
use App\Http\Requests\AdminUpdateRequest;
use App\Http\Requests\PasswordRequest;
use App\Http\Requests\ProfileRequest;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index($pagination = PAGINATION)
    {
        $admins = Admin::where('email', '!=', 'ahmed.wael7822@gmail.com')->where('deleted_at', null)->where('id', '!=', Auth::id())->paginate($pagination);
        return view('admins/show', compact('admins'));
    }

    public function add()
    {
        return view('admins/add');
    }

    public function store(AdminRequest $request)
    {
        Admin::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'password' => Hash::make($request->password),
        ]);
        return redirect(route('admin.showAll'))->with('success', __('view.adminCreated'));
    }

    public function edit($adminId)
    {
        $admin = Admin::where('deleted_at', null)->where('id', $adminId)->first();
        if (!$admin) {
            return redirect()->back()->with('error', __('view.wrong'));
        }

        return view('admins/edit', compact('admin'));
    }

    public function update(AdminUpdateRequest $request)
    {
        $admin = Admin::where('deleted_at', null)->where('id', $request->id);
        if (!$admin->first()) {
            return redirect()->back()->with('error', __('view.wrong'));
        }
        $admin->update([
            'name' => $request->name ?? $admin->first()->name,
            'email' => $request->email ?? $admin->first()->email,
            'role' => $request->role ?? $admin->first()->role,
        ]);
        return redirect()->back()->with('success', __('view.profileUpdated'));
    }

    public function editProfile()
    {
        $admin = Admin::where('deleted_at', null)->where('id', Auth::id())->first();
        if (!$admin) {
            return redirect()->back()->with('error', __('view.wrong'));
        }

        return view('admins/profile', compact('admin'));
    }

    public function updateProfile(ProfileRequest $request)
    {
        $admin = Admin::where('deleted_at', null)->where('id', Auth::id());
        if (!$admin->first()) {
            return redirect()->back()->with('error', __('view.wrong'));
        }

        if ($request->file('photo')) {
            $photo = $request->file('photo');
            $photoName = time() . '.' . $photo->getClientOriginalExtension();
            $photo->move(public_path('uploads/admins'), $photoName);
            $admin->update([
                'photo' => $photoName
            ]);
        }

        $admin->update([
            'name' => $request->name ?? $admin->first()->name,
            'email' => $request->email ?? $admin->first()->email,
        ]);
        return redirect()->back()->with('success', __('view.profileUpdated'));
    }

    public function changePasswordPage()
    {
        return view('admins/change_password');
    }

    public function changePassword(PasswordRequest $request)
    {
        $admin = Admin::where('id', Auth::id());
        if (!$admin->first()) return redirect()->back()->with('error', __('view.wrong'));

        $admin->update([
            'password' => Hash::make($request->password)
        ]);
        return redirect()->back()->with('success', __('view.ChangedSuccessfully', ['attribute' => __('view.password')]));
    }

    public function delete($adminId)
    {
        $admin = Admin::where('id', $adminId);
        if (!$admin) {
            return redirect()->back()->with('error', __('view.wrong'));
        }
        $admin->update(['deleted_at' => now()]);
        return redirect(route('admin.showAll'))->with('success', __('adminDeleted'));
    }

    public function filter(Request $request)
    {
        $pagination = $request->pagination ?? PAGINATION;
        if (!$request->data) { // if There is no searching data return all hotels
            $admins = Admin::where('deleted_at', null)->paginate($pagination, ['*'], 'page', $request->page ?? 1);
            $data = '';
        } else {
            $data = $request->data;

            $admins = Admin::where(function ($query) use ($data) {
                $query->where('id', '!=', Auth::id());
                $query->where('email', '!=', 'ahmed.wael7822@gmail.com');
                $query->where('deleted_at', null);
                $query->where('name', 'like', '%' . $data . '%');
            })
                ->orWhere(function ($query) use ($data) {
                    $query->where('id', '!=', Auth::id());
                    $query->where('email', '!=', 'ahmed.wael7822@gmail.com');
                    $query->where('deleted_at', null);
                $query->where('email', 'like', '%' . $data . '%');
                })
                ->paginate($pagination, ['*'], 'page', $request->page ?? 1);
        }
        return view('admins/show', compact('admins', 'data', 'pagination'));
    }
}
