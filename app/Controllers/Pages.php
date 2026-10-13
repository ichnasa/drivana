<?php

namespace App\Controllers;

use App\Libraries\Store;

/** Public pages. */
class Pages extends BaseController
{
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
