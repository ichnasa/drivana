<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

abstract class BaseController extends Controller
{
    /** @var CLIRequest|IncomingRequest */
    protected $request;

    protected $helpers = ['form', 'url', 'ui'];

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
    }

    /** Fresh row of the signed-in user (the session only keeps id + role). */
    protected function me(): array
    {
        static $row = null;
        if ($row === null) {
            $row = \App\Libraries\Store::get('users', (int) (session('user')['id'] ?? 0)) ?: [];
        }

        return $row;
    }

    protected function role(): string
    {
        return session('user')['role'] ?? 'customer';
    }

    /** Redirect back to a form with field errors and the previous input (never passwords). */
    protected function backWith(string $to, array $errors)
    {
        $old = $this->request->getPost() ?: ($this->request->getGet() ?: []);
        unset($old['password'], $old['current'], $old['confirm'], $old['csrf_test_name']);

        return redirect()->to($to)->with('errors', $errors)->with('old', $old);
    }

    protected function notFound(): never
    {
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
    }
}
