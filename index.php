<?php
session_start(); // Memulai session

// Koneksi ke database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "perpus_vsga";

$conn = new mysqli($servername, $username, $password, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch counts from the database
$query_anggota = "SELECT COUNT(*) AS total_anggota FROM anggota";
$query_buku = "SELECT COUNT(*) AS total_buku FROM buku";
$query_transaksi = "SELECT COUNT(*) AS total_transaksi FROM transaksi";

$result_anggota = $conn->query($query_anggota);
$result_buku = $conn->query($query_buku);
$result_transaksi = $conn->query($query_transaksi);

$total_anggota = $result_anggota->fetch_assoc()['total_anggota'];
$total_buku = $result_buku->fetch_assoc()['total_buku'];
$total_transaksi = $result_transaksi->fetch_assoc()['total_transaksi'];

$conn->close(); // Menutup koneksi
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Dashboard - SB Admin</title>
    <?php include 'layouts/header.php'; ?>
</head>

<body class="sb-nav-fixed">
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <!-- Navbar Brand-->
        <a class="navbar-brand ps-3" href="index.html">Start Bootstrap</a>
        <!-- Sidebar Toggle-->
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"><i class="fas fa-bars"></i></button>
        <!-- Navbar Search-->
        <form class="d-none d-md-inline-block form-inline ms-auto me-0 me-md-3 my-2 my-md-0">
            <!-- <div class="input-group">
                <input class="form-control" type="text" placeholder="Search for..." aria-label="Search for..." aria-describedby="btnNavbarSearch" />
                <button class="btn btn-primary" id="btnNavbarSearch" type="button"><i class="fas fa-search"></i></button>
            </div> -->
        </form>
        <!-- Navbar-->
        <?php include 'layouts/navbar.php'; ?>
    </nav>
    <div id="layoutSidenav">
        <?php include 'layouts/sidebar.php'; ?>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">

                    <div class="container-fluid">
                        <h1>Dashboard</h1>
                        <p>Selamat datang di sistem manajemen perpustakaan.</p>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card text-white bg-primary mb-3">
                                    <div class="card-header">Total Anggota</div>
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo $total_anggota; ?></h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card text-white bg-success mb-3">
                                    <div class="card-header">Total Buku</div>
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo $total_buku; ?></h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card text-white bg-danger mb-3">
                                    <div class="card-header">Total Transaksi</div>
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo $total_transaksi; ?></h5>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <canvas id="dataChart"></canvas> <!-- Chart Container -->

                    </div>

                    <!-- Modal Tambah Buku -->
                    <div class="modal fade" id="tambahBukuModal" tabindex="-1" role="dialog" aria-labelledby="tambahBukuLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="tambahBukuLabel">Tambah Buku</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <form id="tambahBukuForm">
                                        <input type="hidden" name="action" value="add_buku">
                                        <div class="form-group">
                                            <label>ID Buku</label>
                                            <input type="text" class="form-control" name="id_buku" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Judul Buku</label>
                                            <input type="text" class="form-control" name="judul_buku" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Penerbit</label>
                                            <input type="text" class="form-control" name="penerbit" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Tahun Terbit</label>
                                            <input type="number" class="form-control" name="tahun_terbit" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Kategori</label>
                                            <input type="text" class="form-control" name="kategori" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Jumlah Halaman</label>
                                            <input type="number" class="form-control" name="jumlah_halaman" required>
                                        </div>
                                        <div class="form-group">
                                            <label>ISBN</label>
                                            <input type="text" class="form-control" name="isbn" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Lokasi Rak</label>
                                            <input type="text" class="form-control" name="lokasi_rak" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Jumlah Buku</label>
                                            <input type="number" class="form-control" name="jumlah_buku" required>
                                        </div>
                                        <button type="button" class="btn btn-primary" onclick="tambahBuku()">Simpan</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </main>
            <footer class="py-4 bg-light mt-auto">
                <?php include 'layouts/footer.php'; ?>
            </footer>
        </div>
    </div>
    <?php include 'layouts/script.php'; ?>
    <script>
        // Initialize Chart.js
        var ctx = document.getElementById('dataChart').getContext('2d');
        var dataChart = new Chart(ctx, {
            type: 'bar', // Type of chart: bar, pie, line, etc.
            data: {
                labels: ['Anggota', 'Buku', 'Transaksi'],
                datasets: [{
                    label: 'Jumlah Data',
                    data: [<?php echo $total_anggota; ?>, <?php echo $total_buku; ?>, <?php echo $total_transaksi; ?>],
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(255, 99, 132, 0.2)'
                    ],
                    borderColor: [
                        'rgba(54, 162, 235, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(255, 99, 132, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>

</html>