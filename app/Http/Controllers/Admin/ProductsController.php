<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Products;
use App\Models\ProductsPhoto;
use App\Repositories\ProductRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProductsController extends Controller
{
    private $ProductRepository;

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function __construct()
    {
        parent::__construct();
        $this->ProductRepository = app(ProductRepository::class);
    }

    public function index()
    {
        $products = $this->ProductRepository->getAllWithPaginate(15);

        return view('admin.product.index', [
            'products' => $products
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|View|Response
     */
    public function create()
    {
        $product = new Products();

        return view('admin.product.create', [
            'product' => $product,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Application|Factory|View|RedirectResponse
     */
    public function store(Request $request)
    {
        $product = new Products();
        $product->status = $request->input('status');
        $product->s = $request->input('s');
        $product->m = $request->input('m');
        $product->l = $request->input('l');
        $product->xl = $request->input('xl');
        $product->xxl = $request->input('xxl');
        $product->h1 = $request->input('h1');
        $product->title = $request->input('title');
        $product->description = $request->input('description');
        $product->characteristics = $request->input('characteristics');
        $product->cost = $request->input('cost');
        $product->sale_cost = $request->input('sale_cost');
        $product->content = $request->input('content');
        $product->save();
//        dd($product->s);
        $preview = $request->file('preview');
        if ($preview) {
            $preview_name = $preview->getClientOriginalName();
            $request->preview->move(public_path('storage/product'), $preview_name);
            $product->preview = 'storage/product/' . $preview_name;
            $product->save();
        }

        $images = $request->file('images');
        if ($images) {
            foreach ($images as $image) {
                $image_name = $image->getClientOriginalName();

                $image->move(public_path('storage/product'), $image_name);

                $productsPhoto = new ProductsPhoto();
                $productsPhoto->product_id = $product->id;
                $productsPhoto->image = 'storage/product/' . $image_name;
                $productsPhoto->save();
            }
        }
        $product->save();

        if ($product) {
            return redirect()->route('admin.products.edit', [
                'product' => $product,
            ])->with('success', 'Товар успешно создан');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param Products $products
     * @return Response
     */
    public function show(Products $products)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param $id
     * @return Application|Factory|View|Response
     */
    public function edit($id)
    {
        $product = $this->ProductRepository->getEdit($id);
        $productPhoto = ProductsPhoto::where('product_id', '=', $id)->get();

        return view('admin.product.edit', [
            'product' => $product,
            'productPhoto' => $productPhoto
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Products $products
     * @param $id
     * @return RedirectResponse
     */
    public function update(Request $request, Products $products, $id)
    {
        $product = $this->ProductRepository->getEdit($id);
        $product->status = $request->input('status');
        $product->s = $request->input('s');
        $product->m = $request->input('m');
        $product->l = $request->input('l');
        $product->xl = $request->input('xl');
        $product->xxl = $request->input('xxl');
        $product->h1 = $request->input('h1');
        $product->title = $request->input('title');
        $product->description = $request->input('description');
        $product->characteristics = $request->input('characteristics');
        $product->cost = $request->input('cost');
        $product->sale_cost = $request->input('sale_cost');
        $product->content = $request->input('content');
        $product->update();

        if ($request->file('preview')) {
            $preview = $request->file('preview');
            $filename = $preview->getClientOriginalName();
            $request->preview->move(public_path('storage/product'), $filename);

            if ($filename) {
                $product = Products::find($product->id);
                $product->preview = 'storage/product/' . $filename;
                $product->update();
            }
        }
        $images = $request->file('images');
        if ($images) {
            foreach ($images as $image) {
                $image_name = $image->getClientOriginalName();
                $image->move(public_path('storage/product'), $image_name);

                $productsPhoto = ProductsPhoto::where([
                    ['image', '=', 'storage/product/' . $image_name],
                    ['product_id', '=', $product->id],
                ])->first();

                if ($productsPhoto) {
                    $productsPhoto->image = 'storage/product/' . $image_name;
                    $productsPhoto->update();
                } else {
                    $productsPhoto = new ProductsPhoto();
                    $productsPhoto->image = 'storage/product/' . $image_name;
                    $productsPhoto->product_id = $product->id;
                    $productsPhoto->save();
                }
            }
        }


        if ($product) {
            return back()->with('success', 'Успешно сохранено');
        } else {
            return back()
                ->withInput()->with('danger', 'Ошибка сохранения');
        }
    }

    public function destroyImage(Request $request)
    {
        $product = $request->input('product_id');
//        dd($product);
        $productsPhoto = ProductsPhoto::destroy($product);

        if ($productsPhoto) {
            return back()->with('success', 'Фото успешно удалено');
        } else {
            return back()->with('danger', 'Произошла ошибка удаления');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Products $products
     * @param $id
     * @return RedirectResponse
     */
    public function destroy(Products $products, $id)
    {
        $product = Products::destroy($id);

        if ($product) {
            return back()->with('success', 'Статья успешно удалена');
        } else {
            return back()->with('danger', 'Произошла ошибка удаления');
        }
    }
}
