<?php

namespace App\Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Inventory\Domain\Models\ProductCategory;
use App\Modules\Inventory\Http\Requests\StoreCategoryRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', ProductCategory::class);

        $categories = ProductCategory::query()
            ->where('company_id', Auth::user()->company_id)
            ->orderBy('parent_id')
            ->orderBy('name')
            ->get();

        return view('inventory::categories.index', [
            'categories' => $categories,
        ]);
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $this->authorize('create', ProductCategory::class);

        $companyId = Auth::user()->company_id;
        $request->attributes->set('company_id', $companyId);

        ProductCategory::create([
            'company_id' => $companyId,
            'parent_id' => $request->input('parent_id'),
            'code' => $request->input('code'),
            'name' => $request->input('name'),
            'slug' => $this->resolveSlug($request->input('slug'), $request->input('name'), $request->input('code')),
            'status' => $request->input('status', 'active'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.inventory.categories.index')->with('status', 'Kategori oluşturuldu');
    }

    public function update(StoreCategoryRequest $request, ProductCategory $category): RedirectResponse
    {
        $this->authorize('update', $category);
        $request->attributes->set('company_id', Auth::user()->company_id);

        $category->fill([
            'parent_id' => $request->input('parent_id'),
            'code' => $request->input('code'),
            'name' => $request->input('name'),
            'slug' => $this->resolveSlug($request->input('slug'), $request->input('name'), $request->input('code')),
            'status' => $request->input('status', 'active'),
            'is_active' => $request->boolean('is_active', true),
        ])->save();

        return redirect()->route('admin.inventory.categories.index')->with('status', 'Kategori güncellendi');
    }

    public function destroy(ProductCategory $category): RedirectResponse
    {
        $this->authorize('delete', $category);

        if ($category->children()->exists()) {
            return back()->withErrors('Alt kategori bulunduğu için silinemiyor.');
        }

        $category->delete();

        return redirect()->route('admin.inventory.categories.index')->with('status', 'Kategori silindi');
    }

    protected function resolveSlug(?string $slug, string $name, string $code): string
    {
        $base = $slug ?: ($code ?: $name);

        return Str::slug($base) ?: Str::slug($code . '-' . $name);
    }
}
