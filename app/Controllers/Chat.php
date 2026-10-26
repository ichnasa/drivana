<?php

namespace App\Controllers;

use App\Libraries\Drivana;
use App\Libraries\FormRules;
use App\Libraries\Store;

/** Customer ↔ Mitra chat, shared by both roles (route prefix decides the layout). */
class Chat extends BaseController
{
    private function conversation(int $id): array
    {
        $me = (int) session('user')['id'];
        $c = Store::get('conversations', $id);
        if (! $c || ! in_array($me, [(int) $c['customer_id'], (int) $c['mitra_id']], true)) {
            $this->notFound();
        }
        $customer = Store::get('users', (int) $c['customer_id']);
        $mitra = Store::get('users', (int) $c['mitra_id']);
        $car = $c['car_id'] ? Store::get('cars', (int) $c['car_id']) : null;
        $isMitra = $this->role() === 'mitra';
        [$other, $sub] = $this->counterpart($c, $isMitra, $customer, $mitra);

        return $c + [
            'me' => $me, 'customer_name' => $customer['name'], 'mitra_name' => $mitra['name'], 'business_name' => $mitra['business_name'],
            'car_name' => $car['name'] ?? null, 'car_image' => $car['image'] ?? null, 'car_plate' => $car['plate'] ?? null,
            'other_name' => $other, 'sub_label' => $sub,
            'other_id' => $isMitra ? (int) $c['customer_id'] : (int) $c['mitra_id'],
            'other_online' => $this->online($isMitra ? $customer : $mitra),
        ];
    }

    /** [display name, role label] of the person on the other side of the conversation. */
    private function counterpart(array $c, bool $isMitra, array $customer, array $mitra): array
    {
        if ($isMitra) {
            return [$customer['name'], 'Penyewa'];
        }

        return [$mitra['business_name'] ?: $mitra['name'], 'Mitra rental'];
    }

    /** "Online" = signed in during the last 30 minutes. */
    private function online(array $u): bool
    {
        return ! empty($u['last_login_at']) && strtotime($u['last_login_at']) > time() - 1800;
    }

    private function base(): string
    {
        return $this->role() === 'mitra' ? 'mitra/pesan' : 'chat';
    }

    private function messages(int $convId): array
    {
        $rows = Store::where('messages', fn ($m) => (int) $m['conversation_id'] === $convId);
        usort($rows, fn ($a, $b) => $a['id'] <=> $b['id']);

        return $rows;
    }

    private function markRead(int $convId, int $me): void
    {
        foreach ($this->messages($convId) as $m) {
            if ((int) $m['sender_id'] !== $me && $m['read_at'] === null) {
                Store::update('messages', (int) $m['id'], ['read_at' => Drivana::now()]);
            }
        }
    }

    public function index(): string
    {
        $me = (int) session('user')['id'];
        $isMitra = $this->role() === 'mitra';
        $rows = [];
        foreach (Store::where('conversations', fn ($c) => (int) $c[$isMitra ? 'mitra_id' : 'customer_id'] === $me) as $c) {
            $msgs = $this->messages((int) $c['id']);
            [$name] = $this->counterpart($c, $isMitra, Store::get('users', (int) $c['customer_id']), Store::get('users', (int) $c['mitra_id']));
            $rows[] = [
                'id' => $c['id'], 'last_message_at' => $c['last_message_at'], 'other_name' => $name,
                'car_name' => $c['car_id'] ? (Store::get('cars', (int) $c['car_id'])['name'] ?? null) : null,
                'last_body' => $msgs ? end($msgs)['body'] : '', 'unread' => count(array_filter($msgs, fn ($m) => (int) $m['sender_id'] !== $me && $m['read_at'] === null)),
            ];
        }
        $q = mb_strtolower(trim((string) $this->request->getGet('q')));
        $all = count($rows);
        if ($q !== '') {
            $rows = array_filter($rows, fn ($r) => str_contains(mb_strtolower($r['other_name'] . ' ' . $r['car_name'] . ' ' . $r['last_body']), $q));
        }
        usort($rows, fn ($a, $b) => strcmp($b['last_message_at'], $a['last_message_at']));

        return view('chat/index', ['title' => 'Chat', 'role' => $this->role(), 'active' => $isMitra ? 'pesan' : 'chat', 'list' => $rows, 'base' => $this->base(), 'q' => $q, 'total' => $all]);
    }

    /** Customer opens (or creates) a conversation with the owner of a car. */
    public function start(int $mitraId)
    {
        $mitra = Store::get('users', $mitraId);
        if (! $mitra || $mitra['role'] !== 'mitra' || $mitra['status'] !== 'active') {
            $this->notFound();
        }
        $car = (int) $this->request->getGet('mobil') ?: null;
        $me = (int) session('user')['id'];
        $c = Store::first('conversations', fn ($x) => (int) $x['customer_id'] === $me && (int) $x['mitra_id'] === $mitraId);
        if (! $c) {
            $id = Store::insert('conversations', ['customer_id' => $me, 'mitra_id' => $mitraId, 'car_id' => $car, 'created_at' => Drivana::now(), 'last_message_at' => Drivana::now()]);
        } else {
            $id = (int) $c['id'];
            if ($car) {
                Store::update('conversations', $id, ['car_id' => $car]);
            }
        }

        return redirect()->to('chat/' . $id);
    }


    /** Mitra opens (or creates) a conversation with a customer (from a reservation). */
    public function startMitra(int $customerId)
    {
        $cust = Store::get('users', $customerId);
        if (! $cust || $cust['role'] !== 'customer') {
            $this->notFound();
        }
        $me = (int) session('user')['id'];
        $c = Store::first('conversations', fn ($x) => (int) $x['customer_id'] === $customerId && (int) $x['mitra_id'] === $me);
        $id = $c ? (int) $c['id'] : Store::insert('conversations', ['customer_id' => $customerId, 'mitra_id' => $me, 'car_id' => (int) $this->request->getGet('mobil') ?: null, 'created_at' => Drivana::now(), 'last_message_at' => Drivana::now()]);

        return redirect()->to('mitra/pesan/' . $id);
    }

    public function show(int $id): string
    {
        $c = $this->conversation($id);
        $this->markRead($id, $c['me']);

        return view('chat/room', ['title' => $c['other_name'], 'role' => $this->role(), 'c' => $c, 'msgs' => $this->messages($id), 'base' => $this->base(), 'nav' => false]);
    }

    public function call(int $id): string
    {
        $c = $this->conversation($id);

        return view('chat/telepon', ['title' => 'Panggilan', 'c' => $c, 'base' => $this->base(), 'nav' => false, 'role' => $this->role()]);
    }

    public function send(int $id)
    {
        $c = $this->conversation($id);
        $wantsJson = $this->request->isAJAX() || str_contains((string) $this->request->getHeaderLine('Accept'), 'application/json');
        if (! $this->validate(FormRules::chat())) {
            $err = $this->validator->getErrors();

            return $wantsJson ? $this->response->setStatusCode(422)->setJSON(['ok' => false, 'error' => $err['body']]) : $this->backWith($this->base() . '/' . $id, $err);
        }
        $mid = Store::insert('messages', ['conversation_id' => $id, 'sender_id' => $c['me'], 'body' => trim((string) $this->request->getPost('body')), 'kind' => 'text', 'created_at' => Drivana::now(), 'read_at' => null]);
        Store::update('conversations', $id, ['last_message_at' => Drivana::now()]);

        return $wantsJson
            ? $this->response->setJSON(['ok' => true, 'message' => $this->format(Store::get('messages', $mid), $c['me'], $id)])
            : redirect()->to($this->base() . '/' . $id);
    }

    /** Polling endpoint: messages newer than ?after=ID, marked as read. */
    public function poll(int $id)
    {
        $c = $this->conversation($id);
        $after = (int) $this->request->getGet('after');
        $rows = array_values(array_filter($this->messages($id), fn ($m) => $m['id'] > $after));
        $this->markRead($id, $c['me']);

        return $this->response->setJSON(['messages' => array_map(fn ($m) => $this->format($m, $c['me']), $rows)]);
    }

    private function format(array $m, int $me): array
    {
        return ['id' => (int) $m['id'], 'from' => (int) $m['sender_id'] === $me ? 'me' : 'them', 'body' => $m['body'], 'time' => date('H.i', strtotime($m['created_at']))];
    }
}
