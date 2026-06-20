<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\NodeStatus;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    /**
     * Show the login page.
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectUser(Auth::user());
        }

        // Get node statuses and statistics for the left panel of the login screen
        $nodeStatuses = [];
        try {
            $nodeStatuses = DB::connection('mysql')->table('node_status')
                ->join('branches', 'node_status.branch_id', '=', 'branches.id')
                ->select('branches.name', 'node_status.node_status')
                ->get();
        } catch (\Exception $e) {
            // Fallback if DB setup is running
        }

        return view('auth.login', compact('nodeStatuses'));
    }

    /**
     * Handle a login request.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $remember = $request->has('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            
            // Log activity
            try {
                $user = Auth::user();
                DB::connection('mysql')->table('activity_logs')->insert([
                    'branch_id' => $user->branch_id,
                    'user_id' => $user->id,
                    'activity' => 'Login',
                    'description' => "Pengguna {$user->name} berhasil masuk sistem.",
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (\Exception $e) {}

            return $this->redirectUser(Auth::user());
        }

        return back()->withErrors([
            'email' => 'Email atau password yang Anda masukkan salah.',
        ])->onlyInput('email');
    }

    /**
     * Quick login feature for easy coursework testing.
     */
    public function quickLogin(string $role, $branchId = null)
    {
        $query = User::where('role', $role);
        
        if ($branchId) {
            $query->where('branch_id', $branchId);
        } else {
            $query->whereNull('branch_id');
        }

        $user = $query->first();

        if ($user) {
            Auth::login($user);
            request()->session()->regenerate();

            // Log activity
            try {
                DB::connection('mysql')->table('activity_logs')->insert([
                    'branch_id' => $user->branch_id,
                    'user_id' => $user->id,
                    'activity' => 'Login Cepat',
                    'description' => "Pengguna {$user->name} berhasil masuk sistem menggunakan fitur Login Cepat.",
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (\Exception $e) {}

            return $this->redirectUser($user);
        }

        return redirect()->route('login')->with('error', 'Akun demo tidak ditemukan.');
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request)
    {
        // Log activity before logging out
        try {
            if (Auth::check()) {
                $user = Auth::user();
                DB::connection('mysql')->table('activity_logs')->insert([
                    'branch_id' => $user->branch_id,
                    'user_id' => $user->id,
                    'activity' => 'Logout',
                    'description' => "Pengguna {$user->name} keluar dari sistem.",
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        } catch (\Exception $e) {}

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar dari sistem.');
    }

    /**
     * Redirect users based on their role.
     */
    protected function redirectUser($user)
    {
        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('kasir.pos');
    }
}
