<?php

namespace App\Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Inventory\Domain\Models\ProductCategory;
use App\Modules\Inventory\Http\Requests\StoreCategoryRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(ProductCategory::class, 'category');
    }

    public function index(Request $request): View
    {
        $companyId = $this->companyId($request);
        $term = $request->query('q');

        $categories = ProductCategory::query()
            ->where('company_id', $companyId)
            ->with('parent')
            ->search($term)
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('inventory::categories.index', [
            'categories' => $categories,
            'filters' => ['q' => $term],
        ]);
    }

    public function create(Request $request): View
    {
        $companyId = $this->companyId($request);

        return view('inventory::categories.create', [
            'category' => new ProductCategory(['status' => 'active']),
            'parents' => $this->parentOptions($companyId),
        ]);
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $companyId = $this->companyIdOrFail($request);
        $data = $request->validated();
        $data['company_id'] = $companyId;
        $data['parent_id'] = $this->ensureParentBelongsToCompany($data['parent_id'] ?? null, $companyId);

        ProductCategory::create($data);

        return redirect()->route('admin.inventory.categories.index')->with('status', 'Kategori eklendi.');
    }

    public function edit(Request $request, ProductCategory $category): View
    {
        $companyId = $this->companyId($request);

        return view('inventory::categories.edit', [
            'category' => $category,
            'parents' => $this->parentOptions($companyId, $category->id),
        ]);
    }

    public function update(StoreCategoryRequest $request, ProductCategory $category): RedirectResponse
    {
        $companyId = $this->companyIdOrFail($request);
        $data = $request->validated();
        $data['parent_id'] = $this->ensureParentBelongsToCompany($data['parent_id'] ?? null, $companyId, $category->id);

        $category->update($data);

        return redirect()->route('admin.inventory.categories.index')->with('status', 'Kategori güncellendi.');
    }

    public function destroy(ProductCategory $category): RedirectResponse
    {
        $category->delete();

        return redirect()->route('admin.inventory.categories.index')->with('status', 'Kategori silindi.');
    }

    protected function parentOptions(int $companyId, ?int $exceptId = null)
    {
        return ProductCategory::query()
            ->where('company_id', $companyId)
            ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
            ->orderBy('name')
            ->get();
    }

    protected function ensureParentBelongsToCompany(?int $parentId, int $companyId, ?int $currentId = null): ?int
    {
        if (! $parentId) {
            return null;
        }

        if ($parentId === $currentId) {
            throw ValidationException::withMessages([
                'parent_id' => 'Bir kategori kendisini ebeveyn olarak seçemez.',
            ]);
        }

        $exists = ProductCategory::query()
            ->where('company_id', $companyId)
            ->where('id', $parentId)
            ->exists();

        if (! $exists) {
            throw ValidationException::withMessages([
                'parent_id' => 'Seçilen üst kategori bu şirkete ait değil.',
            ]);
        }

        return $parentId;
    }

    protected function companyId(Request $request): ?int
    {
        return $request->attributes->get('company_id') ?? (app()->bound('company') ? app('company')->id : null);
    }

    protected function companyIdOrFail(Request $request): int
    {
        $companyId = $this->companyId($request);

        if (! $companyId) {
            throw ValidationException::withMessages([
                'company_id' => 'Şirket bağlamı bulunamadı.',
            ]);
        }

        return $companyId;
    }
}
