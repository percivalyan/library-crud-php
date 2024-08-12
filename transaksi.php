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

if (isset($_POST['add_transaksi'])) {
    $id_anggota = $_POST['id_anggota'];
    $id_buku = $_POST['id_buku'];
    $tanggal_pinjam = $_POST['tanggal_pinjam'];
    $lama_pinjam = $_POST['lama_pinjam'];

    // Dapatkan jumlah buku yang tersedia
    $cek_buku_sql = "SELECT jumlah_buku FROM buku WHERE id = $id_buku";
    $buku_result = $conn->query($cek_buku_sql);
    $buku_row = $buku_result->fetch_assoc();

    // Jika jumlah buku lebih dari 0, lanjutkan proses peminjaman
    if ($buku_row['jumlah_buku'] > 0) {
        // Kurangi jumlah buku di tabel buku
        $update_buku_sql = "UPDATE buku SET jumlah_buku = jumlah_buku - 1 WHERE id = $id_buku";
        $conn->query($update_buku_sql);

        $sql = "INSERT INTO transaksi (id_anggota, id_buku, tanggal_pinjam, lama_pinjam)
                VALUES ('$id_anggota', '$id_buku', '$tanggal_pinjam', '$lama_pinjam')";

        if ($conn->query($sql) === TRUE) {
            $_SESSION['transaksi_added'] = true;
        } else {
            echo "<div class='alert alert-danger'>Error: " . $sql . "<br>" . $conn->error . "</div>";
        }
    } else {
        // Jika jumlah buku 0 atau kurang, tandai bahwa buku tidak tersedia
        $_SESSION['book_unavailable'] = true;
    }

    // Redirect setelah aksi selesai
    header("Location: transaksi.php");
    exit();
}

// Proses Pengembalian Buku
if (isset($_GET['return_id'])) {
    $return_id = $_GET['return_id'];
    $tanggal_pengembalian = date('Y-m-d');

    // Update transaksi dengan tanggal pengembalian
    $update_transaksi_sql = "UPDATE transaksi SET tanggal_pengembalian='$tanggal_pengembalian' WHERE id=$return_id";
    $conn->query($update_transaksi_sql);

    // Dapatkan ID buku dari transaksi yang dikembalikan
    $transaksi_result = $conn->query("SELECT id_buku FROM transaksi WHERE id=$return_id");
    $transaksi_row = $transaksi_result->fetch_assoc();
    $id_buku = $transaksi_row['id_buku'];

    // Tambah jumlah buku di tabel buku
    $update_buku_sql = "UPDATE buku SET jumlah_buku = jumlah_buku + 1 WHERE id = $id_buku";
    $conn->query($update_buku_sql);

    $_SESSION['transaksi_returned'] = true;

    // Redirect setelah aksi selesai
    header("Location: transaksi.php");
    exit();
}

// Mendapatkan data transaksi untuk ditampilkan
$result = $conn->query("SELECT t.id, a.nama AS anggota, b.id_buku AS buku, t.tanggal_pinjam, t.lama_pinjam, 
                               t.tanggal_pengembalian, 
                               CASE 
                                   WHEN t.tanggal_pengembalian IS NULL THEN 
                                       IF(DATEDIFF(CURDATE(), t.tanggal_pinjam) > t.lama_pinjam, 'Terlambat', 'Sedang Dipinjam')
                                   ELSE 
                                       'Sudah Dikembalikan'
                               END AS status,
                               CASE 
                                   WHEN t.tanggal_pengembalian IS NULL AND DATEDIFF(CURDATE(), t.tanggal_pinjam) > t.lama_pinjam THEN 
                                       (DATEDIFF(CURDATE(), t.tanggal_pinjam) - t.lama_pinjam) * 1000
                                   ELSE 
                                       0
                               END AS denda,
                               DATE_ADD(t.tanggal_pinjam, INTERVAL t.lama_pinjam DAY) AS estimasi_pengembalian
                        FROM transaksi t 
                        JOIN anggota a ON t.id_anggota = a.id 
                        JOIN buku b ON t.id_buku = b.id");

$anggota_result = $conn->query("SELECT id, nama FROM anggota");
$buku_result = $conn->query("SELECT id, id_buku FROM buku");

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
        <div class="input-group">
                <input id="searchInput" class="form-control" type="text" placeholder="Search for..." aria-label="Search for..." aria-describedby="btnNavbarSearch" />
                <button class="btn btn-primary" id="btnNavbarSearch" type="button"><i class="fas fa-search"></i></button>
            </div>
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
                        <h1>Transaksi Peminjaman dan Pengembalian</h1>
                        <p>Halaman untuk mengelola transaksi peminjaman dan pengembalian.</p>

                        <!-- Formulir Tambah Transaksi -->
                        <h2>Tambah Transaksi</h2>
                        <form method="post" action="">
                            <div class="form-group">
                                <label for="id_anggota">ID Anggota</label>
                                <select class="form-control" id="id_anggota" name="id_anggota" required>
                                    <option value="">Pilih Anggota</option>
                                    <?php while ($row = $anggota_result->fetch_assoc()): ?>
                                        <option value="<?php echo $row['id']; ?>"><?php echo $row['nama']; ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="id_buku">ID Buku</label>
                                <select class="form-control" id="id_buku" name="id_buku" required>
                                    <option value="">Pilih Buku</option>
                                    <?php while ($row = $buku_result->fetch_assoc()): ?>
                                        <option value="<?php echo $row['id']; ?>"><?php echo $row['id_buku']; ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="tanggal_pinjam">Tanggal Pinjam</label>
                                <input type="date" class="form-control" id="tanggal_pinjam" name="tanggal_pinjam" required>
                            </div>
                            <div class="form-group">
                                <label for="lama_pinjam">Lama Pinjam (hari)</label>
                                <input type="number" class="form-control" id="lama_pinjam" name="lama_pinjam" required>
                            </div>
                            <button type="submit" name="add_transaksi" class="btn btn-primary">Tambah Transaksi</button>
                        </form>

                        <!-- Tabel Daftar Transaksi -->
                        <h2 class="mt-5">Daftar Transaksi</h2>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Anggota</th>
                                    <th>Buku</th>
                                    <th>Tanggal Pinjam</th>
                                    <th>Lama Pinjam</th>
                                    <th>Estimasi Pengembalian</th>
                                    <th>Status</th>
                                    <th>Denda</th>
                                    <th>Tanggal Pengembalian</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $row['id']; ?></td>
                                        <td><?php echo $row['anggota']; ?></td>
                                        <td><?php echo $row['buku']; ?></td>
                                        <td><?php echo $row['tanggal_pinjam']; ?></td>
                                        <td><?php echo $row['lama_pinjam']; ?></td>
                                        <td><?php echo $row['estimasi_pengembalian']; ?></td>
                                        <td><?php echo $row['status']; ?></td>
                                        <td><?php echo $row['denda']; ?></td>
                                        <td><?php echo $row['tanggal_pengembalian']; ?></td>
                                        <td>
                                            <?php if ($row['status'] == 'Sedang Dipinjam' || $row['status'] == 'Terlambat'): ?>
                                                <button class="btn btn-success btn-sm return-btn" data-id="<?php echo $row['id']; ?>">Kembalikan</button>
                                            <?php else: ?>
                                                <span class="text-muted">Sudah Dikembalikan</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>

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
        $(document).ready(function() {
            // Pop-up konfirmasi pengembalian buku
            $('.return-btn').click(function() {
                var returnId = $(this).data('id');
                Swal.fire({
                    title: 'Apakah Anda yakin ingin mengembalikan buku ini?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, kembalikan!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'transaksi.php?return_id=' + returnId;
                    }
                });
            });

            // Jika transaksi berhasil ditambahkan
            <?php if (isset($_SESSION['transaksi_added']) && $_SESSION['transaksi_added']): ?>
                Swal.fire({
                    title: 'Sukses!',
                    text: 'Transaksi berhasil ditambahkan.',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    <?php unset($_SESSION['transaksi_added']); ?>
                });
            <?php endif; ?>

            // Jika buku tidak tersedia
            <?php if (isset($_SESSION['book_unavailable']) && $_SESSION['book_unavailable']): ?>
                Swal.fire({
                    title: 'Gagal!',
                    text: 'Buku ini tidak tersedia untuk dipinjam karena jumlahnya 0 atau kurang.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                }).then(() => {
                    <?php unset($_SESSION['book_unavailable']); ?>
                });
            <?php endif; ?>

            // Jika buku berhasil dikembalikan
            <?php if (isset($_SESSION['transaksi_returned']) && $_SESSION['transaksi_returned']): ?>
                Swal.fire({
                    title: 'Sukses!',
                    text: 'Buku berhasil dikembalikan.',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    <?php unset($_SESSION['transaksi_returned']); ?>
                });
            <?php endif; ?>
        });

         // Fungsi untuk mencari
         $(document).ready(function() {
            $('#searchInput').on('keyup', function() {
                var value = $(this).val().toLowerCase();
                $('table tbody tr').filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
                });
            });
        });
    </script>
</body>

</html>