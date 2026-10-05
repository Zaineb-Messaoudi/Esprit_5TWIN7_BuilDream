<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryStoreRequest;
use App\Http\Requests\Admin\CategoryUpdateRequest;
use App\Models\Category;
use App\Services\EquipmentCatalogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/** Back Office CRUD for catalogue categories. */
class CategoryController extends Controller
{
    public function __construct(private readonly EquipmentCatalogService $catalog) {}

    public function index(): View
    {
        return view('pages.admin.categories.index', [
            'categories' => Category::withCount('equipment')->latest()->paginate(10),
            'title' => __('Categories'),
        ]);
    }

    public function create(): View
    {
        return view('pages.admin.categories.create', ['title' => __('Create category')]);
    }

    public function store(CategoryStoreRequest $request): RedirectResponse
    {
        $this->catalog->createCategory($request->validated());
        return redirect()->route('admin.categories.index')->with('status', 'category-created');
    }

    public function edit(Category $category): View
    {
        return view('pages.admin.categories.edit', compact('category') + ['title' => __('Edit category')]);
    }

    public function update(CategoryUpdateRequest $request, Category $category): RedirectResponse
    {
        $this->catalog->updateCategory($category, $request->validated());
        return redirect()->route('admin.categories.index')->with('status', 'category-updated');
    }

    public function destroy(Category $category): RedirectResponse
    {
        try {
            $this->catalog->deleteCategory($category);
        } catch (\DomainException $exception) {
            return back()->withErrors(['category' => $exception->getMessage()]);
        }

        return redirect()->route('admin.categories.index')->with('status', 'category-deleted');
    }
}
