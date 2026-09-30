<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\MasterItem;
use App\Services\SimpleExcelExporter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MasterItemsController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        return view('master_items.index.index', compact('categories'));
    }

    public function search(Request $request)
    {
        $kode = $request->kode;
        $nama = $request->nama;
        $category_id = $request->category_id;
        $hargamin = $request->hargamin;
        $hargamax = $request->hargamax;

        $data_search = MasterItem::with('categories');

        if (!empty($kode)) {
            $data_search->where('kode', $kode);
        }
        if (!empty($nama)) {
            $data_search->where('nama', 'LIKE', '%' . $nama . '%');
        }
        if (!empty($category_id)) {
            $data_search->whereHas('categories', function ($q) use ($category_id) {
                $q->where('categories.id', $category_id);
            });
        }

        // Fix filter harga min dan harga max
        if ($request->filled('hargamin')) {
            $data_search->where('harga_beli', '>=', (float) $hargamin);
        }
        if ($request->filled('hargamax')) {
            $data_search->where('harga_beli', '<=', (float) $hargamax);
        }

        $data = $data_search->orderBy('id', 'desc')->get();

        return response()->json([
            'status' => 200,
            'data'   => $data,
        ]);
    }

    public function exportExcel(Request $request)
    {
        $query = MasterItem::with('categories');

        if ($request->filled('kode')) {
            $query->where('kode', $request->kode);
        }
        if ($request->filled('nama')) {
            $query->where('nama', 'LIKE', '%' . $request->nama . '%');
        }
        if ($request->filled('category_id')) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('categories.id', $request->category_id);
            });
        }
        if ($request->filled('hargamin')) {
            $query->where('harga_beli', '>=', (float) $request->hargamin);
        }
        if ($request->filled('hargamax')) {
            $query->where('harga_beli', '<=', (float) $request->hargamax);
        }

        $items = $query->orderBy('id', 'asc')->get();

        $headers = [
            'No',
            'Nama kategori (terpisah koma)',
            'Nama items',
            'Nama supplier',
            'Harga',
            'Laba',
            'Hargajual',
        ];

        $rows = [];
        $no = 1;
        foreach ($items as $item) {
            $kategori = $item->categories->pluck('nama')->implode(', ');
            $harga = (float) $item->harga_beli;
            $laba = (float) $item->laba;
            $hargaJual = round($harga + ($harga * $laba / 100));

            $rows[] = [
                $no++,
                $kategori ?: '-',
                $item->nama,
                $item->supplier,
                $harga,
                $laba,
                $hargaJual,
            ];
        }

        $format = $request->get('format', 'xlsx');
        return SimpleExcelExporter::download('master_items', $headers, $rows, $format);
    }

    public function formView($method, $id = 0)
    {
        if ($method == 'new') {
            $item = (object)[];
            $selectedCategories = [];
        } else {
            $item = MasterItem::with('categories')->findOrFail($id);
            $selectedCategories = $item->categories->pluck('id')->toArray();
        }

        $categories = Category::all();

        return view('master_items.form.index', [
            'item'               => $item,
            'categories'         => $categories,
            'selectedCategories' => $selectedCategories,
            'method'             => $method,
        ]);
    }

    public function singleView($kode)
    {
        $data = MasterItem::with('categories')->where('kode', $kode)->firstOrFail();
        return view('master_items.single.index', compact('data'));
    }

    public function formSubmit(Request $request, $method, $id = 0)
    {
        $request->validate([
            'nama'        => 'required|string|max:255',
            'harga_beli'  => 'required|numeric|min:0',
            'laba'        => 'required|numeric|min:0',
            'supplier'    => 'required|string',
            'jenis'       => 'required|string',
            'categories'  => 'nullable|array',
            'categories.*'=> 'exists:categories,id',
            'category_id' => 'nullable|exists:categories,id',
            'foto'        => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($method == 'new') {
            $data_item = new MasterItem();
            $maxId = (int) MasterItem::withTrashed()->max('id') + 1;
            $kode = str_pad($maxId, 5, '0', STR_PAD_LEFT);
            $data_item->kode = $kode;
        } else {
            $data_item = MasterItem::findOrFail($id);
        }

        // Upload Foto
        if ($request->hasFile('foto')) {
            if ($method != 'new' && $data_item->foto && Storage::disk('public')->exists($data_item->foto)) {
                Storage::disk('public')->delete($data_item->foto);
            }

            $path = $request->file('foto')->store('master_items', 'public');
            $data_item->foto = $path;
        }

        $data_item->nama = $request->nama;
        $data_item->harga_beli = $request->harga_beli;
        $data_item->laba = $request->laba;
        $data_item->supplier = $request->supplier;
        $data_item->jenis = $request->jenis;
        $data_item->save();

        // Sync many-to-many categories
        $categoriesToSync = [];
        if ($request->has('categories')) {
            $categoriesToSync = (array) $request->categories;
        } elseif ($request->filled('category_id')) {
            $categoriesToSync = [$request->category_id];
        }
        $data_item->categories()->sync($categoriesToSync);

        return redirect('master-items');
    }

    public function delete($id)
    {
        $item = MasterItem::find($id);

        if ($item) {
            $item->delete();
        }

        return redirect('master-items');
    }

    public function updateRandomData()
    {
        $data = MasterItem::get();
        foreach ($data as $item) {
            $kode = $item->id;
            $kode = str_pad($kode, 5, '0', STR_PAD_LEFT);

            $item->harga_beli = rand(100, 1000000);
            $item->laba = rand(10, 99);
            $item->kode = $kode;
            $item->supplier = $this->getRandomSupplier();
            $item->jenis = $this->getRandomJenis();
            $item->save();
        }
        return redirect('master-items');
    }

    private function getRandomSupplier()
    {
        $array = ['Tokopaedi', 'Bukulapuk', 'TokoBagas', 'E Commurz', 'Blublu'];
        $random = rand(0, 4);
        return $array[$random];
    }

    private function getRandomJenis()
    {
        $array = ['Obat', 'Alkes', 'Matkes', 'Umum', 'ATK'];
        $random = rand(0, 4);
        return $array[$random];
    }
}
