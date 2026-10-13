<?php

namespace App\Controllers;

use App\Libraries\Store;
use App\Libraries\VerificationService;

/** Streams private verification documents to their owner or an admin. */
class Files extends BaseController
{
    public function document(int $id)
    {
        $doc = Store::get('documents', $id) ?? $this->notFound();
        $u = session('user');
        if ($u['role'] !== 'admin' && (int) $u['id'] !== (int) $doc['user_id']) {
            $this->notFound();
        }
        $file = VerificationService::DIR . $doc['path'];
        if (! is_file($file)) {
            $this->notFound();
        }

        return $this->response->setHeader('Content-Type', mime_content_type($file) ?: 'application/octet-stream')
            ->setHeader('Cache-Control', 'private, max-age=300')->setHeader('X-Content-Type-Options', 'nosniff')->setBody(file_get_contents($file));
    }
}
