<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\MasterItem;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MasterItemsAndCategoriesTest extends TestCase
{
    use DatabaseTransactions;
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    private function createFakeImage(string $name = 'test.jpg'): UploadedFile
    {
        $jpegContent = base64_decode('/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////wgALCAABAAEBAREA/8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAgBAQABPxA=');
        return UploadedFile::fake()->createWithContent($name, $jpegContent);
    }

    /**
     * Requirement 1: Field "foto" upload image in CRUD Master Items
     */
    public function test_can_create_master_item_with_foto_and_categories()
    {
        $cat = Category::create(['kode' => 'KAT-001', 'nama' => 'Farmasi']);
        $file = $this->createFakeImage('obat.jpg');

        $response = $this->post('/master-items/form/new', [
            'nama'        => 'Amoxicillin 500mg',
            'harga_beli'  => 10000,
            'laba'        => 25,
            'supplier'    => 'Tokopaedi',
            'jenis'       => 'Obat',
            'categories'  => [$cat->id],
            'foto'        => $file,
        ]);

        $response->assertRedirect('master-items');

        $item = MasterItem::where('nama', 'Amoxicillin 500mg')->first();
        $this->assertNotNull($item);
        $this->assertNotNull($item->foto);
        Storage::disk('public')->assertExists($item->foto);

        // Verify category relation
        $this->assertTrue($item->categories->contains($cat->id));

        // View single
        $viewResponse = $this->get('/master-items/view/' . $item->kode);
        $viewResponse->assertStatus(200);
        $viewResponse->assertSee('Amoxicillin 500mg');
        $viewResponse->assertSee('Farmasi');
        $viewResponse->assertSee($item->foto);
    }

    public function test_can_update_master_item_and_replace_foto()
    {
        $file1 = $this->createFakeImage('old.jpg');
        $item = MasterItem::create([
            'kode'       => '00099',
            'nama'       => 'Item Lama',
            'harga_beli' => 20000,
            'laba'       => 10,
            'supplier'   => 'Blublu',
            'jenis'      => 'Alkes',
            'foto'       => $file1->store('master_items', 'public'),
        ]);

        $oldFoto = $item->foto;
        Storage::disk('public')->assertExists($oldFoto);

        $file2 = $this->createFakeImage('new.jpg');
        $response = $this->post('/master-items/form/edit/' . $item->id, [
            'nama'        => 'Item Baru',
            'harga_beli'  => 25000,
            'laba'        => 15,
            'supplier'    => 'Blublu',
            'jenis'       => 'Alkes',
            'foto'        => $file2,
        ]);

        $response->assertRedirect('master-items');

        $item->refresh();
        $this->assertEquals('Item Baru', $item->nama);
        $this->assertNotEquals($oldFoto, $item->foto);
        Storage::disk('public')->assertExists($item->foto);
        Storage::disk('public')->assertMissing($oldFoto);
    }

    /**
     * Requirement 2: Fix bug filter harga min dan harga max
     */
    public function test_filter_harga_min_and_max()
    {
        MasterItem::create(['kode' => '10001', 'nama' => 'Item Murah', 'harga_beli' => 1000, 'laba' => 10, 'supplier' => 'Tokopaedi', 'jenis' => 'Obat']);
        MasterItem::create(['kode' => '10002', 'nama' => 'Item Sedang', 'harga_beli' => 5000, 'laba' => 10, 'supplier' => 'Tokopaedi', 'jenis' => 'Obat']);
        MasterItem::create(['kode' => '10003', 'nama' => 'Item Mahal', 'harga_beli' => 10000, 'laba' => 10, 'supplier' => 'Tokopaedi', 'jenis' => 'Obat']);

        // Min only
        $resMin = $this->getJson('/master-items/search?hargamin=5000');
        $resMin->assertStatus(200);
        $dataMin = collect($resMin->json('data'))->pluck('nama');
        $this->assertContains('Item Sedang', $dataMin);
        $this->assertContains('Item Mahal', $dataMin);
        $this->assertNotContains('Item Murah', $dataMin);

        // Max only
        $resMax = $this->getJson('/master-items/search?hargamax=5000');
        $resMax->assertStatus(200);
        $dataMax = collect($resMax->json('data'))->pluck('nama');
        $this->assertContains('Item Murah', $dataMax);
        $this->assertContains('Item Sedang', $dataMax);
        $this->assertNotContains('Item Mahal', $dataMax);

        // Both Min and Max
        $resBoth = $this->getJson('/master-items/search?hargamin=2000&hargamax=8000');
        $resBoth->assertStatus(200);
        $dataBoth = collect($resBoth->json('data'))->pluck('nama');
        $this->assertContains('Item Sedang', $dataBoth);
        $this->assertNotContains('Item Murah', $dataBoth);
        $this->assertNotContains('Item Mahal', $dataBoth);

        // Min 0
        $resZero = $this->getJson('/master-items/search?hargamin=0&hargamax=2000');
        $resZero->assertStatus(200);
        $dataZero = collect($resZero->json('data'))->pluck('nama');
        $this->assertContains('Item Murah', $dataZero);
    }

    /**
     * Requirement 3: CRUD "Kategori Items" and many-to-many
     */
    public function test_categories_crud_and_single_view_with_items()
    {
        // 1. Create Category
        $response = $this->post('/categories/form/new', [
            'kode' => 'KAT-TEST',
            'nama' => 'Kategori Pengujian',
        ]);
        $response->assertRedirect('categories');

        $cat = Category::where('kode', 'KAT-TEST')->first();
        $this->assertNotNull($cat);
        $this->assertEquals('Kategori Pengujian', $cat->nama);

        // Filter index search
        $searchRes = $this->getJson('/categories/search?nama=Pengujian');
        $searchRes->assertStatus(200);
        $this->assertCount(1, $searchRes->json('data'));

        // Attach an item to this category
        $item = MasterItem::create([
            'kode'       => '11111',
            'nama'       => 'Barang Uji',
            'harga_beli' => 15000,
            'laba'       => 20,
            'supplier'   => 'TokoBagas',
            'jenis'      => 'Matkes',
        ]);
        $cat->masterItems()->attach($item->id);

        // View single category - should list the item
        $singleView = $this->get('/categories/view/' . $cat->id);
        $singleView->assertStatus(200);
        $singleView->assertSee('KAT-TEST');
        $singleView->assertSee('Kategori Pengujian');
        $singleView->assertSee('Barang Uji');
        $singleView->assertSee('15.000');

        // Edit Category
        $editResponse = $this->post('/categories/form/edit/' . $cat->id, [
            'nama' => 'Kategori Pengujian Diperbarui',
        ]);
        $editResponse->assertRedirect('categories');
        $cat->refresh();
        $this->assertEquals('Kategori Pengujian Diperbarui', $cat->nama);

        // Delete Category
        $delResponse = $this->get('/categories/delete/' . $cat->id);
        $delResponse->assertRedirect('categories');
        $this->assertSoftDeleted('categories', ['id' => $cat->id]);
    }

    public function test_navbar_has_links_for_master_items_and_categories()
    {
        $response = $this->get('/master-items');
        $response->assertStatus(200);
        $response->assertSee(url('master-items'));
        $response->assertSee(url('categories'));
    }

    /**
     * Requirement 4: PDF download on single category view
     */
    public function test_category_pdf_download()
    {
        $cat = Category::create(['kode' => 'KAT-PDF', 'nama' => 'Kategori Dokumen PDF']);
        $item = MasterItem::create([
            'kode'       => '22222',
            'nama'       => 'Alat Suntik 3ml',
            'harga_beli' => 8000,
            'laba'       => 25,
            'supplier'   => 'E Commurz',
            'jenis'      => 'Alkes',
        ]);
        $cat->masterItems()->attach($item->id);

        $response = $this->get('/categories/pdf-download/' . $cat->id);
        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('Kategori_KAT-PDF.pdf', $response->headers->get('Content-Disposition'));
    }

    /**
     * Requirement 5: Master items Excel download
     */
    public function test_master_items_excel_download()
    {
        $cat = Category::create(['kode' => 'KAT-EXC', 'nama' => 'Kategori Excel']);
        $item = MasterItem::create([
            'kode'       => '33333',
            'nama'       => 'Betadine 60ml',
            'harga_beli' => 12000,
            'laba'       => 20,
            'supplier'   => 'Blublu',
            'jenis'      => 'Obat',
        ]);
        $item->categories()->attach($cat->id);

        $response = $this->get('/master-items/export-excel');
        $response->assertStatus(200);
        $this->assertStringContainsString('master_items.xlsx', $response->headers->get('Content-Disposition'));

        // Test XLS format as well
        $responseXls = $this->get('/master-items/export-excel?format=xls');
        $responseXls->assertStatus(200);
        $this->assertStringContainsString('master_items.xls', $responseXls->headers->get('Content-Disposition'));
        $content = $responseXls->getContent();
        $this->assertStringContainsString('No', $content);
        $this->assertStringContainsString('Nama kategori', $content);
        $this->assertStringContainsString('Nama items', $content);
        $this->assertStringContainsString('Nama supplier', $content);
        $this->assertStringContainsString('Harga', $content);
        $this->assertStringContainsString('Laba', $content);
        $this->assertStringContainsString('Hargajual', $content);
        $this->assertStringContainsString('Kategori Excel', $content);
        $this->assertStringContainsString('Betadine 60ml', $content);
    }
}
