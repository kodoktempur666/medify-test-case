<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class CategoryItemsController extends Controller
{
    public function index()
    {
        return view('categories.index.index');
    }

    public function search(Request $request)
    {
        $kode = $request->kode;
        $nama = $request->nama;

        $query = Category::query();

        if (!empty($kode)) {
            $query->where('kode', 'LIKE', '%' . $kode . '%');
        }
        if (!empty($nama)) {
            $query->where('nama', 'LIKE', '%' . $nama . '%');
        }

        $data = $query->select('id', 'kode', 'nama')->orderBy('id', 'desc')->get();

        return response()->json([
            'status' => 200,
            'data'   => $data,
        ]);
    }

    public function formView($method, $id = 0)
    {
        if ($method == 'new') {
            $category = (object)[];
        } else {
            $category = Category::findOrFail($id);
        }

        $data['category'] = $category;
        $data['method']   = $method;

        return view('categories.form.index', $data);
    }

    public function singleView($id)
    {
        $data['data'] = Category::with('masterItems')
            ->where('id', $id)
            ->orWhere('kode', $id)
            ->firstOrFail();

        return view('categories.single.index', $data);
    }

    public function formSubmit(Request $request, $method, $id = 0)
    {
        $rules = [
            'nama' => 'required|string|max:255',
        ];

        if ($request->filled('kode')) {
            $rules['kode'] = 'string|max:50|unique:categories,kode,' . ($method == 'edit' ? $id : 'NULL') . ',id';
        }

        $request->validate($rules);

        if ($method == 'new') {
            $category = new Category();
            if ($request->filled('kode')) {
                $kode = $request->kode;
            } else {
                $maxId = (int) Category::withTrashed()->max('id') + 1;
                $kode = 'KAT-' . str_pad($maxId, 4, '0', STR_PAD_LEFT);
            }
        } else {
            $category = Category::findOrFail($id);
            $kode = $request->filled('kode') ? $request->kode : $category->kode;
        }

        $category->kode = $kode;
        $category->nama = $request->nama;
        $category->save();

        return redirect('categories');
    }

    public function delete($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return redirect('categories');
    }

    public function pdfDownload($id)
    {
        $category = Category::with('masterItems')
            ->where('id', $id)
            ->orWhere('kode', $id)
            ->firstOrFail();

        $pdf = Pdf::loadView('categories.single.pdf', compact('category'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('Kategori_' . $category->kode . '.pdf');
    }
}
