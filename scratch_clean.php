<?php
$files = [
    'hrd/laporan.php',
    'hrd/notifikasi.php',
    'hrd/detail_karyawan.php',
    'admin/laporan.php',
    'admin/pengaturan.php',
    'admin/log_aktivitas.php',
    'admin/kelola_pengguna.php',
    'admin/admin_knowledge.php',
    'karyawan/notifikasi.php',
    'karyawan/hasil.php',
    'karyawan/bantuan.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $content = preg_replace('/<style>.*?<\/style>/s', '', $content);
        file_put_contents($file, $content);
        echo "Cleaned $file\n";
    }
}
?>
