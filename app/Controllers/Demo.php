<?php

namespace App\Controllers;

use App\Libraries\Store;

/** Reset all demo data back to its starting point. */
class Demo extends BaseController
{
    public function reset()
    {
        Store::reset();
        session()->destroy();

        return redirect()->to('login')->with('success', 'Data demo dikembalikan ke awal.');
    }
}
