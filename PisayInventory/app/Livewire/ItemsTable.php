<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Item;

class ItemsTable extends Component
{
    use WithPagination;

    public $search = '';
    
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.items-table', [
            'items' => Item::with([
                'classification',
                'unitOfMeasure',
                'supplier',
                'created_by_user',
                'modified_by_user'
            ])
            ->where('IsDeleted', 0)
            ->where(function($query) {
                $query->where('ItemName', 'like', '%' . $this->search . '%')
                    ->orWhere('Description', 'like', '%' . $this->search . '%')
                    ->orWhereHas('classification', function($q) {
                        $q->where('ClassificationName', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('supplier', function($q) {
                        $q->where('SupplierName', 'like', '%' . $this->search . '%');
                    });
            })
            ->orderBy('ItemName')
            ->paginate(10),
            'trashedItems' => Item::with([
                'classification',
                'unitOfMeasure',
                'supplier',
                'deleted_by_user'
            ])
            ->where('IsDeleted', 1)
            ->orderBy('ItemName')
            ->paginate(10)
        ]);
    }
}
