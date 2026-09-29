<?php

namespace Database\Seeders;

use App\Models\BatchObat;
use App\Models\Dokter;
use App\Models\Kategori;
use App\Models\Notifikasi;
use App\Models\Obat;
use App\Models\Pelanggan;
use App\Models\PembayaranHutang;
use App\Models\Pembelian;
use App\Models\PembelianDetail;
use App\Models\Pengaturan;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\PurchaseOrder;
use App\Models\Resep;
use App\Models\ReturPenjualan;
use App\Models\ReturPenjualanDetail;
use App\Models\Satuan;
use App\Models\ShiftKasir;
use App\Models\StokMutasi;
use App\Models\Suplier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $today = Carbon::today();

        // 1. PENGATURAN DEFAULT
        $pengaturans = [
            ['key' => 'nama_apotek', 'value' => 'Apotek Bintan 88', 'tipe' => 'string', 'keterangan' => 'Nama Resmi Apotek'],
            ['key' => 'alamat_apotek', 'value' => 'Jl. Nusantara No. 88, Kijang Kota, Bintan Timur, Kepulauan Riau', 'tipe' => 'string', 'keterangan' => 'Alamat Apotek'],
            ['key' => 'telepon_apotek', 'value' => '0771-482910 / 0812-7088-8888', 'tipe' => 'string', 'keterangan' => 'Nomor Telepon / WhatsApp'],
            ['key' => 'sia_apotek', 'value' => 'SIA-1992/DK-BINTAN/2022', 'tipe' => 'string', 'keterangan' => 'Surat Izin Apotek (SIA)'],
            ['key' => 'sipa_apoteker', 'value' => 'SIPA-19950812/STRA-KEMKES/2023', 'tipe' => 'string', 'keterangan' => 'SIPA Apoteker Penanggung Jawab'],
            ['key' => 'nama_apoteker_pj', 'value' => 'apt. Siti Rahma, S.Farm', 'tipe' => 'string', 'keterangan' => 'Apoteker Penanggung Jawab'],
            ['key' => 'telegram_bot_token', 'value' => env('TELEGRAM_BOT_TOKEN', ''), 'tipe' => 'string', 'keterangan' => 'Telegram Bot Token'],
            ['key' => 'telegram_chat_id', 'value' => env('TELEGRAM_CHAT_ID', ''), 'tipe' => 'string', 'keterangan' => 'Telegram Group / Channel Chat ID'],
            ['key' => 'notif_stok_menipis', 'value' => '1', 'tipe' => 'boolean', 'keterangan' => 'Kirim notifikasi stok menipis'],
            ['key' => 'notif_stok_kosong', 'value' => '1', 'tipe' => 'boolean', 'keterangan' => 'Kirim notifikasi stok habis / kosong'],
            ['key' => 'notif_expired', 'value' => '1', 'tipe' => 'boolean', 'keterangan' => 'Kirim notifikasi obat kedaluwarsa & hampir expired'],
            ['key' => 'notif_hutang', 'value' => '1', 'tipe' => 'boolean', 'keterangan' => 'Kirim notifikasi hutang suplier jatuh tempo'],
            ['key' => 'notif_transaksi_void', 'value' => '1', 'tipe' => 'boolean', 'keterangan' => 'Kirim notifikasi pembatalan/void transaksi'],
            ['key' => 'notif_ringkasan_harian', 'value' => '1', 'tipe' => 'boolean', 'keterangan' => 'Kirim ringkasan harian penjualan'],
            ['key' => 'jam_ringkasan_harian', 'value' => '21:00', 'tipe' => 'string', 'keterangan' => 'Waktu pengiriman ringkasan harian'],
            ['key' => 'ambang_expired_hari', 'value' => '90', 'tipe' => 'integer', 'keterangan' => 'Ambang hari peringatan kedaluwarsa'],
        ];

        foreach ($pengaturans as $item) {
            Pengaturan::create($item);
        }

        // 2. USER DEMO
        $owner = User::create([
            'name' => 'H. Suherman (Owner)',
            'email' => 'owner@apotekbintan88.com',
            'password' => Hash::make('password'),
            'role' => 'owner',
            'telegram_chat_id' => null,
            'status_aktif' => true,
        ]);

        $apoteker = User::create([
            'name' => 'apt. Siti Rahma, S.Farm',
            'email' => 'apoteker@apotekbintan88.com',
            'password' => Hash::make('password'),
            'role' => 'apoteker',
            'telegram_chat_id' => null,
            'status_aktif' => true,
        ]);

        $kasir = User::create([
            'name' => 'Budi Santoso (Kasir)',
            'email' => 'kasir@apotekbintan88.com',
            'password' => Hash::make('password'),
            'role' => 'kasir',
            'telegram_chat_id' => null,
            'status_aktif' => true,
        ]);

        $gudang = User::create([
            'name' => 'Rian Hidayat (Gudang)',
            'email' => 'gudang@apotekbintan88.com',
            'password' => Hash::make('password'),
            'role' => 'admin_gudang',
            'telegram_chat_id' => null,
            'status_aktif' => true,
        ]);

        // 3. KATEGORI (10 Kategori)
        $kategoris = [
            ['nama' => 'Obat Bebas', 'deskripsi' => 'Obat dengan lingkaran hijau, dapat dibeli bebas tanpa resep.'],
            ['nama' => 'Obat Bebas Terbatas', 'deskripsi' => 'Obat dengan lingkaran biru dengan tanda peringatan P1-P6.'],
            ['nama' => 'Obat Keras', 'deskripsi' => 'Obat dengan lingkaran merah berhuruf K, wajib dengan resep dokter.'],
            ['nama' => 'Antibiotik', 'deskripsi' => 'Obat antibakteri yang wajib resep dan harus dihabiskan.'],
            ['nama' => 'Vitamin & Suplemen', 'deskripsi' => 'Multivitamin, suplemen nutrisi, dan daya tahan tubuh.'],
            ['nama' => 'Obat Herbal / Jamu', 'deskripsi' => 'Obat tradisional berbahan alami terstandar / fitofarmaka.'],
            ['nama' => 'Alat Kesehatan (Alkes)', 'deskripsi' => 'Kasa, perban, plester, termometer, spuit, dan perlengkapan medis.'],
            ['nama' => 'Salep & Obat Luar', 'deskripsi' => 'Krim antibiotik, antijamur, gel pereda nyeri, antiseptik.'],
            ['nama' => 'Sirup & Suspensi Anak', 'deskripsi' => 'Sediaan cair untuk anak-anak dan bayi.'],
            ['nama' => 'Tetes Mata & Telinga', 'deskripsi' => 'Tetes mata steril dan cairan pembersih telinga.'],
        ];

        $kategoriMap = [];
        foreach ($kategoris as $k) {
            $cat = Kategori::create($k);
            $kategoriMap[$k['nama']] = $cat->id;
        }

        // 4. SATUAN (10 Satuan)
        $satuans = [
            ['nama' => 'Tablet', 'deskripsi' => 'Sediaan padat tablet pipih/lonjong'],
            ['nama' => 'Strip', 'deskripsi' => 'Kemasan strip isi 4/10 tablet/kapsul'],
            ['nama' => 'Kapsul', 'deskripsi' => 'Sediaan kapsul gelatin'],
            ['nama' => 'Botol', 'deskripsi' => 'Kemasan botol sirup / cairan / tetes'],
            ['nama' => 'Box', 'deskripsi' => 'Kemasan dus luar'],
            ['nama' => 'Tube', 'deskripsi' => 'Kemasan salep / gel / krim'],
            ['nama' => 'Sachet', 'deskripsi' => 'Kemasan serbuk / cairan sekali pakai'],
            ['nama' => 'Ampul', 'deskripsi' => 'Sediaan injeksi ampul kaca'],
            ['nama' => 'Vial', 'deskripsi' => 'Sediaan injeksi vial kecil'],
            ['nama' => 'Pcs', 'deskripsi' => 'Satuan buah / potong / unit umum'],
        ];

        $satuanMap = [];
        foreach ($satuans as $s) {
            $sat = Satuan::create($s);
            $satuanMap[$s['nama']] = $sat->id;
        }

        // 5. SUPLIER (5 PBF)
        $supliers = [
            [
                'nama' => 'PT. Kimia Farma Trading & Distribution',
                'kontak_person' => 'Agus Priyono',
                'telepon' => '0778-452100',
                'email' => 'order.batam@kftd.co.id',
                'alamat' => 'Komplek Inti Batam Blok A No. 3, Sei Panas, Batam',
            ],
            [
                'nama' => 'PT. Enseval Putera Megatrading Tbk',
                'kontak_person' => 'Dwi Handayani',
                'telepon' => '0778-428990',
                'email' => 'sales.btm@enseval.com',
                'alamat' => 'Jl. Kerapu No. 12, Batu Ampar, Batam',
            ],
            [
                'nama' => 'PT. Anugrah Argon Medica (AAM)',
                'kontak_person' => 'Ferry Santoso',
                'telepon' => '0778-467812',
                'email' => 'cs.batam@anugrah-argon.com',
                'alamat' => 'Kawasan Industri Cammo Blok B No. 8, Batam Center',
            ],
            [
                'nama' => 'PT. Kalbe Farma Tbk Cabang Riau',
                'kontak_person' => 'Rina Marlina',
                'telepon' => '0771-318920',
                'email' => 'rina.m@kalbe.co.id',
                'alamat' => 'Jl. D.I. Panjaitan Km. 8, Tanjungpinang',
            ],
            [
                'nama' => 'PT. Mensa Binasukses (MBS)',
                'kontak_person' => 'Hendrik Tan',
                'telepon' => '0778-456001',
                'email' => 'batam@mbs.co.id',
                'alamat' => 'Komplek Citra Indah Blok C No. 5, Batam',
            ],
        ];

        $suplierList = [];
        foreach ($supliers as $sup) {
            $suplierList[] = Suplier::create($sup);
        }

        // 6. DOKTER (5 Dokter)
        $dokters = [
            ['nama' => 'dr. Hendra Wijaya, Sp.PD', 'no_sip' => '446/SIP-D/BINTAN/2021', 'spesialis' => 'Spesialis Penyakit Dalam', 'telepon' => '0812-7001-2233', 'alamat' => 'Praktek Bersama Jl. Merdeka No. 12, Kijang'],
            ['nama' => 'dr. Maya Kartika, Sp.A', 'no_sip' => '512/SIP-D/BINTAN/2022', 'spesialis' => 'Spesialis Anak', 'telepon' => '0813-8822-4455', 'alamat' => 'Klinik Bunda & Buah Hati, Bintan Timur'],
            ['nama' => 'dr. Andi Pratama', 'no_sip' => '104/SIP-D/BINTAN/2020', 'spesialis' => 'Dokter Umum', 'telepon' => '0811-9988-7711', 'alamat' => 'Puskesmas Kijang Kota'],
            ['nama' => 'drg. Citra Lestari', 'no_sip' => '308/SIP-DG/BINTAN/2023', 'spesialis' => 'Dokter Gigi', 'telepon' => '0821-3344-5566', 'alamat' => 'Klinik Gigi Sehat Ceria, Kijang'],
            ['nama' => 'dr. Ahmad Fauzi, Sp.JP', 'no_sip' => '602/SIP-D/BINTAN/2022', 'spesialis' => 'Spesialis Jantung & Pembuluh Darah', 'telepon' => '0812-6677-8899', 'alamat' => 'RSUD Bintan, Kijang Kota'],
        ];

        $dokterList = [];
        foreach ($dokters as $doc) {
            $dokterList[] = Dokter::create($doc);
        }

        // 7. PELANGGAN (10 Pelanggan)
        $pelanggans = [
            ['nama' => 'Budi Prabowo', 'telepon' => '0812-7001-1122', 'alamat' => 'Jl. Barek Motor No. 45, Kijang'],
            ['nama' => 'Dewi Anggraini', 'telepon' => '0813-6455-2233', 'alamat' => 'Perumahan Bintan Permai Blok C-12'],
            ['nama' => 'Agus Salim', 'telepon' => '0852-6411-9988', 'alamat' => 'Kampung Kolam No. 7, Kijang Kota'],
            ['nama' => 'Nurul Hidayati', 'telepon' => '0821-7788-4455', 'alamat' => 'Jl. Nusantara Km. 20, Bintan Timur'],
            ['nama' => 'Hendri Gunawan', 'telepon' => '0812-7711-3344', 'alamat' => 'Kampung Melayu No. 88, Bintan'],
            ['nama' => 'Rina Novita', 'telepon' => '0813-9090-1122', 'alamat' => 'Jl. Pelabuhan Kijang No. 2'],
            ['nama' => 'Joko Susilo', 'telepon' => '0823-8899-7711', 'alamat' => 'Perum Bintan Indah No. 15'],
            ['nama' => 'Ratna Sari', 'telepon' => '0853-4455-6677', 'alamat' => 'Kampung Sei Enam Laut No. 24'],
            ['nama' => 'Dedi Kurniawan', 'telepon' => '0812-7080-9010', 'alamat' => 'Jl. Korindo No. 18, Kijang'],
            ['nama' => 'Sri Wahyuni', 'telepon' => '0813-7890-4321', 'alamat' => 'Kampung Barek Motor No. 10'],
        ];

        $pelangganList = [];
        foreach ($pelanggans as $cust) {
            $pelangganList[] = Pelanggan::create($cust);
        }

        // 8. 50 DATA OBAT REALISTIS DENGAN BATCH & KONDISI BERAGAM
        $daftarObat = [
            // Obat Bebas & Bebas Terbatas
            ['kode' => 'OBT-001', 'nama' => 'Paracetamol 500mg', 'generik' => 'Paracetamol', 'kat' => 'Obat Bebas', 'sat' => 'Strip', 'beli' => 4500, 'jual' => 6500, 'min' => 20, 'rak' => 'A-01', 'resep' => false, 'desc' => 'Pereda nyeri dan penurun demam.'],
            ['kode' => 'OBT-002', 'nama' => 'Panadol Extra 10 Tablet', 'generik' => 'Paracetamol + Caffeine', 'kat' => 'Obat Bebas', 'sat' => 'Strip', 'beli' => 11000, 'jual' => 14000, 'min' => 15, 'rak' => 'A-01', 'resep' => false, 'desc' => 'Meredakan sakit kepala dan sakit gigi membandel.'],
            ['kode' => 'OBT-003', 'nama' => 'Bodrex Tablet', 'generik' => 'Paracetamol + Caffeine', 'kat' => 'Obat Bebas', 'sat' => 'Strip', 'beli' => 4000, 'jual' => 6000, 'min' => 25, 'rak' => 'A-02', 'resep' => false, 'desc' => 'Pereda sakit kepala dan demam.'],
            ['kode' => 'OBT-004', 'nama' => 'Promag Tablet Kunyah', 'generik' => 'Hydrotalcite + Magnesium Hydroxide + Simethicone', 'kat' => 'Obat Bebas', 'sat' => 'Strip', 'beli' => 8500, 'jual' => 11000, 'min' => 20, 'rak' => 'A-03', 'resep' => false, 'desc' => 'Meredakan sakit maag, asam lambung berlebih.'],
            ['kode' => 'OBT-005', 'nama' => 'Antasida Doen Tablet', 'generik' => 'Aluminium Hydroxide + Magnesium Hydroxide', 'kat' => 'Obat Bebas', 'sat' => 'Strip', 'beli' => 2500, 'jual' => 4000, 'min' => 30, 'rak' => 'A-03', 'resep' => false, 'desc' => 'Antasida generik untuk mengatasi gejala maag.'],
            ['kode' => 'OBT-006', 'nama' => 'Mixagrip Flu & Batuk', 'generik' => 'Paracetamol + Dextromethorphan + Phenylephrine', 'kat' => 'Obat Bebas Terbatas', 'sat' => 'Strip', 'beli' => 3200, 'jual' => 5000, 'min' => 20, 'rak' => 'A-04', 'resep' => false, 'desc' => 'Meredakan gejala flu disertai batuk.'],
            ['kode' => 'OBT-007', 'nama' => 'OBH Combi Batuk & Flu 100ml', 'generik' => 'Succus Liquiritiae + Paracetamol + Ammonium Chloride', 'kat' => 'Obat Bebas Terbatas', 'sat' => 'Botol', 'beli' => 18500, 'jual' => 23000, 'min' => 10, 'rak' => 'B-01', 'resep' => false, 'desc' => 'Obat batuk hitam untuk batuk berdahak dan flu.'],
            ['kode' => 'OBT-008', 'nama' => 'Diapet Kapsul', 'generik' => 'Ekstrak Daun Jambu Biji & Kunyit', 'kat' => 'Obat Herbal / Jamu', 'sat' => 'Strip', 'beli' => 3800, 'jual' => 5500, 'min' => 15, 'rak' => 'B-02', 'resep' => false, 'desc' => 'Membantu mengurangi frekuensi buang air besar pada diare.'],
            ['kode' => 'OBT-009', 'nama' => 'Oralit 200ml Sachet', 'generik' => 'Natrium Klorida + Kalium Klorida + Glukosa', 'kat' => 'Obat Bebas', 'sat' => 'Sachet', 'beli' => 1200, 'jual' => 2000, 'min' => 40, 'rak' => 'B-02', 'resep' => false, 'desc' => 'Cairan rehidrasi pencegah dehidrasi akibat diare.'],
            ['kode' => 'OBT-010', 'nama' => 'Tolak Angin Cair Sachet', 'generik' => 'Ekstrak Herbal Adas + Jahe + Daun Mint', 'kat' => 'Obat Herbal / Jamu', 'sat' => 'Sachet', 'beli' => 3500, 'jual' => 5000, 'min' => 50, 'rak' => 'B-03', 'resep' => false, 'desc' => 'Meredakan masuk angin, mual, dan kembung.'],

            // Obat Keras & Antibiotik (Wajib Resep)
            ['kode' => 'OBT-011', 'nama' => 'Amoxicillin 500mg', 'generik' => 'Amoxicillin Trihydrate', 'kat' => 'Antibiotik', 'sat' => 'Strip', 'beli' => 5500, 'jual' => 8000, 'min' => 25, 'rak' => 'C-01', 'resep' => true, 'desc' => 'Antibiotik penisilin broad-spectrum untuk infeksi bakteri.'],
            ['kode' => 'OBT-012', 'nama' => 'Cefixime 100mg Kapsul', 'generik' => 'Cefixime', 'kat' => 'Antibiotik', 'sat' => 'Strip', 'beli' => 22000, 'jual' => 30000, 'min' => 15, 'rak' => 'C-01', 'resep' => true, 'desc' => 'Antibiotik sefalosporin generasi ke-3 untuk infeksi saluran kemih dan pernapasan.'],
            ['kode' => 'OBT-013', 'nama' => 'Ciprofloxacin 500mg', 'generik' => 'Ciprofloxacin HCl', 'kat' => 'Antibiotik', 'sat' => 'Strip', 'beli' => 9000, 'jual' => 13500, 'min' => 15, 'rak' => 'C-02', 'resep' => true, 'desc' => 'Antibiotik kuinolon untuk infeksi saluran cerna & kemih.'],
            ['kode' => 'OBT-014', 'nama' => 'Amlodipine 5mg Tablet', 'generik' => 'Amlodipine Besilate', 'kat' => 'Obat Keras', 'sat' => 'Strip', 'beli' => 3500, 'jual' => 5500, 'min' => 30, 'rak' => 'C-03', 'resep' => true, 'desc' => 'Antihipertensi golongan calcium channel blocker.'],
            ['kode' => 'OBT-015', 'nama' => 'Amlodipine 10mg Tablet', 'generik' => 'Amlodipine Besilate', 'kat' => 'Obat Keras', 'sat' => 'Strip', 'beli' => 5000, 'jual' => 7500, 'min' => 30, 'rak' => 'C-03', 'resep' => true, 'desc' => 'Antihipertensi dosis tinggi.'],
            ['kode' => 'OBT-016', 'nama' => 'Captopril 25mg', 'generik' => 'Captopril', 'kat' => 'Obat Keras', 'sat' => 'Strip', 'beli' => 2800, 'jual' => 4500, 'min' => 25, 'rak' => 'C-04', 'resep' => true, 'desc' => 'Antihipertensi ACE inhibitor.'],
            ['kode' => 'OBT-017', 'nama' => 'Metformin 500mg', 'generik' => 'Metformin HCl', 'kat' => 'Obat Keras', 'sat' => 'Strip', 'beli' => 3200, 'jual' => 5000, 'min' => 30, 'rak' => 'C-05', 'resep' => true, 'desc' => 'Antidiabetes oral lini pertama golongan biguanida.'],
            ['kode' => 'OBT-018', 'nama' => 'Glimepiride 2mg', 'generik' => 'Glimepiride', 'kat' => 'Obat Keras', 'sat' => 'Strip', 'beli' => 8500, 'jual' => 12000, 'min' => 15, 'rak' => 'C-05', 'resep' => true, 'desc' => 'Antidiabetes golongan sulfonilurea.'],
            ['kode' => 'OBT-019', 'nama' => 'Omeprazole 20mg Kapsul', 'generik' => 'Omeprazole', 'kat' => 'Obat Keras', 'sat' => 'Strip', 'beli' => 7000, 'jual' => 10500, 'min' => 25, 'rak' => 'C-06', 'resep' => true, 'desc' => 'PPI penekan sekresi asam lambung pada GERD dan tukak lambung.'],
            ['kode' => 'OBT-020', 'nama' => 'Lansoprazole 30mg Kapsul', 'generik' => 'Lansoprazole', 'kat' => 'Obat Keras', 'sat' => 'Strip', 'beli' => 9500, 'jual' => 14000, 'min' => 20, 'rak' => 'C-06', 'resep' => true, 'desc' => 'PPI generasi kedua untuk tukak duodenum.'],
            ['kode' => 'OBT-021', 'nama' => 'Simvastatin 20mg', 'generik' => 'Simvastatin', 'kat' => 'Obat Keras', 'sat' => 'Strip', 'beli' => 4500, 'jual' => 7000, 'min' => 25, 'rak' => 'D-01', 'resep' => true, 'desc' => 'Penurun kolesterol total dan LDL golongan statin.'],
            ['kode' => 'OBT-022', 'nama' => 'Atorvastatin 20mg', 'generik' => 'Atorvastatin Calcium', 'kat' => 'Obat Keras', 'sat' => 'Strip', 'beli' => 24000, 'jual' => 32000, 'min' => 10, 'rak' => 'D-01', 'resep' => true, 'desc' => 'Statin poten untuk hiperkolesterolemia berat.'],
            ['kode' => 'OBT-023', 'nama' => 'Allopurinol 100mg', 'generik' => 'Allopurinol', 'kat' => 'Obat Keras', 'sat' => 'Strip', 'beli' => 3600, 'jual' => 5500, 'min' => 20, 'rak' => 'D-02', 'resep' => true, 'desc' => 'Penurun kadar asam urat darah pencegah asam urat kambuh.'],
            ['kode' => 'OBT-024', 'nama' => 'Cetirizine 10mg Tablet', 'generik' => 'Cetirizine Dihydrochloride', 'kat' => 'Obat Keras', 'sat' => 'Strip', 'beli' => 4000, 'jual' => 6500, 'min' => 25, 'rak' => 'D-03', 'resep' => true, 'desc' => 'Antihistamin generasi ke-2 tidak menyebabkan kantuk berat.'],
            ['kode' => 'OBT-025', 'nama' => 'Dexamethasone 0.5mg', 'generik' => 'Dexamethasone', 'kat' => 'Obat Keras', 'sat' => 'Strip', 'beli' => 2000, 'jual' => 3500, 'min' => 30, 'rak' => 'D-04', 'resep' => true, 'desc' => 'Kortikosteroid antiinflamasi dan antialergi kuat.'],
            ['kode' => 'OBT-026', 'nama' => 'Methylprednisolone 4mg', 'generik' => 'Methylprednisolone', 'kat' => 'Obat Keras', 'sat' => 'Strip', 'beli' => 6000, 'jual' => 9000, 'min' => 20, 'rak' => 'D-04', 'resep' => true, 'desc' => 'Antiinflamasi steroid pada radang tenggorokan dan sendi.'],
            ['kode' => 'OBT-027', 'nama' => 'Asam Mefenamat 500mg', 'generik' => 'Mefenamic Acid', 'kat' => 'Obat Keras', 'sat' => 'Strip', 'beli' => 4500, 'jual' => 7000, 'min' => 25, 'rak' => 'D-05', 'resep' => true, 'desc' => 'Pereda nyeri gigi, nyeri haid, dan pasca trauma.'],
            ['kode' => 'OBT-028', 'nama' => 'Ibuprofen 400mg', 'generik' => 'Ibuprofen', 'kat' => 'Obat Bebas Terbatas', 'sat' => 'Strip', 'beli' => 3800, 'jual' => 6000, 'min' => 20, 'rak' => 'D-05', 'resep' => false, 'desc' => 'Antiinflamasi non-steroid untuk nyeri dan demam.'],
            ['kode' => 'OBT-029', 'nama' => 'Salbutamol 2mg', 'generik' => 'Salbutamol Sulfate', 'kat' => 'Obat Keras', 'sat' => 'Strip', 'beli' => 2500, 'jual' => 4500, 'min' => 15, 'rak' => 'D-06', 'resep' => true, 'desc' => 'Bronkodilator pereda sesak napas pada asma.'],
            ['kode' => 'OBT-030', 'nama' => 'Furosemide 40mg', 'generik' => 'Furosemide', 'kat' => 'Obat Keras', 'sat' => 'Strip', 'beli' => 3000, 'jual' => 5000, 'min' => 15, 'rak' => 'D-06', 'resep' => true, 'desc' => 'Diuretik loop pada edema dan hipertensi.'],

            // Vitamin & Suplemen
            ['kode' => 'OBT-031', 'nama' => 'Neurobion Forte Tablet', 'generik' => 'Vitamin B1 + B6 + B12 Dosis Tinggi', 'kat' => 'Vitamin & Suplemen', 'sat' => 'Strip', 'beli' => 38000, 'jual' => 46000, 'min' => 15, 'rak' => 'E-01', 'resep' => false, 'desc' => 'Vitamin neurotropik untuk kebas, kesemutan, dan saraf.'],
            ['kode' => 'OBT-032', 'nama' => 'Enervon-C Multivitamin 30s', 'generik' => 'Vitamin C 500mg + Vitamin B Kompleks', 'kat' => 'Vitamin & Suplemen', 'sat' => 'Botol', 'beli' => 34000, 'jual' => 42000, 'min' => 10, 'rak' => 'E-01', 'resep' => false, 'desc' => 'Menjaga daya tahan tubuh dan metabolisme energi.'],
            ['kode' => 'OBT-033', 'nama' => 'CDR Effervescent 10s', 'generik' => 'Kalsium + Vitamin C + D + B6', 'kat' => 'Vitamin & Suplemen', 'sat' => 'Tube', 'beli' => 45000, 'jual' => 54000, 'min' => 10, 'rak' => 'E-02', 'resep' => false, 'desc' => 'Kalsium effervescent rasa jeruk untuk kesehatan tulang.'],
            ['kode' => 'OBT-034', 'nama' => 'Sangobion Kapsul', 'generik' => 'Ferrous Gluconate + Asam Folat + Vitamin C', 'kat' => 'Vitamin & Suplemen', 'sat' => 'Strip', 'beli' => 16000, 'jual' => 20500, 'min' => 15, 'rak' => 'E-02', 'resep' => false, 'desc' => 'Suplemen zat besi penambah darah pada anemia.'],
            ['kode' => 'OBT-035', 'nama' => 'Imboost Force Tablet', 'generik' => 'Echinacea Purpurea + Zinc Picolinate + Black Elderberry', 'kat' => 'Vitamin & Suplemen', 'sat' => 'Strip', 'beli' => 68000, 'jual' => 82000, 'min' => 10, 'rak' => 'E-03', 'resep' => false, 'desc' => 'Imunomodulator mempercepat pemulihan dari sakit.'],
            ['kode' => 'OBT-036', 'nama' => 'Vitamin D3 1000 IU Tablet', 'generik' => 'Cholecalciferol', 'kat' => 'Vitamin & Suplemen', 'sat' => 'Botol', 'beli' => 48000, 'jual' => 60000, 'min' => 10, 'rak' => 'E-03', 'resep' => false, 'desc' => 'Suplemen vitamin D3 penunjang sistem kekebalan tubuh.'],

            // Salep & Obat Luar
            ['kode' => 'OBT-037', 'nama' => 'Bioplacenton Gel 15g', 'generik' => 'Placenta Extract + Neomycin Sulfate', 'kat' => 'Salep & Obat Luar', 'sat' => 'Tube', 'beli' => 22000, 'jual' => 28000, 'min' => 12, 'rak' => 'F-01', 'resep' => false, 'desc' => 'Obat luka bakar ringan dan luka gores bernanah.'],
            ['kode' => 'OBT-038', 'nama' => 'Salep 88 6g', 'generik' => 'Asam Salisilat + Asam Benzoat + Sulfur', 'kat' => 'Salep & Obat Luar', 'sat' => 'Pcs', 'beli' => 9500, 'jual' => 13000, 'min' => 20, 'rak' => 'F-01', 'resep' => false, 'desc' => 'Mengobati penyakit kulit panu, kadas, kudis, kurap.'],
            ['kode' => 'OBT-039', 'nama' => 'Thrombophob Gel 20g', 'generik' => 'Heparin Sodium', 'kat' => 'Salep & Obat Luar', 'sat' => 'Tube', 'beli' => 52000, 'jual' => 64000, 'min' => 8, 'rak' => 'F-02', 'resep' => false, 'desc' => 'Gel pereda memar, lebam, dan varises.'],
            ['kode' => 'OBT-040', 'nama' => 'Gentamicin Salep Kulit 5g', 'generik' => 'Gentamicin Sulfate', 'kat' => 'Salep & Obat Luar', 'sat' => 'Tube', 'beli' => 5500, 'jual' => 8500, 'min' => 15, 'rak' => 'F-02', 'resep' => true, 'desc' => 'Antibiotik topikal untuk infeksi bakteri kulit.'],
            ['kode' => 'OBT-041', 'nama' => 'Betason-N Cream 5g', 'generik' => 'Betamethasone + Neomycin', 'kat' => 'Salep & Obat Luar', 'sat' => 'Tube', 'beli' => 14000, 'jual' => 19500, 'min' => 10, 'rak' => 'F-03', 'resep' => true, 'desc' => 'Krim kombinasi kortikosteroid dan antibiotik infeksi eksim.'],
            ['kode' => 'OBT-042', 'nama' => 'Minyak Kayu Putih Cap Lang 60ml', 'generik' => 'Oleum Cajuputi 100%', 'kat' => 'Obat Bebas', 'sat' => 'Botol', 'beli' => 21000, 'jual' => 26000, 'min' => 20, 'rak' => 'F-04', 'resep' => false, 'desc' => 'Minyak herbal menghangatkan tubuh dan meredakan gatal gigitan serangga.'],

            // Sediaan Sirup Anak & Tetes Mata
            ['kode' => 'OBT-043', 'nama' => 'Sanmol Sirup Anak 60ml', 'generik' => 'Paracetamol 120mg/5ml', 'kat' => 'Sirup & Suspensi Anak', 'sat' => 'Botol', 'beli' => 18000, 'jual' => 23000, 'min' => 15, 'rak' => 'G-01', 'resep' => false, 'desc' => 'Sirup pereda panas dan demam rasa stroberi untuk anak.'],
            ['kode' => 'OBT-044', 'nama' => 'Tempra Drops Bayi 15ml', 'generik' => 'Paracetamol 80mg/0.8ml', 'kat' => 'Sirup & Suspensi Anak', 'sat' => 'Botol', 'beli' => 47000, 'jual' => 56000, 'min' => 10, 'rak' => 'G-01', 'resep' => false, 'desc' => 'Tetes penurun demam bayi dilengkapi pipet tetes.'],
            ['kode' => 'OBT-045', 'nama' => 'Insto Regular Tetes Mata 7.5ml', 'generik' => 'Tetrahydrozoline HCl 0.05%', 'kat' => 'Tetes Mata & Telinga', 'sat' => 'Botol', 'beli' => 14500, 'jual' => 18500, 'min' => 20, 'rak' => 'G-02', 'resep' => false, 'desc' => 'Meredakan iritasi mata merah akibat debu dan asap.'],
            ['kode' => 'OBT-046', 'nama' => 'Rohto Cool Tetes Mata 7ml', 'generik' => 'Naphazoline HCl', 'kat' => 'Tetes Mata & Telinga', 'sat' => 'Botol', 'beli' => 15000, 'jual' => 19000, 'min' => 15, 'rak' => 'G-02', 'resep' => false, 'desc' => 'Tetes mata dengan sensasi dingin menyegarkan.'],

            // Alat Kesehatan (Alkes)
            ['kode' => 'OBT-047', 'nama' => 'Betadine Antiseptik Cair 60ml', 'generik' => 'Povidone Iodine 10%', 'kat' => 'Alat Kesehatan (Alkes)', 'sat' => 'Botol', 'beli' => 27000, 'jual' => 34000, 'min' => 15, 'rak' => 'H-01', 'resep' => false, 'desc' => 'Antiseptik luka luar pembunuh kuman patogen.'],
            ['kode' => 'OBT-048', 'nama' => 'Hansaplast Plester Kain Roll 1m', 'generik' => 'Kain Perekat Medis', 'kat' => 'Alat Kesehatan (Alkes)', 'sat' => 'Pcs', 'beli' => 6500, 'jual' => 9500, 'min' => 25, 'rak' => 'H-01', 'resep' => false, 'desc' => 'Plester elastis pelindung luka gores kecil.'],
            ['kode' => 'OBT-049', 'nama' => 'Alkohol 70% Medis 100ml', 'generik' => 'Ethanol 70%', 'kat' => 'Alat Kesehatan (Alkes)', 'sat' => 'Botol', 'beli' => 7000, 'jual' => 10000, 'min' => 20, 'rak' => 'H-02', 'resep' => false, 'desc' => 'Antiseptik dan disinfektan alat medis.'],
            ['kode' => 'OBT-050', 'nama' => 'Termometer Digital Onemed', 'generik' => 'Digital Thermometer', 'kat' => 'Alat Kesehatan (Alkes)', 'sat' => 'Pcs', 'beli' => 24000, 'jual' => 35000, 'min' => 10, 'rak' => 'H-02', 'resep' => false, 'desc' => 'Pengukur suhu tubuh digital akurat dengan layar LCD.'],
        ];

        $createdObats = [];
        foreach ($daftarObat as $index => $item) {
            $obat = Obat::create([
                'kode' => $item['kode'],
                'nama' => $item['nama'],
                'nama_generik' => $item['generik'],
                'kategori_id' => $kategoriMap[$item['kat']],
                'satuan_id' => $satuanMap[$item['sat']],
                'harga_beli' => $item['beli'],
                'harga_jual' => $item['jual'],
                'stok_minimum' => $item['min'],
                'lokasi_rak' => $item['rak'],
                'butuh_resep' => $item['resep'],
                'deskripsi' => $item['desc'],
            ]);
            $createdObats[$item['kode']] = $obat;

            // Tentukan kondisi batch sesuai rencana:
            // 1-35: Aman (stok melimpah, expired 1 - 2 tahun ke depan)
            // 36-41: Menipis (stok <= stok_minimum)
            // 42-45: Kosong (stok = 0)
            // 46-48: Hampir Kedaluwarsa (expired dalam 20-50 hari)
            // 49-50: Sudah Kedaluwarsa (expired 15-45 hari yang lalu)
            $suplierIndex = $index % count($suplierList);
            $suplier = $suplierList[$suplierIndex];

            if ($index < 35) {
                // AMAN: 2 batch berbeda
                $b1 = BatchObat::create([
                    'obat_id' => $obat->id,
                    'suplier_id' => $suplier->id,
                    'no_batch' => 'BATCH-'.str_pad($index * 2 + 1, 4, '0', STR_PAD_LEFT),
                    'tanggal_kedaluwarsa' => $today->copy()->addMonths(12 + ($index % 12)),
                    'jumlah_masuk' => 60,
                    'jumlah_saat_ini' => 45,
                    'harga_beli' => $item['beli'],
                ]);
                $b2 = BatchObat::create([
                    'obat_id' => $obat->id,
                    'suplier_id' => $suplier->id,
                    'no_batch' => 'BATCH-'.str_pad($index * 2 + 2, 4, '0', STR_PAD_LEFT),
                    'tanggal_kedaluwarsa' => $today->copy()->addMonths(18 + ($index % 12)),
                    'jumlah_masuk' => 50,
                    'jumlah_saat_ini' => 50,
                    'harga_beli' => $item['beli'],
                ]);

                // Mutasi stok
                StokMutasi::create([
                    'obat_id' => $obat->id,
                    'batch_obat_id' => $b1->id,
                    'user_id' => $gudang->id,
                    'jenis' => 'masuk',
                    'jumlah' => 60,
                    'saldo_sebelum' => 0,
                    'saldo_sesudah' => 60,
                    'referensi_tipe' => 'PenerimaanBarang',
                    'referensi_id' => 1,
                    'keterangan' => 'Stok awal sistem',
                ]);
                StokMutasi::create([
                    'obat_id' => $obat->id,
                    'batch_obat_id' => $b2->id,
                    'user_id' => $gudang->id,
                    'jenis' => 'masuk',
                    'jumlah' => 50,
                    'saldo_sebelum' => 60,
                    'saldo_sesudah' => 110,
                    'referensi_tipe' => 'PenerimaanBarang',
                    'referensi_id' => 1,
                    'keterangan' => 'Stok awal sistem batch 2',
                ]);
            } elseif ($index < 41) {
                // MENIPIS (stok <= stok_minimum)
                $qty = max(2, (int) round($item['min'] * 0.4));
                $b = BatchObat::create([
                    'obat_id' => $obat->id,
                    'suplier_id' => $suplier->id,
                    'no_batch' => 'BATCH-MIN-'.str_pad($index, 3, '0', STR_PAD_LEFT),
                    'tanggal_kedaluwarsa' => $today->copy()->addMonths(8),
                    'jumlah_masuk' => 30,
                    'jumlah_saat_ini' => $qty,
                    'harga_beli' => $item['beli'],
                ]);
                StokMutasi::create([
                    'obat_id' => $obat->id,
                    'batch_obat_id' => $b->id,
                    'user_id' => $gudang->id,
                    'jenis' => 'masuk',
                    'jumlah' => $qty,
                    'saldo_sebelum' => 0,
                    'saldo_sesudah' => $qty,
                    'referensi_tipe' => 'PenerimaanBarang',
                    'referensi_id' => 1,
                    'keterangan' => 'Stok menipis sisa operasional',
                ]);
            } elseif ($index < 45) {
                // KOSONG (stok = 0)
                $b = BatchObat::create([
                    'obat_id' => $obat->id,
                    'suplier_id' => $suplier->id,
                    'no_batch' => 'BATCH-EMP-'.str_pad($index, 3, '0', STR_PAD_LEFT),
                    'tanggal_kedaluwarsa' => $today->copy()->addMonths(6),
                    'jumlah_masuk' => 20,
                    'jumlah_saat_ini' => 0,
                    'harga_beli' => $item['beli'],
                ]);
                StokMutasi::create([
                    'obat_id' => $obat->id,
                    'batch_obat_id' => $b->id,
                    'user_id' => $gudang->id,
                    'jenis' => 'keluar',
                    'jumlah' => 20,
                    'saldo_sebelum' => 20,
                    'saldo_sesudah' => 0,
                    'referensi_tipe' => 'Penjualan',
                    'referensi_id' => 1,
                    'keterangan' => 'Stok habis terjual',
                ]);
            } elseif ($index < 48) {
                // HAMPIR KEDALUWARSA (expired dalam 25 - 45 hari)
                $days = 25 + ($index - 45) * 10;
                $b = BatchObat::create([
                    'obat_id' => $obat->id,
                    'suplier_id' => $suplier->id,
                    'no_batch' => 'BATCH-NEAR-EXP-'.str_pad($index, 3, '0', STR_PAD_LEFT),
                    'tanggal_kedaluwarsa' => $today->copy()->addDays($days),
                    'jumlah_masuk' => 25,
                    'jumlah_saat_ini' => 18,
                    'harga_beli' => $item['beli'],
                ]);
                StokMutasi::create([
                    'obat_id' => $obat->id,
                    'batch_obat_id' => $b->id,
                    'user_id' => $gudang->id,
                    'jenis' => 'masuk',
                    'jumlah' => 18,
                    'saldo_sebelum' => 0,
                    'saldo_sesudah' => 18,
                    'referensi_tipe' => 'PenerimaanBarang',
                    'referensi_id' => 1,
                    'keterangan' => 'Stok hampir kedaluwarsa',
                ]);
            } else {
                // SUDAH KEDALUWARSA (expired 20-40 hari yang lalu)
                $daysAgo = 20 + ($index - 48) * 15;
                $b = BatchObat::create([
                    'obat_id' => $obat->id,
                    'suplier_id' => $suplier->id,
                    'no_batch' => 'BATCH-EXP-'.str_pad($index, 3, '0', STR_PAD_LEFT),
                    'tanggal_kedaluwarsa' => $today->copy()->subDays($daysAgo),
                    'jumlah_masuk' => 15,
                    'jumlah_saat_ini' => 15,
                    'harga_beli' => $item['beli'],
                ]);
                StokMutasi::create([
                    'obat_id' => $obat->id,
                    'batch_obat_id' => $b->id,
                    'user_id' => $gudang->id,
                    'jenis' => 'masuk',
                    'jumlah' => 15,
                    'saldo_sebelum' => 0,
                    'saldo_sesudah' => 15,
                    'referensi_tipe' => 'PenerimaanBarang',
                    'referensi_id' => 1,
                    'keterangan' => 'Stok kedaluwarsa siap dimusnahkan',
                ]);
            }
        }

        // 9. TRANSAKSI PEMBELIAN & HUTANG (10 Transaksi)
        $pembelianData = [
            ['sup' => 0, 'hari_lalu' => 25, 'kredit' => false, 'tempo' => null, 'lunas' => true],
            ['sup' => 1, 'hari_lalu' => 20, 'kredit' => false, 'tempo' => null, 'lunas' => true],
            ['sup' => 2, 'hari_lalu' => 18, 'kredit' => true, 'tempo' => -2, 'lunas' => false], // Sudah jatuh tempo 2 hari lalu!
            ['sup' => 3, 'hari_lalu' => 15, 'kredit' => true, 'tempo' => 1, 'lunas' => false],  // H-1 jatuh tempo!
            ['sup' => 4, 'hari_lalu' => 12, 'kredit' => true, 'tempo' => 3, 'lunas' => false],  // H-3 jatuh tempo!
            ['sup' => 0, 'hari_lalu' => 10, 'kredit' => true, 'tempo' => 14, 'lunas' => false], // Jatuh tempo 14 hari lagi
            ['sup' => 1, 'hari_lalu' => 7, 'kredit' => false, 'tempo' => null, 'lunas' => true],
            ['sup' => 2, 'hari_lalu' => 5, 'kredit' => true, 'tempo' => 25, 'lunas' => false],
            ['sup' => 3, 'hari_lalu' => 3, 'kredit' => false, 'tempo' => null, 'lunas' => true],
            ['sup' => 4, 'hari_lalu' => 1, 'kredit' => true, 'tempo' => 30, 'lunas' => false],
        ];

        foreach ($pembelianData as $idx => $p) {
            $tglFaktur = $today->copy()->subDays($p['hari_lalu']);
            $tglTempo = $p['tempo'] !== null ? $today->copy()->addDays($p['tempo']) : null;
            $suplierObj = $suplierList[$p['sup']];
            $noFaktur = 'INV-B88-'.date('Ym', strtotime($tglFaktur)).'-'.str_pad($idx + 1, 4, '0', STR_PAD_LEFT);

            // Purchase order link for first 4
            $poId = null;
            if ($idx < 4) {
                $po = PurchaseOrder::create([
                    'no_po' => 'PO-B88-'.date('Ym', strtotime($tglFaktur)).'-'.str_pad($idx + 1, 4, '0', STR_PAD_LEFT),
                    'suplier_id' => $suplierObj->id,
                    'user_id' => $gudang->id,
                    'tanggal_po' => $tglFaktur->copy()->subDays(3),
                    'estimasi_tanggal_terima' => $tglFaktur,
                    'total_perkiraan' => 1250000,
                    'status' => 'diterima',
                    'catatan' => 'PO rutin pengadaan bulanan',
                ]);
                $poId = $po->id;
            }

            $pembelian = Pembelian::create([
                'no_faktur' => $noFaktur,
                'purchase_order_id' => $poId,
                'suplier_id' => $suplierObj->id,
                'user_id' => $gudang->id,
                'tanggal_faktur' => $tglFaktur,
                'tanggal_jatuh_tempo' => $tglTempo,
                'jenis_pembayaran' => $p['kredit'] ? 'kredit' : 'tunai',
                'status_pembayaran' => $p['lunas'] ? 'lunas' : 'belum_lunas',
                'subtotal' => 1500000 + ($idx * 250000),
                'total_akhir' => 1500000 + ($idx * 250000),
                'total_terbayar' => $p['lunas'] ? (1500000 + ($idx * 250000)) : 500000,
                'sisa_hutang' => $p['lunas'] ? 0 : (1000000 + ($idx * 250000)),
                'catatan' => 'Faktur pengadaan obat batch reguler',
            ]);

            // Add detail items
            $sampleObat = $createdObats['OBT-001'];
            PembelianDetail::create([
                'pembelian_id' => $pembelian->id,
                'obat_id' => $sampleObat->id,
                'batch_obat_id' => null,
                'no_batch' => 'BATCH-INV-'.($idx + 1),
                'tanggal_kedaluwarsa' => $today->copy()->addMonths(18),
                'jumlah' => 100,
                'harga_beli' => $sampleObat->harga_beli,
                'subtotal' => 100 * $sampleObat->harga_beli,
            ]);

            // Add payment if partially paid
            if (! $p['lunas'] && $pembelian->total_terbayar > 0) {
                PembayaranHutang::create([
                    'no_pembayaran' => 'PAY-B88-'.date('Ym', strtotime($tglFaktur)).'-'.str_pad($idx + 1, 4, '0', STR_PAD_LEFT),
                    'pembelian_id' => $pembelian->id,
                    'user_id' => $owner->id,
                    'tanggal_bayar' => $tglFaktur->copy()->addDays(2),
                    'jumlah_bayar' => 500000,
                    'metode_bayar' => 'transfer',
                    'catatan' => 'Uang muka pengadaan obat',
                ]);
            }
        }

        // 10. SHIFT KASIR (1 Selesai kemarin, 1 Buka hari ini)
        ShiftKasir::create([
            'user_id' => $kasir->id,
            'waktu_buka' => $today->copy()->subDay()->setTime(8, 0),
            'waktu_tutup' => $today->copy()->subDay()->setTime(21, 0),
            'modal_awal' => 300000,
            'total_penjualan_tunai' => 1450000,
            'total_penjualan_non_tunai' => 850000,
            'uang_fisik_akhir' => 1750000,
            'selisih' => 0,
            'status' => 'tutup',
            'catatan' => 'Shift lancar, kas pas',
        ]);

        $activeShift = ShiftKasir::create([
            'user_id' => $kasir->id,
            'waktu_buka' => $today->copy()->setTime(8, 0),
            'waktu_tutup' => null,
            'modal_awal' => 300000,
            'total_penjualan_tunai' => 650000,
            'total_penjualan_non_tunai' => 450000,
            'uang_fisik_akhir' => null,
            'selisih' => null,
            'status' => 'buka',
            'catatan' => 'Shift pagi kasir Budi',
        ]);

        // 11. RESEP CONTOH
        $resep1 = Resep::create([
            'no_resep' => 'RSP-20260928-001',
            'dokter_id' => $dokterList[0]->id,
            'nama_dokter' => $dokterList[0]->nama,
            'nama_pasien' => 'Ny. Ratna Sari (52 th)',
            'tanggal_resep' => $today->copy()->subDay(),
            'catatan' => 'R/ Amlodipine 10mg No. XXX S 1 dd 1 pagi',
        ]);

        $resep2 = Resep::create([
            'no_resep' => 'RSP-20260929-002',
            'dokter_id' => $dokterList[1]->id,
            'nama_dokter' => $dokterList[1]->nama,
            'nama_pasien' => 'An. Rizky Pratama (5 th)',
            'tanggal_resep' => $today,
            'catatan' => 'R/ Sanmol Sirup No. I S 3 dd cth 1 prn demam',
        ]);

        // 12. 30 TRANSAKSI PENJUALAN
        $metodeList = ['tunai', 'tunai', 'transfer', 'qris'];
        $obatPool = array_values($createdObats);

        for ($i = 1; $i <= 30; $i++) {
            $hariLalu = (int) floor((30 - $i) / 3); // tersebar dalam 10 hari terakhir
            $trxDate = $today->copy()->subDays($hariLalu)->setTime(9 + ($i % 11), rand(5, 55));
            $isResep = ($i % 5 === 0);
            $custObj = ($i % 3 === 0) ? $pelangganList[($i % count($pelangganList))] : null;
            $metode = $metodeList[$i % 4];

            $noTrx = 'TRX-'.date('Ymd', strtotime($trxDate)).'-'.str_pad($i, 4, '0', STR_PAD_LEFT);

            // Pilih 1 - 3 obat secara acak
            $itemCount = rand(1, 3);
            $subtotal = 0;
            $trxDetails = [];

            for ($j = 0; $j < $itemCount; $j++) {
                $selObat = $obatPool[($i * 3 + $j) % 30]; // pilih dari 30 obat pertama yang aman
                $qty = rand(1, 3);
                $batch = $selObat->availableBatches()->first();
                $itemSubtotal = $qty * $selObat->harga_jual;
                $subtotal += $itemSubtotal;

                $trxDetails[] = [
                    'obat' => $selObat,
                    'batch' => $batch,
                    'qty' => $qty,
                    'subtotal' => $itemSubtotal,
                ];
            }

            $diskon = ($i % 7 === 0) ? 5000 : 0;
            $totalAkhir = max(0, $subtotal - $diskon);
            $jumlahBayar = ($metode === 'tunai') ? (ceil($totalAkhir / 10000) * 10000) : $totalAkhir;
            $kembalian = max(0, $jumlahBayar - $totalAkhir);

            // Void 2 transaksi (no 12 & no 22)
            $isVoid = in_array($i, [12, 22], true);

            $penjualan = Penjualan::create([
                'no_transaksi' => $noTrx,
                'user_id' => $kasir->id,
                'shift_kasir_id' => $activeShift->id,
                'pelanggan_id' => $custObj?->id,
                'resep_id' => $isResep ? ($i % 2 === 0 ? $resep1->id : $resep2->id) : null,
                'jenis_transaksi' => $isResep ? 'resep' : 'biasa',
                'subtotal' => $subtotal,
                'diskon_persen' => 0,
                'diskon_nominal' => $diskon,
                'total_akhir' => $totalAkhir,
                'metode_pembayaran' => $metode,
                'jumlah_bayar' => $jumlahBayar,
                'kembalian' => $kembalian,
                'status' => $isVoid ? 'void' : 'selesai',
                'void_alasan' => $isVoid ? 'Pelanggan salah bawa uang / kartu rusak' : null,
                'void_user_id' => $isVoid ? $apoteker->id : null,
                'void_at' => $isVoid ? $trxDate->copy()->addMinutes(15) : null,
                'catatan' => $isResep ? 'Dilayani sesuai resep dokter' : null,
                'created_at' => $trxDate,
                'updated_at' => $trxDate,
            ]);

            foreach ($trxDetails as $det) {
                PenjualanDetail::create([
                    'penjualan_id' => $penjualan->id,
                    'obat_id' => $det['obat']->id,
                    'batch_obat_id' => $det['batch']?->id,
                    'no_batch' => $det['batch']?->no_batch ?? 'BATCH-001',
                    'harga_beli' => $det['obat']->harga_beli,
                    'harga_jual' => $det['obat']->harga_jual,
                    'jumlah' => $det['qty'],
                    'subtotal' => $det['subtotal'],
                    'created_at' => $trxDate,
                    'updated_at' => $trxDate,
                ]);

                // Kurangi stok batch jika transaksi selesai
                if (! $isVoid && $det['batch']) {
                    $det['batch']->decrement('jumlah_saat_ini', $det['qty']);

                    StokMutasi::create([
                        'obat_id' => $det['obat']->id,
                        'batch_obat_id' => $det['batch']->id,
                        'user_id' => $kasir->id,
                        'jenis' => 'keluar',
                        'jumlah' => $det['qty'],
                        'saldo_sebelum' => $det['batch']->jumlah_saat_ini + $det['qty'],
                        'saldo_sesudah' => $det['batch']->jumlah_saat_ini,
                        'referensi_tipe' => 'Penjualan',
                        'referensi_id' => $penjualan->id,
                        'keterangan' => 'Penjualan '.$penjualan->no_transaksi,
                        'created_at' => $trxDate,
                        'updated_at' => $trxDate,
                    ]);
                }
            }

            // 1 Retur penjualan contoh pada transaksi ke-18
            if ($i === 18 && ! $isVoid) {
                $retur = ReturPenjualan::create([
                    'no_retur' => 'RET-JUAL-20260927-001',
                    'penjualan_id' => $penjualan->id,
                    'user_id' => $kasir->id,
                    'total_pengembalian' => 13000,
                    'alasan' => 'Kemasan segel obat terbuka saat dibawa pulang pelanggan',
                    'created_at' => $trxDate->copy()->addHours(2),
                    'updated_at' => $trxDate->copy()->addHours(2),
                ]);

                $firstDet = $trxDetails[0];
                ReturPenjualanDetail::create([
                    'retur_penjualan_id' => $retur->id,
                    'obat_id' => $firstDet['obat']->id,
                    'batch_obat_id' => $firstDet['batch']?->id,
                    'jumlah' => 1,
                    'harga_satuan' => $firstDet['obat']->harga_jual,
                    'subtotal' => $firstDet['obat']->harga_jual,
                    'kembalikan_ke_stok' => false,
                    'kondisi' => 'rusak_segel',
                ]);
            }
        }

        // 13. NOTIFIKASI IN-APP AWAL
        Notifikasi::create([
            'role_target' => 'owner,apoteker,admin_gudang',
            'judul' => '⚠️ Stok Menipis Terdeteksi',
            'pesan' => 'Obat Bioplacenton Gel 15g tersisa 5 tube (Minimum: 12 tube). Segera buat Purchase Order.',
            'tipe' => 'stok_menipis',
            'link' => '/stok',
            'created_at' => $today->copy()->subHours(2),
        ]);

        Notifikasi::create([
            'role_target' => 'owner,apoteker',
            'judul' => '⏰ Hutang Suplier Jatuh Tempo H-1',
            'pesan' => 'Faktur INV-B88-202609-0004 ke PT. Kalbe Farma Tbk jatuh tempo besok!',
            'tipe' => 'hutang',
            'link' => '/pembelian/hutang',
            'created_at' => $today->copy()->subHours(1),
        ]);

        Notifikasi::create([
            'role_target' => 'owner,apoteker',
            'judul' => '⛔ Transaksi Dibatalkan (Void)',
            'pesan' => 'Transaksi TRX-20260925-0012 telah dibatalkan oleh kasir dengan otorisasi Apoteker.',
            'tipe' => 'void',
            'link' => '/penjualan',
            'created_at' => $today->copy()->subDays(2),
        ]);
    }
}
