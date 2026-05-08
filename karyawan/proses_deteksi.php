<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}

// Logika perhitungan skor burnout (mock)
// Di sini biasanya ada logika sistem pakar menggunakan Forward Chaining atau Certainty Factor
// Untuk sementara, kita langsung arahkan ke halaman hasil.

header('Location: hasil.php');
exit();
