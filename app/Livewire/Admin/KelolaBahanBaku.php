<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use App\Models\BahanBaku;
use App\Models\Produk;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class KelolaBahanBaku extends Component
{
    // Filter & Search State
    public $search = '';
    public $filterStatus = 'semua'; // 'semua', 'menipis', 'habis', 'aman'
    public $filterSatuan = '';

    // Modal Visibility State
    public $showModalBahan = false;
    public $showKalkulatorKemasan = false;

    // Form Bahan Baku State
    public $bahan_baku_id = null;
    public $nama_bahan = '';
    public $stok = 0;
    public $satuan = 'gram';
    public $stok_minimum = 0;
    public $harga_satuan = 0;
    public $alasan_koreksi = '';

    // Kalkulator Bantu Harga Kemasan State
    public $harga_beli_kemasan = '';
    public $isi_per_kemasan = '';

    /**
     * Ambil nilai stok bahan baku (mendukung kolom stok_sekarang maupun stok)
     */
    public function getStok($bahan): float
    {
        if (Schema::hasColumn('bahan_bakus', 'stok_sekarang') && isset($bahan->stok_sekarang)) {
            return (float) $bahan->stok_sekarang;
        }
        return (float) ($bahan->stok ?? $bahan->stok_sekarang ?? 0);
    }

    /**
     * Ambil nilai harga satuan bahan baku (mendukung harga_satuan, harga_beli, atau harga)
     */
    public function getHargaSatuan($bahan): float
    {
        return (float) ($bahan->harga_satuan ?? $bahan->harga_beli ?? $bahan->harga ?? 0);
    }

    /**
     * Konversi nilai antar satuan (kg <-> gram, liter <-> ml, pcs/set)
     */
    public function konversiNilaiStok($jumlah, $fromSatuan, $toSatuan): float
    {
        $from = strtolower(trim($fromSatuan ?? ''));
        $to   = strtolower(trim($toSatuan ?? ''));

        if ($from === $to || empty($from) || empty($to)) {
            return (float) $jumlah;
        }

        if ($from === 'kg' && in_array($to, ['gram', 'gr', 'g'])) {
            return (float) $jumlah * 1000;
        }
        if (in_array($from, ['gram', 'gr', 'g']) && $to === 'kg') {
            return (float) $jumlah / 1000;
        }

        if (in_array($from, ['liter', 'l']) && $to === 'ml') {
            return (float) $jumlah * 1000;
        }
        if (in_array($from, ['ml']) && in_array($to, ['liter', 'l'])) {
            return (float) $jumlah / 1000;
        }

        return (float) $jumlah;
    }

    /**
     * Hitung estimasi maksimal cup yang bisa dibuat dari stok bahan baku saat ini untuk 1 item resep
     */
    public function estimasiKapasitasCup($bahan, $resep): int
    {
        $stokTersedia = $this->getStok($bahan);
        if ($stokTersedia <= 0) {
            return 0;
        }

        $takaran = (float) ($resep->takaran_per_cup ?? $resep->jumlah_takaran ?? 0);
        if ($takaran <= 0) {
            return 0;
        }

        $satuanResep = $resep->satuan ?? $bahan->satuan;
        $kebutuhanPerCupDalamSatuanGudang = $this->konversiNilaiStok($takaran, $satuanResep, $bahan->satuan);

        if ($kebutuhanPerCupDalamSatuanGudang <= 0) {
            return 0;
        }

        return (int) floor($stokTersedia / $kebutuhanPerCupDalamSatuanGudang);
    }

    /**
     * Kalkulator bantu konversi harga beli kemasan besar ke harga per satuan terkecil
     */
    public function hitungHargaSatuanDariKemasan()
    {
        $hargaKemasan = (float) $this->harga_beli_kemasan;
        $isiKemasan   = (float) $this->isi_per_kemasan;

        if ($hargaKemasan > 0 && $isiKemasan > 0) {
            $this->harga_satuan = round($hargaKemasan / $isiKemasan, 2);
        }
    }

    public function updatedHargaBeliKemasan()
    {
        $this->hitungHargaSatuanDariKemasan();
    }

    public function updatedIsiPerKemasan()
    {
        $this->hitungHargaSatuanDariKemasan();
    }

    /**
     * Simpan log aktivitas ke tabel audit_logs secara aman sesuai struktur kolom
     */
    private function catatLog($aktivitas, $keterangan = null)
    {
        $data = [
            'user_id'   => Auth::id(),
            'aktivitas' => $keterangan && !Schema::hasColumn('audit_logs', 'keterangan')
                ? "{$aktivitas}: {$keterangan}"
                : $aktivitas,
        ];

        if ($keterangan && Schema::hasColumn('audit_logs', 'keterangan')) {
            $data['keterangan'] = $keterangan;
        }

        AuditLog::create($data);
    }

    /**
     * Sinkronisasi ulang nilai HPP pada semua produk yang menggunakan bahan baku ini
     */
    private function sinkronisasiHppProdukTerkait($bahanBakuId)
    {
        if (!Schema::hasColumn('produks', 'hpp')) {
            return;
        }

        $produks = Produk::with('reseps.bahanBaku')
            ->whereHas('reseps', fn($q) => $q->where('bahan_baku_id', $bahanBakuId))
            ->get();

        foreach ($produks as $produk) {
            $totalHpp = 0;
            foreach ($produk->reseps as $rsp) {
                $bahan = $rsp->bahanBaku;
                if (!$bahan) continue;

                $hargaBahan  = $this->getHargaSatuan($bahan);
                $takaran     = (float) ($rsp->takaran_per_cup ?? $rsp->jumlah_takaran ?? 0);
                $satuanResep = $rsp->satuan ?? $bahan->satuan;

                if ($hargaBahan > 0 && $takaran > 0) {
                    $takaranGudang = $this->konversiNilaiStok($takaran, $satuanResep, $bahan->satuan);
                    $totalHpp += ($takaranGudang * $hargaBahan);
                }
            }

            $produk->update(['hpp' => round($totalHpp)]);
        }
    }

    public function openModalBahan()
    {
        $this->resetInput();
        $this->resetValidation();
        $this->showModalBahan = true;
    }

    public function editBahan($id)
    {
        $this->resetValidation();
        $this->reset(['harga_beli_kemasan', 'isi_per_kemasan', 'alasan_koreksi']);
        $this->showKalkulatorKemasan = false;

        $bahan = BahanBaku::findOrFail($id);
        $this->bahan_baku_id = $bahan->id;
        $this->nama_bahan    = $bahan->nama_bahan;
        $this->stok          = $this->getStok($bahan);
        $this->satuan        = $bahan->satuan ?? 'gram';
        $this->stok_minimum  = (float) ($bahan->stok_minimum ?? 0);
        $this->harga_satuan  = $this->getHargaSatuan($bahan);

        $this->showModalBahan = true;
    }

    public function simpanBahan()
    {
        $this->validate([
            'nama_bahan'   => 'required|string|max:255|unique:bahan_bakus,nama_bahan,' . $this->bahan_baku_id,
            'stok'         => 'required|numeric|min:0',
            'satuan'       => 'required|string',
            'stok_minimum' => 'required|numeric|min:0',
            'harga_satuan' => 'nullable|numeric|min:0',
        ]);

        $isEdit = (bool) $this->bahan_baku_id;
        $stokLama = 0;

        if ($isEdit) {
            $bahanLama = BahanBaku::find($this->bahan_baku_id);
            $stokLama = $bahanLama ? $this->getStok($bahanLama) : 0;
        }

        $data = [
            'nama_bahan'   => $this->nama_bahan,
            'satuan'       => $this->satuan,
            'stok_minimum' => $this->stok_minimum,
        ];

        // Dukungan skema kolom stok_sekarang maupun stok
        if (Schema::hasColumn('bahan_bakus', 'stok_sekarang')) {
            $data['stok_sekarang'] = $this->stok;
        }
        if (Schema::hasColumn('bahan_bakus', 'stok')) {
            $data['stok'] = $this->stok;
        }

        // Dukungan skema kolom harga_satuan, harga_beli, atau harga
        if (Schema::hasColumn('bahan_bakus', 'harga_satuan')) {
            $data['harga_satuan'] = $this->harga_satuan ?: 0;
        } elseif (Schema::hasColumn('bahan_bakus', 'harga_beli')) {
            $data['harga_beli'] = $this->harga_satuan ?: 0;
        } elseif (Schema::hasColumn('bahan_bakus', 'harga')) {
            $data['harga'] = $this->harga_satuan ?: 0;
        }

        DB::transaction(function () use ($isEdit, $stokLama, $data) {
            $bahan = BahanBaku::updateOrCreate(['id' => $this->bahan_baku_id], $data);

            // Sinkronisasi HPP semua produk yang memakai bahan baku ini
            $this->sinkronisasiHppProdukTerkait($bahan->id);

            // Catat jejak spesifik jika terjadi perubahan/koreksi stok manual oleh Admin
            if ($isEdit && (float) $stokLama !== (float) $this->stok) {
                $selisih = (float) $this->stok - (float) $stokLama;
                $tanda   = $selisih > 0 ? '+' : '';
                $alasan  = $this->alasan_koreksi ? " (Alasan: {$this->alasan_koreksi})" : ' (Koreksi Admin)';

                $this->catatLog(
                    'Koreksi Stok Bahan Baku',
                    "Koreksi stok {$bahan->nama_bahan} dari {$stokLama} {$bahan->satuan} menjadi {$this->stok} {$bahan->satuan} ({$tanda}{$selisih} {$bahan->satuan}){$alasan}"
                );
            } else {
                $this->catatLog(
                    $isEdit ? 'Update Master Bahan Baku' : 'Tambah Bahan Baku Baru',
                    ($isEdit ? 'Memperbarui' : 'Menambahkan') . " master bahan baku {$bahan->nama_bahan} (Stok: {$this->stok} {$bahan->satuan})"
                );
            }
        });

        session()->flash('message', $isEdit ? 'Data master bahan baku & HPP resep terkait berhasil diperbarui!' : 'Bahan baku baru berhasil ditambahkan!');
        $this->showModalBahan = false;
        $this->resetInput();
    }

    public function hapusBahan($id)
    {
        $bahan = BahanBaku::withCount('reseps')->findOrFail($id);

        if ($bahan->reseps_count > 0) {
            session()->flash('error', "Gagal menghapus! Bahan '{$bahan->nama_bahan}' masih digunakan aktif pada {$bahan->reseps_count} resep menu produk.");
            return;
        }

        $nama = $bahan->nama_bahan;
        $bahan->delete();

        $this->catatLog('Hapus Master Bahan Baku', "Menghapus bahan baku {$nama} dari daftar master");
        session()->flash('message', "Bahan baku '{$nama}' berhasil dihapus.");
    }

    /**
     * Ekspor Daftar Belanja Bahan Baku Menipis / Habis ke CSV
     */
    public function exportDaftarBelanjaCsv()
    {
        $semuaBahan = BahanBaku::withCount('reseps')->orderBy('nama_bahan', 'asc')->get();

        // Ambil bahan yang stoknya kurang dari atau sama dengan stok minimum
        $bahanRestock = $semuaBahan->filter(function ($b) {
            return $this->getStok($b) <= (float) $b->stok_minimum;
        });

        // Jika semua aman, ekspor seluruh daftar master sebagai laporan inventori
        $dataEkspor = $bahanRestock->isNotEmpty() ? $bahanRestock : $semuaBahan;
        $filename = 'daftar_belanja_bahan_beekopi_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($dataEkspor) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, [
                'Nama Bahan Baku',
                'Stok Saat Ini',
                'Batas Minimum',
                'Satuan',
                'Defisit / Rekomendasi Beli',
                'Harga Satuan Terakhir (Rp)',
                'Estimasi Biaya Restock (Rp)',
                'Status Stok',
            ]);

            foreach ($dataEkspor as $b) {
                $stok     = $this->getStok($b);
                $min      = (float) $b->stok_minimum;
                $harga    = $this->getHargaSatuan($b);
                $rekomendasiBeli = $stok <= $min ? max(($min * 2) - $stok, 1) : 0;
                $estimasiBiaya   = round($rekomendasiBeli * $harga);
                $status          = $stok <= 0 ? 'HABIS' : ($stok <= $min ? 'MENIPIS / RESTOCK' : 'AMAN');

                fputcsv($handle, [
                    $b->nama_bahan,
                    $stok,
                    $min,
                    $b->satuan,
                    $rekomendasiBeli,
                    $harga,
                    $estimasiBiaya,
                    $status,
                ]);
            }

            fclose($handle);
        }, $filename);
    }

    private function resetInput()
    {
        $this->reset([
            'bahan_baku_id',
            'nama_bahan',
            'stok',
            'stok_minimum',
            'harga_satuan',
            'alasan_koreksi',
            'harga_beli_kemasan',
            'isi_per_kemasan',
            'showKalkulatorKemasan',
        ]);
        $this->satuan = 'gram';
    }

    public function render()
    {
        $allBahan = BahanBaku::with(['reseps.produk'])->withCount('reseps')->orderBy('nama_bahan', 'asc')->get();

        // Statistik Kartu Atas
        $totalJenisBahan = $allBahan->count();
        $stokMenipisCount = $allBahan->filter(fn($b) => $this->getStok($b) > 0 && $this->getStok($b) <= (float) $b->stok_minimum)->count();
        $stokHabisCount   = $allBahan->filter(fn($b) => $this->getStok($b) <= 0)->count();
        $estimasiValuasi  = $allBahan->sum(fn($b) => round($this->getStok($b) * $this->getHargaSatuan($b)));

        // Penyaringan Tabel
        $bahanBakus = $allBahan->filter(function ($b) {
            $stok = $this->getStok($b);
            $min  = (float) $b->stok_minimum;

            if ($this->search && !str_contains(strtolower($b->nama_bahan), strtolower($this->search))) {
                return false;
            }

            if ($this->filterSatuan && strtolower($b->satuan) !== strtolower($this->filterSatuan)) {
                return false;
            }

            if ($this->filterStatus === 'habis' && $stok > 0) {
                return false;
            }
            if ($this->filterStatus === 'menipis' && $stok > $min) {
                return false;
            }
            if ($this->filterStatus === 'aman' && $stok <= $min) {
                return false;
            }

            return true;
        });

        return view('livewire.admin.kelola-bahan-baku', [
            'bahanBakus'       => $bahanBakus,
            'totalJenisBahan'  => $totalJenisBahan,
            'stokMenipisCount' => $stokMenipisCount,
            'stokHabisCount'   => $stokHabisCount,
            'estimasiValuasi'  => $estimasiValuasi,
        ])->layout('layouts.app');
    }
}
