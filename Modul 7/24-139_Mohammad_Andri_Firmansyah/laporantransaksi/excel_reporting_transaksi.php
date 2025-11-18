<?php
require_once '../includes/hubung.php';

if (!isset($_GET['export'])) {
    header("Location: reportingtransaksi.php");
    exit;
}

$dari   = $_GET['dari']   ?? '2025-10-30';
$sampai = $_GET['sampai'] ?? '2025-11-03';

$query = "
    SELECT waktu_transaksi, SUM(total) AS total_harian
    FROM transaksi
    WHERE waktu_transaksi BETWEEN '$dari' AND '$sampai'
    GROUP BY waktu_transaksi
    ORDER BY waktu_transaksi ASC
";

$hasil = mysqli_query($conn, $query);

$labels = [];
$total_harian = [];
$data = [];

while ($row = mysqli_fetch_assoc($hasil)) {
    $labels[] = $row['waktu_transaksi'];
    $total_harian[] = (int)$row['total_harian'];
    $data[] = $row;
}

$queryPelanggan = "
    SELECT COUNT(DISTINCT pelanggan_id) AS jml_pelanggan
    FROM transaksi
    WHERE waktu_transaksi BETWEEN '$dari' AND '$sampai'
";
$pelanggan = mysqli_fetch_assoc(mysqli_query($conn, $queryPelanggan));

$queryPendapatan = "
    SELECT SUM(total) AS total_pendapatan
    FROM transaksi
    WHERE waktu_transaksi BETWEEN '$dari' AND '$sampai'
";
$pendapatan = mysqli_fetch_assoc(mysqli_query($conn, $queryPendapatan));

header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=laporan_transaksi.xls");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Laporan</title>
</head>
<body>

    <div class="judul">
        <?= "<h2>Laporan Transaksi dari tanggal $dari sampai $sampai</h2>" ?>
    </div>

    <div>
        <table>
            <tr>
                <th>No</th>
                <th>Total</th>
                <th>Tanggal</th>
            </tr>

            <?php 
            $no = 1;
            foreach($data as $row): 
            ?>
                <tr>
                    <td><?= $no++; ?></td>
                    <td><?= number_format($row['total_harian']); ?></td>
                    <td><?= $row['waktu_transaksi']; ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <br>

    <div>
        <table>
            <tr>
                <th>Jumlah Pelanggan</th>
                <th>Total Pendapatan</th>
            </tr>
            <tr>
                <td><?= $pelanggan['jml_pelanggan']; ?></td>
                <td><?= number_format($pendapatan['total_pendapatan']); ?></td>
            </tr>
        </table>
    </div>
</body>
</html>
