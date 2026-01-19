<?php

use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;

new class extends Component {
    use WithPagination;

    #[Url]
    public ?string $search = '';

    #[Url]
    public ?array $brands = [];

    #[Url]
    public ?array $categories = [];

    public function clearCategoryFilter()
    {
        $this->categories = [];
        $this->resetPage();
    }

    public function clearBrandFilter()
    {
        $this->brands = [];
        $this->resetPage();
    }

    public function clearAllFilters()
    {
        $this->categories = [];
        $this->brands = [];
        $this->resetPage();
    }

    public function toggleCategory($id)
    {
        $id = (string) $id;
        if (in_array($id, $this->categories)) {
            $this->categories = array_values(array_diff($this->categories, [$id]));
        } else {
            $this->categories[] = $id;
        }
    }

    public function toggleBrand($id)
    {
        $id = (string) $id;
        if (in_array($id, $this->brands)) {
            $this->brands = array_values(array_diff($this->brands, [$id]));
        } else {
            $this->brands[] = $id;
        }
    }

    public function updated($property)
    {
        if (str($property)->startsWith(['search', 'brands', 'categories'])) {
            $this->resetPage();
        }
    }

    #[Computed]
    public function products()
    {
        return Product::with(['brand', 'category'])
            ->filter($this->search, $this->categories, $this->brands)
            ->paginate(6);
    }

    #[Computed(persist: true)]
    public function allBrands()
    {
        return Brand::orderBy('name')->get();
    }

    #[Computed(persist: true)]
    public function allCategories()
    {
        return Category::orderBy('name')->get();
    }
};
?>

<div class="py-4">
    <div class="container-fluid">
        <div class="mb-4">
            <h2 class="h3 fw-bold text-dark mb-3">
                Produtos
            </h2>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm mb-4 rounded-0">
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="search" class="form-label fw-semibold">Buscar Produtos</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input id="search" name="search" type="text"
                                    wire:model.live.debounce.250ms="search" class="form-control form-control-lg"
                                    placeholder="Buscar por nome...">
                                @if ($search)
                                    <button class="btn btn-outline-secondary" wire:click="$set('search', '')"
                                        type="button">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm rounded-0">
                    <div class="card-body bg-light">
                        <div class="d-flex flex-column">
                            <div class="mb-3 d-flex justify-content-between">
                                <h5><i class="bi bi-funnel me-1"></i>Filtros</h5>

                                @if (count($this->categories) > 0 || count($this->brands) > 0)
                                    <button wire:click="clearAllFilters" type="button"
                                        class="btn p-0 btn-link link-secondary"
                                        style="--bs-btn-padding-y: .25rem; --bs-btn-padding-x: .5rem; --bs-btn-font-size: .75rem;">
                                        Limpar filtros
                                    </button>
                                @endif
                            </div>

                            <div class="card rounded-0 p-0 mb-3">
                                <div class="card-header bg-white">
                                    <div class="d-flex justify-content-between align-items-end">
                                        <div>
                                            <span class="form-label fw-semibold">Categorias</span>
                                        </div>
                                        @if (count($this->categories) > 1)
                                            <span
                                                class="badge rounded-pill bg-dark-subtle text-black">{{ count($this->categories) }}</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="card-body p-0 overflow-y-auto ps-1" style="max-height: 155px;">
                                    @foreach ($this->allCategories as $category)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="{{ $category->id }}"
                                                id="category-{{ $category->id }}" wire:model.live="categories"
                                                wire:key="category-{{ $category->id }}" wire:loading.attr="disabled">
                                            <label class="form-check-label" for="category-{{ $category->id }}">
                                                {{ $category->name }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>

                                @if (count($this->categories) > 0)
                                    <div class="card-footer bg-white pt-0">
                                        <div class="mt-2">
                                            <p class="small text-muted mb-0">
                                                {{ count($this->categories) }} categoria(s) selecionadas.
                                            </p>
                                            <button wire:click="clearCategoryFilter" type="button"
                                                class="btn p-0 btn-link link-danger"
                                                style="--bs-btn-padding-y: .25rem; --bs-btn-padding-x: .5rem; --bs-btn-font-size: .75rem;">
                                                Limpar
                                            </button>
                                        </div>
                                    </div>
                                @endif

                            </div>

                            <div class="card rounded-0 p-0 mb-3">
                                <div class="card-header bg-white">
                                    <div class="d-flex justify-content-between align-items-end">
                                        <div>
                                            <span class="form-label fw-semibold">Marcas</span>
                                        </div>
                                        @if (count($this->brands) > 1)
                                            <span
                                                class="badge rounded-pill bg-dark-subtle text-black">{{ count($this->brands) }}</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="card-body p-0 overflow-y-auto ps-1" style="max-height: 155px;">
                                    @foreach ($this->allBrands as $brand)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="{{ $brand->id }}"
                                                id="brand-{{ $brand->id }}" wire:model.live="brands"
                                                wire:key="brand-{{ $brand->id }}" wire:loading.attr="disabled">
                                            <label class="form-check-label" for="brand-{{ $brand->id }}">
                                                {{ $brand->name }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>

                                @if (count($this->brands) > 0)
                                    <div class="card-footer bg-white pt-0">
                                        <div class="mt-2">
                                            <p class="small text-muted mb-0">
                                                {{ count($this->brands) }} marca(s) selecionadas.
                                            </p>
                                            <button wire:click="clearBrandFilter" type="button"
                                                class="btn p-0 btn-link link-danger"
                                                style="--bs-btn-padding-y: .25rem; --bs-btn-padding-x: .5rem; --bs-btn-font-size: .75rem;">
                                                Limpar
                                            </button>
                                        </div>
                                    </div>
                                @endif

                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-9">

                <div class="card shadow-sm rounded-0">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0 text-dark">
                            <i class="bi bi-list-ul me-2"></i>Resultados ({{ $this->products->total() }} produtos)
                        </h5>
                    </div>
                    <div class="card-body">

                        <div class="row">
                            <div wire:loading>
                                <div class="col-12 text-center pt-3">
                                    <div class="spinner-border text-primary mb-3" role="status">
                                        <span class="visually-hidden">Carregando...</span>
                                    </div>
                                    <p class="text-muted fw-semibold">
                                        Carregando produtos...
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div wire:loading.remove>
                            @if ($this->products->count() > 0)
                                <div class="row g-4">
                                    @foreach ($this->products as $product)
                                        <div class="col-md-12 col-lg-6 col-xl-4" wire:key="{{ $product->id }}">
                                            <div class="card h-100 shadow-sm">

                                                <div class="card-body d-flex flex-column">
                                                    <h6 class="card-title fw-bold mb-2 text-dark">
                                                        {{ Str::limit($product->name, 50) }}
                                                    </h6>

                                                    @if ($product->description)
                                                        <p class="card-text text-muted small flex-grow-1 mb-3">
                                                            {{ Str::limit($product->description, 150) }}
                                                        </p>
                                                    @endif

                                                    <div class="mb-4">
                                                        <p class="mb-0">
                                                            <span class="fw-bold">Categoria:</span>
                                                            @if ($product->category)
                                                                <button
                                                                    wire:click="toggleCategory({{ $product->category->id }})"
                                                                    type="button"
                                                                    class="badge bg-primary-subtle text-dark border-0 cursor-pointer"
                                                                    style="cursor: pointer; transition: all 0.2s;"
                                                                    title="Clique para filtrar por esta categoria">
                                                                    {{ Str::limit($product->category->name, 21) }}
                                                                </button>
                                                            @else
                                                                <span class="badge bg-light text-dark">Sem
                                                                    categoria</span>
                                                            @endif
                                                        </p>
                                                        <p class="mb-0">
                                                            <span class="fw-bold">Marca:</span>
                                                            @if ($product->brand)
                                                                <button
                                                                    wire:click="toggleBrand({{ $product->brand->id }})"
                                                                    type="button"
                                                                    class="badge bg-dark-subtle text-dark border-0 cursor-pointer"
                                                                    style="cursor: pointer; transition: all 0.2s;"
                                                                    title="Clique para filtrar por esta marca">
                                                                    {{ $product->brand->name }}
                                                                </button>
                                                            @else
                                                                <span class="badge bg-light text-dark">Sem marca</span>
                                                            @endif
                                                        </p>
                                                    </div>

                                                    <div class="d-grid">
                                                        <button class="btn btn-outline-success rounded-0 fw-bold">
                                                            R$ {{ number_format($product->price, 2, ',', '.') }}
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i class="bi bi-inbox fs-1 text-muted d-block mb-3"></i>
                                    <p class="text-muted mb-0">Nenhum produto encontrado com os filtros selecionados.
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>

                    @if ($this->products->hasPages())
                        <div class="card-footer bg-light border-top pt-3 pb-0">
                            {{ $this->products->links() }}
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>
</div>
