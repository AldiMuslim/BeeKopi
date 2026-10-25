<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LaporanLabaRugiExport implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function collection(): Collection
    {
        $rows = [
            ['Keterangan', 'Nominal (Rp)'],
            ['Total Omzet Penjualan (Kotor)', $this->data['totalOmzet']],
            ['- Omzet Tunai', $this->data['omzetTunai']],
            ['- Omzet QRIS', $this->data['omzetQris']],
            ['Belanja Bahan Baku (HPP)', $this->data['totalBiayaBahan']],
            ['Operasional Lapak (Approved)', $this->data['totalBiayaOperasional']],
            ['- Uang Makan Staff', $this->data['totalUangMakan']],
            ['- Pengeluaran Operasional Lain', $this->data['totalPengeluaranLain']],
            ['TOTAL ESTIMASI LABA BERSIH', $this->data['labaBersih']],
            ['', ''],
            ['KONTRIBUSI PENJUALAN PER LAPAK', ''],
            ['Nama Lapak Cabang', 'Total Omzet (Rp)'],
        ];

        foreach ($this->data['performaLapaks'] as $lapak) {
            $rows[] = [$lapak->nama_lapak, $lapak->total_omzet ?? 0];
        }

        return new Collection($rows);
    }

    public function headings(): array
    {
        return [
            ['BEE KOPI - LAPORAN LABA & RUGI'],
            ['Periode: ' . $this->data['tanggal_mulai'] . ' s/d ' . $this->data['tanggal_selesai']],
            [''],
        ];
    }

    public function title(): string
    {
        return 'Laba Rugi';
    }

    public function styles(Worksheet $sheet): ?array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            2 => ['font' => ['italic' => true]],
            4 => ['font' => ['bold' => true]],
        ];
    }
}
