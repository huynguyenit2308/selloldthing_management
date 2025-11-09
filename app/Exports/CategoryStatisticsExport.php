<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class CategoryStatisticsExport implements FromCollection, WithHeadings, WithStyles, WithTitle, WithColumnWidths
{
    protected $categoryStats;
    protected $overviewStats;
    protected $dateRange;

    public function __construct($categoryStats, $overviewStats, $dateRange)
    {
        $this->categoryStats = $categoryStats;
        $this->overviewStats = $overviewStats;
        $this->dateRange = $dateRange;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $data = collect([
            // Header thông tin
            ['BÁO CÁO THỐNG KÊ DANH MỤC SẢN PHẨM', '', '', '', ''],
            ['Thời gian: ' . $this->dateRange['label'], '', '', '', ''],
            ['Ngày xuất: ' . now()->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i:s'), '', '', '', ''],
            ['', '', '', '', ''], // Empty row
            
            // Thống kê tổng quan
            ['THỐNG KÊ TỔNG QUAN', '', '', '', ''],
            ['Tổng doanh thu:', number_format($this->overviewStats['total_revenue'], 0, ',', '.') . ' ₫', '', '', ''],
            ['Số sản phẩm đã bán:', number_format($this->overviewStats['total_products_sold']), '', '', ''],
            ['Tăng trưởng:', number_format($this->overviewStats['growth_rate'], 1) . '%', '', '', ''],
            ['', '', '', '', ''], // Empty row
        ]);

        // Data rows
        $totalRevenue = $this->categoryStats->sum('revenue');
        
        foreach ($this->categoryStats as $category) {
            $percentage = $totalRevenue > 0 ? ($category->revenue / $totalRevenue) * 100 : 0;
            
            $data->push([
                $category->name,
                $category->total_products,
                $category->products_sold,
                number_format($category->revenue, 0, ',', '.') . ' ₫',
                number_format($percentage, 2) . '%'
            ]);
        }

        return $data;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Danh mục',
            'Số sản phẩm',
            'Sản phẩm đã bán',
            'Doanh thu',
            'Tỷ lệ %'
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Thống kê danh mục';
    }

    /**
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 30,
            'B' => 15,
            'C' => 20,
            'D' => 20,
            'E' => 15,
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        // Style cho header
        $sheet->getStyle('A1:E1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['rgb' => '2563eb']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);

        // Style cho thống kê tổng quan
        $sheet->getStyle('A5:E5')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'EEF2F8']
            ]
        ]);

        // Style cho table header (row 10)
        $sheet->getStyle('A10:E10')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2563eb']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ]);

        // Style cho data rows
        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle('A11:E' . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC']
                ]
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);

        // Center align cho các cột số
        $sheet->getStyle('B11:E' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        return [];
    }
}
