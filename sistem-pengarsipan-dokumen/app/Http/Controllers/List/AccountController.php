<?php

namespace App\Http\Controllers\List;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AccountController extends Controller
{
    public function index()
    {
        $accounts = User::latest()->get();

        return Inertia::render('admin/account', [
            'accounts' => $accounts,
        ]);
    }

    public function update(Request $request, User $account)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        $account->name = $validated['name'];

        if (!empty($validated['password'])) {
            $account->password = \Hash::make($validated['password']);
        }

        $account->save();

        return back()->with('success', 'User berhasil diupdate');
    }

    public function toggleActive(User $account)
    {
        $account->is_active = ! $account->is_active;
        $account->save();

        return back()->with('success', 'Status user berhasil diubah');
    }

    public function destroy(User $account)
    {
        $account->delete();

        return back()->with('success', 'User berhasil dihapus');
    }
}
