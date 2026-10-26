<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * @internal
 */
final class FeatureTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testLandingPage(): void
    {
        $result = $this->get('/');
        $result->assertStatus(200);
        $result->assertSee('Sewa mobil online');
        $result->assertDontSee('Chat langsung dengan mitra dan pengemudi');
        $result->assertSee('Chat langsung dengan mitra');
    }

    public function testPhotoCreditsRouteRemoved(): void
    {
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        $this->get('kredit');
    }

    public function testComplaintsHelpCenterRouteRemoved(): void
    {
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        $this->withSession(['user' => ['id' => 1, 'role' => 'customer', 'status' => 'active']])->get('bantuan');
    }

    public function testAdminKeluhanRouteRemoved(): void
    {
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        $this->withSession(['user' => ['id' => 1, 'role' => 'admin', 'status' => 'active']])->get('admin/keluhan');
    }

    public function testChatDriverRouteRemoved(): void
    {
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        $this->withSession(['user' => ['id' => 1, 'role' => 'customer', 'status' => 'active']])->get('chat/pengemudi/DRV-123');
    }

    public function testCustomerRoutesLoad(): void
    {
        $customer = \App\Libraries\Store::first('users', fn ($u) => $u['role'] === 'customer');
        $session = ['user' => $customer];

        $res1 = $this->withSession($session)->get('beranda');
        $res1->assertStatus(200);

        $res2 = $this->withSession($session)->get('chat');
        $res2->assertStatus(200);

        $res3 = $this->withSession($session)->get('profil');
        $res3->assertStatus(200);
        $res3->assertDontSee('Bantuan dan Keluhan');

        $res4 = $this->withSession($session)->get('pesanan');
        $res4->assertStatus(200);
    }

    public function testAdminRoutesLoad(): void
    {
        $admin = \App\Libraries\Store::first('users', fn ($u) => $u['role'] === 'admin');
        $session = ['user' => $admin];

        $res1 = $this->withSession($session)->get('admin/dashboard');
        $res1->assertStatus(200);
        $res1->assertDontSee('admin/keluhan');

        $res2 = $this->withSession($session)->get('admin/pengaturan');
        $res2->assertStatus(200);
        $res2->assertDontSee('admin/keluhan');

        $res3 = $this->withSession($session)->get('admin/aktivitas');
        $res3->assertStatus(200);
        $res3->assertDontSee('admin/keluhan');
    }

    public function testPesananDetailWithDriverLoadsWithoutDriverChat(): void
    {
        // Find a booking with_driver
        $booking = \App\Libraries\Store::first('bookings', fn ($b) => $b['with_driver'] && in_array($b['status'], ['dikonfirmasi', 'aktif'], true));
        $customer = \App\Libraries\Store::get('users', (int) $booking['customer_id']);

        $res = $this->withSession(['user' => $customer])->get('pesanan/' . $booking['code']);
        $res->assertStatus(200);
        $res->assertDontSee('Chat pengemudi');
    }

    public function testPublicPagesLoad(): void
    {
        $res1 = $this->get('kontak');
        $res1->assertStatus(200);
        $res1->assertDontSee('menu Bantuan');

        $res2 = $this->get('privasi');
        $res2->assertStatus(200);
        $res2->assertDontSee('menu Bantuan');
    }

    public function testMitraRoutesLoad(): void
    {
        $mitra = \App\Libraries\Store::first('users', fn ($u) => $u['role'] === 'mitra');
        $session = ['user' => $mitra];

        $res1 = $this->withSession($session)->get('mitra/dashboard');
        $res1->assertStatus(200);

        $res2 = $this->withSession($session)->get('mitra/pesan');
        $res2->assertStatus(200);

        $res3 = $this->withSession($session)->get('mitra/profil');
        $res3->assertStatus(200);
        $res3->assertDontSee('Bantuan dan Keluhan');
    }
}
