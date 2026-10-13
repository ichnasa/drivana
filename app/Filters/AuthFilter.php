<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Usage in routes: ['filter' => 'auth:customer'] | 'auth:mitra' | 'auth:admin' | 'auth:any'
 * Re-checks the account on every request, so deactivating a user takes effect immediately.
 */
class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $role = $arguments[0] ?? 'customer';
        $sess = session('user');

        if (! $sess) {
            return redirect()->to($role === 'mitra' ? 'mitra/login' : 'login')->with('error', 'Masuk dulu untuk melanjutkan.');
        }

        $row = \App\Libraries\Store::get('users', (int) $sess['id']);
        if (! $row || $row['status'] !== 'active') {
            session()->destroy();

            return redirect()->to('login')->with('error', 'Akun tidak aktif. Hubungi admin Drivana.');
        }

        if ($role !== 'any' && $row['role'] !== $role) {
            return redirect()->to(\App\Libraries\Drivana::home($row['role']));   // signed in, but wrong area
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
