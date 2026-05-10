<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'hrd') {
    header('Location: ../index.php');
    exit();
}
$user = $_SESSION['user'];
$nama = $user['nama'];
$initials = implode('', array_map(fn($w) => strtoupper($w[0]), array_slice(explode(' ', $nama), 0, 2)));
$active_menu = 'karyawan';

// Mock Data Karyawan Full
$karyawan_list = [
    ['id' => 101, 'nama' => 'Andi Wijaya', 'divisi' => 'IT', 'tanggal' => '10 Mei 2026', 'tingkat' => 'Tinggi'],
    ['id' => 102, 'nama' => 'Maria Ulfa', 'divisi' => 'Marketing', 'tanggal' => '09 Mei 2026', 'tingkat' => 'Sedang'],
    ['id' => 103, 'nama' => 'Bambang', 'divisi' => 'Finance', 'tanggal' => '08 Mei 2026', 'tingkat' => 'Rendah'],
    ['id' => 104, 'nama' => 'Citra', 'divisi' => 'HR', 'tanggal' => '07 Mei 2026', 'tingkat' => 'Sedang'],
    ['id' => 105, 'nama' => 'Dedi', 'divisi' => 'IT', 'tanggal' => '06 Mei 2026', 'tingkat' => 'Tinggi'],
    ['id' => 106, 'nama' => 'Eka', 'divisi' => 'Operasional', 'tanggal' => '05 Mei 2026', 'tingkat' => 'Rendah'],
    ['id' => 107, 'nama' => 'Farhan', 'divisi' => 'Marketing', 'tanggal' => '04 Mei 2026', 'tingkat' => 'Tinggi'],
    ['id' => 108, 'nama' => 'Gita', 'divisi' => 'Finance', 'tanggal' => '03 Mei 2026', 'tingkat' => 'Sedang'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Data Karyawan – BurnoutXpert</title>
    <?php include '../includes/head.php'; ?>

</head>
<body>

<?php include '../includes/sidebar_hrd.php'; ?>

<div class="main-wrapper">
    <?php 
        $page_title = "Data Karyawan";
        include '../includes/topbar.php'; 
    ?>

    <main class="page-content">
        <div class="content-card">
            <div class="card-header">
                <h2 class="card-title">Daftar Seluruh Karyawan</h2>
                <div class="search-box" style="display:flex; gap:0.75rem; background:var(--color-gray-50); padding:0.5rem 1rem; border-radius:12px; border:1.5px solid var(--color-gray-200); width:100%; max-width:300px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--color-gray-400);"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    <input type="text" id="karyawanSearch" class="search-input" style="background:none; border:none; font-family:inherit; font-size:0.9rem; width:100%; outline:none;" placeholder="Cari nama atau divisi...">
                </div>
            </div>

            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Karyawan</th>
                            <th>Divisi</th>
                            <th>Deteksi Terakhir</th>
                            <th>Tingkat Burnout</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($karyawan_list as $k): ?>
                        <tr>
                            <td data-label="Karyawan">
                                <div class="user-info" style="display:flex; align-items:center; gap:0.75rem;">
                                    <div class="user-avatar" style="width:36px; height:36px; border-radius:50%; background:var(--color-primary-50); color:var(--color-primary); font-size:0.8rem; font-weight:700; display:flex; align-items:center; justify-content:center;"><?= substr($k['nama'], 0, 1) ?></div>
                                    <span style="font-weight: 600;"><?= $k['nama'] ?></span>
                                </div>
                            </td>
                            <td data-label="Divisi"><?= $k['divisi'] ?></td>
                            <td data-label="Deteksi Terakhir" style="color: var(--color-gray-500);"><?= $k['tanggal'] ?></td>
                            <td data-label="Tingkat Burnout">
                                <span class="badge badge-<?= strtolower($k['tingkat']) ?>">
                                    <?= $k['tingkat'] ?>
                                </span>
                            </td>
                            <td data-label="Aksi"><a href="detail_karyawan.php?id=<?= $k['id'] ?>" class="btn-detail">Lihat Detail</a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('karyawanSearch');
    const tableRows = document.querySelectorAll('.data-table tbody tr');

    if(searchInput) {
        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();

            tableRows.forEach(row => {
                const name = row.querySelector('td[data-label="Karyawan"]').textContent.toLowerCase();
                const division = row.querySelector('td[data-label="Divisi"]').textContent.toLowerCase();

                if(name.includes(searchTerm) || division.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
});
</script>
</body>
</html>
