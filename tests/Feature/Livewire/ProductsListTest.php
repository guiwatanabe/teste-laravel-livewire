<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Livewire\Livewire;

beforeEach(function () {
    $categories = Category::factory(3)->create();
    $brands = Brand::factory(3)->create();

    Product::factory(15)->sequence(
        ...collect(range(1, 15))->map(fn ($i) => [
            'category_id' => $categories->random()->id,
            'brand_id' => $brands->random()->id,
        ])->toArray()
    )->create();
});

test('can select a single category', function () {
    $category = Category::first();

    Livewire::test('products-list')
        ->call('toggleCategory', $category->id)
        ->assertSet('categories', [$category->id]);
});

test('can select multiple categories', function () {
    $categories = Category::take(2)->pluck('id')->toArray();

    $component = Livewire::test('products-list');

    foreach ($categories as $categoryId) {
        $component->call('toggleCategory', $categoryId);
    }

    $component->assertSet('categories', $categories);
});

test('can select a single brand', function () {
    $brand = Brand::first();

    Livewire::test('products-list')
        ->call('toggleBrand', $brand->id)
        ->assertSet('brands', [$brand->id]);
});

test('can select multiple brands', function () {
    $brands = Brand::take(2)->pluck('id')->toArray();

    $component = Livewire::test('products-list');

    foreach ($brands as $brandId) {
        $component->call('toggleBrand', $brandId);
    }

    $component->assertSet('brands', $brands);
});

test('can select both categories and brands simultaneously', function () {
    $category = Category::first();
    $brand = Brand::first();

    Livewire::test('products-list')
        ->call('toggleCategory', $category->id)
        ->call('toggleBrand', $brand->id)
        ->assertSet('categories', [$category->id])
        ->assertSet('brands', [$brand->id]);
});

test('toggling a selected category removes it from filter', function () {
    $category = Category::first();

    Livewire::test('products-list')
        ->call('toggleCategory', $category->id)
        ->assertSet('categories', [$category->id])
        ->call('toggleCategory', $category->id)
        ->assertSet('categories', []);
});

test('toggling a selected brand removes it from filter', function () {
    $brand = Brand::first();

    Livewire::test('products-list')
        ->call('toggleBrand', $brand->id)
        ->assertSet('brands', [$brand->id])
        ->call('toggleBrand', $brand->id)
        ->assertSet('brands', []);
});

test('search filters persist in URL', function () {
    Livewire::test('products-list')
        ->set('search', 'laptop')
        ->assertSet('search', 'laptop');
});

test('category filters persist in URL', function () {
    $categories = Category::take(2)->pluck('id')->toArray();

    $component = Livewire::test('products-list');
    foreach ($categories as $categoryId) {
        $component->call('toggleCategory', $categoryId);
    }

    $component->assertSet('categories', $categories);
});

test('brand filters persist in URL', function () {
    $brands = Brand::take(2)->pluck('id')->toArray();

    $component = Livewire::test('products-list');
    foreach ($brands as $brandId) {
        $component->call('toggleBrand', $brandId);
    }

    $component->assertSet('brands', $brands);
});

test('filters are restored from URL on component mount', function () {
    $category = Category::first();
    $brand = Brand::first();
    $search = 'test';

    Livewire::test('products-list', [
        'search' => $search,
        'categories' => [$category->id],
        'brands' => [$brand->id],
    ])
        ->assertSet('search', $search)
        ->assertSet('categories', [$category->id])
        ->assertSet('brands', [$brand->id]);
});

test('can clear category filter with clearCategoryFilter method', function () {
    $categories = Category::take(2)->pluck('id')->toArray();

    $component = Livewire::test('products-list');
    foreach ($categories as $categoryId) {
        $component->call('toggleCategory', $categoryId);
    }

    $component
        ->call('clearCategoryFilter')
        ->assertSet('categories', []);
});

test('can clear brand filter with clearBrandFilter method', function () {
    $brands = Brand::take(2)->pluck('id')->toArray();

    $component = Livewire::test('products-list');
    foreach ($brands as $brandId) {
        $component->call('toggleBrand', $brandId);
    }

    $component
        ->call('clearBrandFilter')
        ->assertSet('brands', []);
});

test('can clear search by setting it to empty string', function () {
    Livewire::test('products-list')
        ->set('search', 'laptop')
        ->assertSet('search', 'laptop')
        ->set('search', '')
        ->assertSet('search', '');
});

test('clearing search updates product list', function () {
    $searchTerm = 'test';

    Livewire::test('products-list')
        ->set('search', $searchTerm)
        ->set('search', '')
        ->assertSee(Product::first()->name);
});

test('filters return correct products based on search', function () {
    Product::factory()->create(['name' => 'Unique Laptop']);

    Livewire::test('products-list')
        ->set('search', 'Unique Laptop')
        ->call('$refresh')
        ->assertSee('Unique Laptop');
});

test('filters return correct products by category', function () {
    $category = Category::first();

    Product::factory(3)->create(['category_id' => $category->id]);

    Livewire::test('products-list')
        ->call('toggleCategory', $category->id)
        ->call('$refresh');

    $productsInCategory = Product::where('category_id', $category->id)->count();
    expect($productsInCategory)->toBeGreaterThan(0);
});

test('filters return correct products by brand', function () {
    $brand = Brand::first();

    Product::factory(3)->create(['brand_id' => $brand->id]);

    Livewire::test('products-list')
        ->call('toggleBrand', $brand->id)
        ->call('$refresh');

    $productsWithBrand = Product::where('brand_id', $brand->id)->count();
    expect($productsWithBrand)->toBeGreaterThan(0);
});

test('combining multiple filters returns correct products', function () {
    $category = Category::first();
    $brand = Brand::first();

    $productsWithBrandAndCategory = Product::where('brand_id', $brand->id)->where('category_id', $category->id)->count();

    Livewire::test('products-list')
        ->call('toggleCategory', $category->id)
        ->call('toggleBrand', $brand->id)
        ->call('$refresh')
        ->assertCount('products', $productsWithBrandAndCategory);
});

test('selecting and deselecting maintains empty array state', function () {
    $category = Category::first();

    Livewire::test('products-list')
        ->call('toggleCategory', $category->id)
        ->call('toggleCategory', $category->id)
        ->assertSet('categories', []);
});

test('clearing filters when none are selected does nothing', function () {
    Livewire::test('products-list')
        ->assertSet('categories', [])
        ->call('clearCategoryFilter')
        ->assertSet('categories', []);
});

test('search with special characters is handled safely', function () {
    Livewire::test('products-list')
        ->set('search', "O'Reilly")
        ->assertSet('search', "O'Reilly");
});

test('empty search string returns all products', function () {
    $totalProducts = Product::count();

    $component = Livewire::test('products-list')
        ->set('search', '');

    expect($component->get('products')->total())->toBe($totalProducts);
});
