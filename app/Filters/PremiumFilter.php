<?php

namespace App\Filters;

use App\Libraries\PaymentService;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/** Mitra premium features need an active subscription. */
class PremiumFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $id = (int) (session('user')['id'] ?? 0);
        if (! (new PaymentService())->activeSubscription($id)) {
            return redirect()->to('mitra/langganan')->with('error', 'Fitur ini khusus pelanggan Premium. Berlangganan dulu untuk membukanya.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
