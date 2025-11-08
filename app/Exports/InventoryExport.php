<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class InventoryExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    /**
     * @var \Illuminate\Support\Collection<int, array>
     */
    private Collection $products;

    public function __construct(Collection $products)
    {
        $this->products = $products;
    }

    public function collection(): Collection
    {
        return $this->products;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Tên sản phẩm',
            'Giá (VND)',
            'Tồn kho',
            'Đã bán',
            'Trạng thái',
        ];
    }

    public function map($row): array
    {
        return [
            $row['id'],
            $row['name'],
            number_format($row['price'], 0, ',', '.'),
            $row['quantity'],
            $row['sold'],
            $row['inventory_status']['label'] ?? '',
        ];
    }

    public function title(): string
    {
        return 'Bao_cao_ton_kho';
    }
}
