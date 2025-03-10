<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $orderColumn = request('order_column', 'created_at');
        if (!in_array($orderColumn, ['id', 'name', 'created_at'])) {
            $orderColumn = 'created_at';
        }
        $orderDirection = request('order_direction', 'desc');
        if (!in_array($orderDirection, ['asc', 'desc'])) {
            $orderDirection = 'desc';
        }

        $categories = Category::withCount('posts') // Relación con el modelo Post
            ->when(request('search_id'), function ($query) {
                $query->where('id', request('search_id'));
            })
            ->when(request('search_title'), function ($query) {
                $query->where('name', 'like', '%' . request('search_title') . '%');
            })
            ->when(request('search_global'), function ($query) {
                $query->where(function ($q) {
                    $q->where('id', request('search_global'))
                        ->orWhere('name', 'like', '%' . request('search_global') . '%');
                });
            })
            ->orderBy($orderColumn, $orderDirection)
            ->paginate(50);

        return CategoryResource::collection($categories);
    }

    public function store(StoreCategoryRequest $request)
    {
        $this->authorize('category-create');
        $category = Category::create($request->validated());

        return new CategoryResource($category);
    }

    public function show(Category $category)
    {
        $this->authorize('category-edit');

        // Incluye las publicaciones asociadas en la respuesta
        return new CategoryResource($category->load('posts'));
    }

    public function update(StoreCategoryRequest $request, Category $category)
    {
        $this->authorize('category-edit');
        $category->update($request->validated());

        return new CategoryResource($category);
    }

    public function destroy(Category $category)
    {
        $this->authorize('category-delete');

        if ($category->posts()->count() > 0) {
            return response()->json(['error' => 'No puedes eliminar una categoría que tiene publicaciones asociadas.'], 400);
        }

        $category->delete();

        return response()->noContent();
    }

    public function getList()
    {
        // Lista básica de categorías sin paginación
        return CategoryResource::collection(Category::with('posts')->get());
    }
}
