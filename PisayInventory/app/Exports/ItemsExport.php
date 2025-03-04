<?php

namespace App\Exports;

use App\Models\Item;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ItemsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $fields;
    protected $itemsStatus;

    public function __construct($fields, $itemsStatus)
    {
        $this->fields = $fields;
        $this->itemsStatus = $itemsStatus;
    }

    public function collection()
    {
        $query = Item::with(['classification', 'unitOfMeasure']);

        switch ($this->itemsStatus) {
            case 'active':
                $query->where('IsDeleted', false);
                break;
            case 'deleted':
                $query->where('IsDeleted', true);
                break;
            // 'all' doesn't need additional conditions
        }

        return $query->get();
    }

    public function headings(): array
    {
        $headers = [];
        foreach ($this->fields as $field) {
            $headers[] = $this->getReadableColumnName($field);
        }
        return $headers;
    }

    public function map($item): array
    {
        $row = [];
        foreach ($this->fields as $field) {
            switch ($field) {
                case 'ItemName':
                    $row[] = $item->ItemName;
                    break;
                case 'Description':
                    $row[] = $item->Description;
                    break;
                case 'Classification':
                    $row[] = $item->classification ? $item->classification->ClassificationName : '';
                    break;
                case 'Unit':
                    $row[] = $item->unitOfMeasure ? $item->unitOfMeasure->UnitName : '';
                    break;
                case 'StocksAvailable':
                    $row[] = $item->StocksAvailable;
                    break;
                case 'ReorderPoint':
                    $row[] = $item->ReorderPoint;
                    break;
            }
        }
        return $row;
    }

    private function getReadableColumnName($field): string
    {
        $names = [
            'ItemName' => 'Item Name',
            'Description' => 'Description',
            'Classification' => 'Classification',
            'Unit' => 'Unit of Measure',
            'StocksAvailable' => 'Stocks Available',
            'ReorderPoint' => 'Reorder Point'
        ];

        return $names[$field] ?? $field;
    }
}