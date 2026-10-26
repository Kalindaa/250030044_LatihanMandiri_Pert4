<?php
// TUGAS MANDIRI: Rancang class Buku, Anggota, Peminjaman


// CLASS BUKU 
class Buku {
    public $kode;
    public $judul;
    public $penulis;
    private $stok; // private: hanya bisa diubah lewat method

    public function __construct($kode, $judul, $penulis, $stok) {
        $this->kode = $kode;
        $this->judul = $judul;
        $this->penulis = $penulis;
        $this->setStok($stok);
    }

    // Getter
    public function getStok() {
        return $this->stok;
    }

    // Setter dengan validasi
    public function setStok($stok) {
        if ($stok >= 0) {
            $this->stok = $stok;
        } else {
            echo "Stok tidak valid!<br>";
            $this->stok = 0;
        }
    }

    // Return value: true kalau buku masih tersedia
    public function isTersedia() {
        return $this->stok > 0;
    }

    public function kurangiStok() {
        if ($this->isTersedia()) {
            $this->stok--;
            return true;
        }
        return false;
    }

    public function tambahStok() {
        $this->stok++;
    }

    public function tampilkanInfo() {
        echo "Kode: $this->kode<br>";
        echo "Judul: $this->judul<br>";
        echo "Penulis: $this->penulis<br>";
        echo "Stok: $this->stok<br>";
    }
}

// CLASS ANGGOTA 
class Anggota {
    public $idAnggota;
    public $nama;
    public $email;
    private $jumlahPinjam; // jumlah buku yang sedang dipinjam
    private $batasPinjam;  // maksimal buku yang boleh dipinjam

    public function __construct($idAnggota, $nama, $email, $batasPinjam = 3) {
        $this->idAnggota = $idAnggota;
        $this->nama = $nama;
        $this->email = $email;
        $this->batasPinjam = $batasPinjam;
        $this->jumlahPinjam = 0;
    }

    public function getJumlahPinjam() {
        return $this->jumlahPinjam;
    }

    public function getBatasPinjam() {
        return $this->batasPinjam;
    }

    // Return value: true kalau anggota masih boleh meminjam
    public function bolehPinjam() {
        return $this->jumlahPinjam < $this->batasPinjam;
    }

    public function tambahPinjaman() {
        if ($this->bolehPinjam()) {
            $this->jumlahPinjam++;
            return true;
        }
        return false;
    }

    public function kurangiPinjaman() {
        if ($this->jumlahPinjam > 0) {
            $this->jumlahPinjam--;
        }
    }

    public function tampilkanInfo() {
        echo "ID Anggota: $this->idAnggota<br>";
        echo "Nama: $this->nama<br>";
        echo "Email: $this->email<br>";
        echo "Sedang dipinjam: $this->jumlahPinjam dari $this->batasPinjam buku<br>";
    }
}

// CLASS PEMINJAMAN 
class Peminjaman {
    public $idPeminjaman;
    public $anggota; // objek Anggota
    public $buku;    // objek Buku
    public $tanggalPinjam;
    public $tanggalKembali;
    private $status; // "Dipinjam" atau "Dikembalikan"
    private $dendaPerHari = 1000;
    private $lamaPinjam = 7; // hari

    public function __construct($idPeminjaman, Anggota $anggota, Buku $buku, $tanggalPinjam) {
        $this->idPeminjaman = $idPeminjaman;
        $this->anggota = $anggota;
        $this->buku = $buku;
        $this->tanggalPinjam = $tanggalPinjam;
        $this->tanggalKembali = null;
        $this->status = "Belum diproses";
    }

    public function getStatus() {
        return $this->status;
    }

    // Proses peminjaman: cek anggota dan stok dulu
    public function prosesPinjam() {
        if (!$this->anggota->bolehPinjam()) {
            echo "Peminjaman gagal: {$this->anggota->nama} sudah mencapai batas pinjam.<br>";
            return false;
        }
        if (!$this->buku->isTersedia()) {
            echo "Peminjaman gagal: buku '{$this->buku->judul}' sedang kosong.<br>";
            return false;
        }

        $this->buku->kurangiStok();
        $this->anggota->tambahPinjaman();
        $this->status = "Dipinjam";
        echo "{$this->anggota->nama} berhasil meminjam '{$this->buku->judul}'.<br>";
        return true;
    }

    // Return value: besar denda (0 kalau tidak telat)
    public function hitungDenda($tanggalKembali) {
        $pinjam = strtotime($this->tanggalPinjam);
        $kembali = strtotime($tanggalKembali);
        $selisihHari = ($kembali - $pinjam) / 86400;
        $telat = $selisihHari - $this->lamaPinjam;

        if ($telat > 0) {
            return $telat * $this->dendaPerHari;
        }
        return 0;
    }

    public function prosesKembali($tanggalKembali) {
        if ($this->status != "Dipinjam") {
            echo "Buku ini tidak sedang dipinjam.<br>";
            return false;
        }

        $this->tanggalKembali = $tanggalKembali;
        $this->buku->tambahStok();
        $this->anggota->kurangiPinjaman();
        $this->status = "Dikembalikan";

        $denda = $this->hitungDenda($tanggalKembali);
        echo "{$this->anggota->nama} mengembalikan '{$this->buku->judul}'.<br>";
        if ($denda > 0) {
            echo "Terlambat, denda: Rp " . number_format($denda, 0, ',', '.') . "<br>";
        } else {
            echo "Tepat waktu, tidak ada denda.<br>";
        }
        return true;
    }

    public function tampilkanInfo() {
        echo "ID Peminjaman: $this->idPeminjaman<br>";
        echo "Anggota: {$this->anggota->nama}<br>";
        echo "Buku: {$this->buku->judul}<br>";
        echo "Tanggal Pinjam: $this->tanggalPinjam<br>";
        echo "Status: $this->status<br>";
    }
}

// PROGRAM UTAMA 
echo "<h3>Data Buku</h3>";
$buku1 = new Buku("B001", "Pemrograman PHP Dasar", "Andi Wijaya", 2);
$buku2 = new Buku("B002", "Basis Data", "Siti Rahma", 1);
$buku1->tampilkanInfo();
echo "<br>";
$buku2->tampilkanInfo();

echo "<h3>Data Anggota</h3>";
$anggota1 = new Anggota("A001", "Kalyn", "kalyn@mail.com", 2);
$anggota2 = new Anggota("A002", "Budi", "budi@mail.com");
$anggota1->tampilkanInfo();
echo "<br>";
$anggota2->tampilkanInfo();

echo "<h3>Proses Peminjaman</h3>";
$pinjam1 = new Peminjaman("P001", $anggota1, $buku1, "2026-10-01");
$pinjam1->prosesPinjam();

$pinjam2 = new Peminjaman("P002", $anggota2, $buku2, "2026-10-02");
$pinjam2->prosesPinjam();

// Buku 2 stoknya sudah habis, jadi ini akan gagal
$pinjam3 = new Peminjaman("P003", $anggota1, $buku2, "2026-10-03");
$pinjam3->prosesPinjam();

echo "<h3>Info Peminjaman</h3>";
$pinjam1->tampilkanInfo();

echo "<h3>Pengembalian</h3>";
$pinjam1->prosesKembali("2026-10-12"); // telat 4 hari
echo "<br>";
$pinjam2->prosesKembali("2026-10-08"); // tepat waktu

echo "<h3>Kondisi Akhir</h3>";
echo "Stok '{$buku1->judul}': " . $buku1->getStok() . "<br>";
echo "Stok '{$buku2->judul}': " . $buku2->getStok() . "<br>";
echo "Pinjaman {$anggota1->nama}: " . $anggota1->getJumlahPinjam() . "<br>";
?>