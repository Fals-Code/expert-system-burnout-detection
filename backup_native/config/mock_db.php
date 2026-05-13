<?php
// ============================================================
//  BurnoutXpert – Knowledge Base Loader (DB + Fallback)
//  Mengambil basis pengetahuan dari database.
// ============================================================

require_once __DIR__ . '/config.php';

function bx_get_knowledge_base(): array {
    $db = getDBConnection();
    
    // Default hardcoded knowledge base (fallback)
    $kb = [
        'gejala' => [],
        'aturan' => []
    ];

    if (!$db) return $kb;

    try {
        // 1. Load Gejala
        $stmt = $db->query("SELECT * FROM gejala ORDER BY kode ASC");
        $kb['gejala'] = $stmt->fetchAll();

        // 2. Load Aturan + Relasi Gejala
        $stmtAturan = $db->query("
            SELECT a.*, d.nama as diagnosa, d.color, d.bg_light, d.deskripsi as `desc` 
            FROM aturan a
            JOIN diagnosa d ON a.diagnosa_id = d.id
        ");
        $aturanRows = $stmtAturan->fetchAll();

        foreach ($aturanRows as $row) {
            // Ambil gejala untuk aturan ini
            $stmtG = $db->prepare("
                SELECT g.kode 
                FROM aturan_gejala ag 
                JOIN gejala g ON ag.gejala_id = g.id 
                WHERE ag.aturan_id = ?
            ");
            $stmtG->execute([$row['id']]);
            $gejalaKodes = $stmtG->fetchAll(PDO::FETCH_COLUMN);

            $kb['aturan'][] = [
                'id'       => $row['kode'],
                'diagnosa' => $row['diagnosa'],
                'gejala'   => $gejalaKodes,
                'cf_pakar' => (float)$row['cf_pakar'],
                'color'    => $row['color'],
                'bg_light' => $row['bg_light'],
                'desc'     => $row['desc']
            ];
        }
    } catch (Exception $e) {
        error_log("Gagal load KB: " . $e->getMessage());
    }

    return $kb;
}

// Untuk kompatibilitas dengan kode lama yang menggunakan include
return bx_get_knowledge_base();
