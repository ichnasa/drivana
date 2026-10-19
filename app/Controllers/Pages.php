<?php

namespace App\Controllers;

use App\Libraries\Store;

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

    /** Unsplash photo credits for every seeded car photo. */
    public function kredit(): string
    {
        $seen = [];
        foreach (Store::all('cars') as $c) {
            if ($c['image_credit_url'] && ! isset($seen[$c['image_credit_url']])) {
                $seen[$c['image_credit_url']] = ['car' => $c['name'], 'photographer' => $c['image_credit'], 'url' => $c['image_credit_url'], 'image' => $c['image']];
            }
        }

        return view('pages/kredit', ['title' => 'Kredit foto', 'items' => array_values($seen), 'nav' => false]);
    }
}
