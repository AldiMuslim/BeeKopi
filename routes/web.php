<?php

use App\Livewire\Admin\AuditLogIndex;
use App\Livewire\Admin\DashboardAdmin;
use App\Livewire\Admin\KelolaLapak;
use App\Livewire\Admin\KelolaProduk;
use App\Livewire\Admin\KelolaUser;
use App\Livewire\Management\AlokasiStok;
use App\Livewire\Management\AnalisisPenjualan;
use App\Livewire\Management\DashboardLabaRugi;
use App\Livewire\Management\VerifikasiLaporan;
use App\Livewire\Produksi\KelolaBahanBaku;
use App\Livewire\Produksi\PermintaanRestockLapak;
use App\Livewire\Produksi\RacikKopi;
use App\Livewire\Sales\KasirLapak;
use App\Livewire\Sales\LaporanPenutupan;
use App\Livewire\Sales\PenerimaanStok;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {

    // Rute Profile kompatibel Breeze
    Route::view('profile', 'profile')->name('profile.edit');
    Route::view('profile-view', 'profile')->name('profile');

    // Rute Logout
    Route::post('/logout', function (Request $request) {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    })->name('logout');

    // Redirect Dashboard Sesuai Peran
    Route::get('/dashboard', function () {
        $role = Auth::user()->role?->nama;

        return match ($role) {
            'sales' => redirect()->route('sales.kasir'),
            'produksi' => redirect()->route('produksi.bahan-baku'),
            'management' => redirect()->route('management.verifikasi'),
            'admin' => redirect()->route('admin.users'),
            default => abort(403, 'Akses tidak diizinkan.'),
        };
    })->name('dashboard');

    // Modul Sales
    Route::prefix('sales')->name('sales.')->group(function () {
        Route::get('/kasir', KasirLapak::class)->name('kasir');
        Route::get('/laporan-penutupan', LaporanPenutupan::class)->name('laporan-penutupan');
        Route::get('/penerimaan-stok', PenerimaanStok::class)->name('penerimaan-stok');
    });

    // Modul Produksi
    Route::prefix('produksi')->name('produksi.')->group(function () {
        Route::get('/restock-lapak', PermintaanRestockLapak::class)->name('restock-lapak');
        Route::get('/bahan-baku', KelolaBahanBaku::class)->name('bahan-baku');
        Route::get('/racik-kopi', RacikKopi::class)->name('racik-kopi');
    });

    // Modul Management
    Route::prefix('management')->name('management.')->group(function () {
        Route::get('/analisis-penjualan', AnalisisPenjualan::class)->name('analisis-penjualan');
        Route::get('/verifikasi-laporan', VerifikasiLaporan::class)->name('verifikasi');
        Route::get('/alokasi-stok', AlokasiStok::class)->name('alokasi-stok');
        Route::get('/laba-rugi', DashboardLabaRugi::class)->name('laba-rugi');
    });

    // Modul Admin
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', DashboardAdmin::class)->name('dashboard');
        Route::get('/users', KelolaUser::class)->name('users');
        Route::get('/lapak', KelolaLapak::class)->name('lapak');
        Route::get('/produk', KelolaProduk::class)->name('produk');
        Route::get('/bahan-baku', KelolaBahanBaku::class)->name('bahan-baku');
        Route::get('/audit-log', AuditLogIndex::class)->name('audit-log');
    });
});

require __DIR__ . '/auth.php';
