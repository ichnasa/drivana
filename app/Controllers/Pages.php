<?php

namespace App\Controllers;


/** Public pages. */
class Pages extends BaseController
{
    /** F-01 Landing page; signed-in users go to their own home. */
    public function landing()
    {
        $u = session('user');
        if ($u) {
            return redirect()->to(\App\Libraries\Drivana::home($u['role']));
        }

        return view('pages/landing', ['title' => 'Sewa mobil online']);
    }

    public function kontak(): string
    {
        return view('pages/kontak', ['title' => 'Hubungi kami', 'nav' => false]);
    }

    public function privasi(): string
    {
        return view('pages/privasi', ['title' => 'Kebijakan privasi', 'nav' => false]);
    }

}
