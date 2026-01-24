<?php
namespace App\Http\Controllers\AdminUI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use App\Services\PasswordResetService;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    protected PasswordResetService $passwordResetService;

    public function __construct(PasswordResetService $passwordResetService)
    {
        $this->passwordResetService = $passwordResetService;
    }

    public function index()
    {
        $users = User::with('userRole')->orderByDesc('id')->get();
        return view('adminui.users.index', compact('users'));
    }

    public function create()
    {
        // Get roles from database for dropdown
        $roles = Role::whereIn('name', ['super_admin', 'admin', 'asesor', 'komite_teknis'])
            ->orderBy('name')
            ->get();
        
        return view('adminui.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role_id' => 'required|exists:roles,id',
        ], [
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'role_id.required' => 'Role wajib dipilih.',
            'role_id.exists' => 'Role tidak valid.',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
        ]);

        return redirect()->route('adminui.users.index')->with('success', 'User berhasil ditambahkan!');
    }
    public function edit(User $user)
    {
        // Get roles from database for dropdown
        $roles = Role::whereIn('name', ['super_admin', 'admin', 'asesor', 'komite_teknis'])
            ->orderBy('name')
            ->get();
        
        return view('adminui.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role_id' => 'required|exists:roles,id',
            'password' => 'nullable|string|min:8',
        ], [
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan.',
            'role_id.required' => 'Role wajib dipilih.',
            'role_id.exists' => 'Role tidak valid.',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role_id' => $request->role_id,
        ];

        // Only update password if provided
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('adminui.users.index')->with('success', 'User berhasil diupdate!');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('adminui.users.index')->with('success', 'User berhasil dihapus!');
    }

    /**
     * Reset password for a user by admin
     * Sends password reset link to user's email
     * 
     * Security: Rate limited, audit logged, token expires in 60 minutes
     * Compliance: ISO 27001, ISO 17024
     */
    public function resetPassword(Request $request, User $user)
    {
        $result = $this->passwordResetService->sendResetByAdmin(
            $user,
            auth()->user(),
            $request
        );

        return back()->with(
            $result['success'] ? 'success' : 'error',
            $result['message']
        );
    }
}
