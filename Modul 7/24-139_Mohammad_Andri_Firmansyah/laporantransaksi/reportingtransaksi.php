<?php 
require_once '../includes/hubung.php';

$dari   = isset($_GET['dari']) ? $_GET['dari'] : '2025-10-30';
$sampai = isset($_GET['sampai']) ? $_GET['sampai'] : '2025-11-03';

$query = "SELECT waktu_transaksi, SUM(total) AS total_harian FROM transaksi";

if ($dari !== '' && $sampai !== '') {
    $query = "SELECT waktu_transaksi, SUM(total) AS total_harian 
              FROM transaksi 
              WHERE waktu_transaksi BETWEEN '$dari' AND '$sampai'
              GROUP BY waktu_transaksi 
              ORDER BY waktu_transaksi ASC";
} else {
    $query = "SELECT waktu_transaksi, SUM(total) AS total_harian 
              FROM transaksi 
              GROUP BY waktu_transaksi 
              ORDER BY waktu_transaksi ASC";
}

try {
    $hasil = mysqli_query($conn, $query);

    $labels = [];
    $total_harian = [];
    $data = [];

    while ($row = mysqli_fetch_assoc($hasil)) {
        $labels[] = $row['waktu_transaksi'];
        $total_harian[] = (int)$row['total_harian'];
        $data[] = $row;
    }

} catch (Exception $e) {
    echo $e;
}

if ($dari !== '' && $sampai !== '') {
    $queryPelanggan = "
        SELECT COUNT(DISTINCT pelanggan_id) AS jml_pelanggan 
        FROM transaksi 
        WHERE waktu_transaksi BETWEEN '$dari' AND '$sampai'
    ";
} else {
    $queryPelanggan = "
        SELECT COUNT(DISTINCT pelanggan_id) AS jml_pelanggan 
        FROM transaksi
    ";
}

$pelanggan = mysqli_fetch_assoc(mysqli_query($conn, $queryPelanggan));

if ($dari !== '' && $sampai !== '') {
    $queryPendapatan = "
        SELECT SUM(total) AS total_pendapatan 
        FROM transaksi 
        WHERE waktu_transaksi BETWEEN '$dari' AND '$sampai'
    ";
} else {
    $queryPendapatan = "
        SELECT SUM(total) AS total_pendapatan 
        FROM transaksi
    ";
}

$pendapatan = mysqli_fetch_assoc(mysqli_query($conn, $queryPendapatan));


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporting Transaksi</title>
    <link rel="stylesheet" href="../aset/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<div class="utama">
    <div class="judul">
        <?= "<h2>Laporan Transaksi dari tanggal $dari sampai dengan tanggal $sampai</h2>" ?>
    </div>

    <div class="tombol no-print">
        <button onclick="window.print()" class="btn btn-cetak">
            Cetak Halaman
        </button>

        <button name="export" class="btn btn-ekspor" onclick="window.location.href='excel_reporting_transaksi.php?export=1&dari=<?= $dari ?>&sampai=<?= $sampai ?>'">
            Export Excel
        </button>
    </div>


    <div class="form-filter no-print">
        <form method="GET" action="">
            <span class="form-date">
                <input type="date" name="dari" value="<?= $dari ?>">
            </span>
            <span class="form-date">
                <input type="date" name="sampai" value="<?= $sampai ?>">
            </span>
            <span>
                <button class="btn-tampilkan" type="submit">Tampilkan</button>
            </span>
        </form>
    </div>

    <div class="diagram">
        <div style="width: 60%; margin-top:20px;">
            <canvas id="myChart"></canvas>
        </div>
    </div>

    <script>
    const ctx = document.getElementById('myChart');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($labels); ?>,
            datasets: [{
                label: 'Total Transaksi Harian',
                data: <?= json_encode($total_harian); ?>,
                borderWidth: 2,
                borderColor: 'black',
                backgroundColor: 'grey'
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display:true,
                        text: 'Total (Rp)'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Tanggal Transaksi'
                    }
                }
            }
        }
    });
    </script>

    <div style="margin-top: 20px;">
        <table>
            <tr>
                <th>ID</th>
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

</div>

</body>
</html>
