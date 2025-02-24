<?php

namespace App\Imports;

use App\Models\Item;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\Log;

class ItemsImport implements ToModel, WithHeadingRow, WithValidation
{
    private $columnMapping;
    private $defaultClassification;
    private $defaultUnit;
    private $defaultStocks;
    private $defaultReorderPoint;
    private $createdById;

    public function __construct($columnMapping, $defaultClassification, $defaultUnit, $defaultStocks, $defaultReorderPoint, $createdById)
    {
        $this->columnMapping = $columnMapping;
        $this->defaultClassification = $defaultClassification;
        $this->defaultUnit = $defaultUnit;
        $this->defaultStocks = $defaultStocks;
        $this->defaultReorderPoint = $defaultReorderPoint;
        $this->createdById = $createdById;
    }

    public function model(array $row)
{
    try {
        Log::info('Processing row:', ['row_data' => $row, 'column_mapping' => $this->columnMapping]);

        // Debug Description specifically
        $descriptionColumn = $this->columnMapping['Description'];
        $rawDescription = $row[$descriptionColumn] ?? null;
        $processedDescription = $this->getValue($row, 'Description', '');
        
        Log::info('Description debug:', [
            'mapped_column' => $descriptionColumn,
            'raw_value' => $rawDescription,
            'processed_value' => $processedDescription
        ]);

        // Convert names to IDs
        $classificationId = $this->getClassificationId($row);
        $unitId = $this->getUnitId($row);

        return new Item([
            'ItemName' => $row[$this->columnMapping['ItemName']] ?? null,
            'Description' => $rawDescription, // Try using raw value directly
            'ClassificationId' => $classificationId,
            'UnitOfMeasureId' => $unitId,
            'StocksAvailable' => (int)$this->getValue($row, 'StocksAvailable', $this->defaultStocks),
            'ReorderPoint' => (int) $this->getValue($row, 'ReorderPoint', $this->defaultReorderPoint)
                            ?: (int) ($row['reorder_point'] ?? $this->defaultReorderPoint),
            'CreatedById' => $this->createdById,
            'DateCreated' => now(),
            'IsDeleted' => false
        ]);

    } catch (\Exception $e) {
        Log::error('Row processing error:', [
            'error' => $e->getMessage(),
            'row' => $row,
            'mapping' => $this->columnMapping
        ]);
        throw $e;
    }
}

private function getClassificationId($row)
{
    $classificationName = trim($row[$this->columnMapping['ClassificationId']] ?? '');

    if (!$classificationName) {
        return $this->defaultClassification; // Use default if classification is not provided
    }

    // Find classification ID in the database
    $classification = \App\Models\Classification::where('ClassificationName', $classificationName)->first();

    if ($classification) {
        return $classification->ClassificationId;
    }

    // Optionally create classification if not found (Remove if you don’t want this)
    $newClassification = \App\Models\Classification::create([
        'ClassificationName' => $classificationName,
        'CreatedById' => $this->createdById,
        'DateCreated' => now(),
        'IsDeleted' => false
    ]);

    return $newClassification->ClassificationId;
}


private function getUnitId($row)
{
    $unitName = trim($row[$this->columnMapping['UnitId']] ?? '');

    if (!$unitName) {
        return $this->defaultUnit; // Use default if unit is not provided
    }

    // Find unit ID in the database
    $unit = \App\Models\Unit::where('UnitName', $unitName)->first();

    if ($unit) {
        return $unit->UnitOfMeasureId;
    }

    // Optionally create the unit if not found (Remove if you don’t want this)
    $newUnit = \App\Models\Unit::create([
        'UnitName' => $unitName,
        'CreatedById' => $this->createdById,
        'DateCreated' => now(),
        'IsDeleted' => false
    ]);

    return $newUnit->UnitOfMeasureId;
}




private function getValue($row, $field, $default = null)
{
    $mappedColumn = $this->columnMapping[$field] ?? null;

    if (!$mappedColumn) {
        return $default;
    }

    // Normalize column names (trim and lowercase)
    $normalizedRow = [];
    foreach ($row as $key => $value) {
        $normalizedRow[trim(strtolower($key))] = $value;
    }

    // Normalize mapped column name
    $normalizedMappedColumn = trim(strtolower($mappedColumn));

    // Ensure numeric values (for Stocks and ReorderPoint)
    if (isset($normalizedRow[$normalizedMappedColumn])) {
        return is_numeric($normalizedRow[$normalizedMappedColumn]) 
            ? (int) $normalizedRow[$normalizedMappedColumn] 
            : $default;
    }

    return $default;
}



public function rules(): array
{
    // The validation should use the mapped column names from the Excel file
    $mappedColumns = array_values($this->columnMapping);
    
    $rules = [];
    foreach ($mappedColumns as $excelColumn) {
        if ($excelColumn === $this->columnMapping['ItemName']) {
            $rules[$excelColumn] = 'required|string|max:255';
        } elseif ($excelColumn === $this->columnMapping['Description']) {
            $rules[$excelColumn] = 'nullable|string';
        } elseif ($excelColumn === $this->columnMapping['StocksAvailable']) {
            $rules[$excelColumn] = 'nullable|integer|min:0';
        } elseif ($excelColumn === $this->columnMapping['ReorderPoint']) {
            $rules[$excelColumn] = 'nullable|integer|min:0';
        }
    }
    
    return $rules;
}

public function customValidationMessages()
{
    // Use the mapped column name in the error message
    $itemNameColumn = $this->columnMapping['ItemName'] ?? '';
    
    return [
        $itemNameColumn.'.required' => 'The item name field is required.',
        '*.StocksAvailable.min' => 'The stocks available must be at least 0.',
        '*.ReorderPoint.min' => 'The reorder point must be at least 0.',
    ];
}
}