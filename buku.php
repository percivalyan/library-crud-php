<?php
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

// Proses Tambah Buku
if (isset($_POST['action']) && $_POST['action'] === 'add_buku') {
    $id_buku = $_POST['id_buku'];
    $judul_buku = $_POST['judul_buku'];
    $penerbit = $_POST['penerbit'];
    $tahun_terbit = $_POST['tahun_terbit'];
    $kategori = $_POST['kategori'];
    $jumlah_halaman = $_POST['jumlah_halaman'];
    $isbn = $_POST['isbn'];
    $lokasi_rak = $_POST['lokasi_rak'];
    $jumlah_buku = $_POST['jumlah_buku'];

    // Tentukan status berdasarkan jumlah buku
    $status = ($jumlah_buku > 0) ? 'Tersedia' : 'Tidak Tersedia';

    // Gunakan prepared statement untuk menghindari SQL Injection
    $stmt = $conn->prepare("INSERT INTO buku (id_buku, judul_buku, penerbit, tahun_terbit, kategori, jumlah_halaman, isbn, lokasi_rak, jumlah_buku, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssssss", $id_buku, $judul_buku, $penerbit, $tahun_terbit, $kategori, $jumlah_halaman, $isbn, $lokasi_rak, $jumlah_buku, $status);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Buku baru berhasil ditambahkan.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $stmt->error]);
    }
    $stmt->close();
    exit;
}

// Proses Hapus Buku
if (isset($_POST['action']) && $_POST['action'] === 'delete_buku') {
    $id = $_POST['id'];

    // Gunakan prepared statement untuk menghindari SQL Injection
    $stmt = $conn->prepare("DELETE FROM buku WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Buku berhasil dihapus.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $stmt->error]);
    }
    $stmt->close();
    exit;
}

// Proses Edit Buku
if (isset($_POST['action']) && $_POST['action'] === 'edit_buku') {
    $id = $_POST['id'];
    $id_buku = $_POST['id_buku'];
    $judul_buku = $_POST['judul_buku'];
    $penerbit = $_POST['penerbit'];
    $tahun_terbit = $_POST['tahun_terbit'];
    $kategori = $_POST['kategori'];
    $jumlah_halaman = $_POST['jumlah_halaman'];
    $isbn = $_POST['isbn'];
    $lokasi_rak = $_POST['lokasi_rak'];
    $jumlah_buku = $_POST['jumlah_buku'];

    // Tentukan status berdasarkan jumlah buku
    $status = ($jumlah_buku > 0) ? 'Tersedia' : 'Tidak Tersedia';

    // Gunakan prepared statement untuk update data
    $stmt = $conn->prepare("UPDATE buku SET id_buku=?, judul_buku=?, penerbit=?, tahun_terbit=?, kategori=?, jumlah_halaman=?, isbn=?, lokasi_rak=?, jumlah_buku=?, status=? WHERE id=?");
    $stmt->bind_param("ssssssssssi", $id_buku, $judul_buku, $penerbit, $tahun_terbit, $kategori, $jumlah_halaman, $isbn, $lokasi_rak, $jumlah_buku, $status, $id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Buku berhasil diperbarui.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $stmt->error]);
    }
    $stmt->close();
    exit;
}

// Mendapatkan data buku untuk ditampilkan
$result = $conn->query("SELECT * FROM buku");

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
        <a class="navbar-brand ps-3" href="index.html" style="color: white; display: flex; align-items: center; font-size: 25px;">
            <!-- Logo SVG -->
            <img src="assets/img/book-of-black-cover-closed-svgrepo-com.svg" alt="Book Icon" width="35" height="35" style="margin-right: 10px;">
            Ruang Baca
        </a>
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
                        <h1 class="mt-4">Kelola Buku</h1>
                        <p>Halaman untuk mengelola data buku.</p>

                        <!-- Button trigger modal -->
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahBukuModal">
                            Tambah Buku
                        </button>

                        <!-- Tabel Daftar Buku -->
                        <h2 class="mt-5">Daftar Buku</h2>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>ID Buku</th>
                                        <th>Judul Buku</th>
                                        <th>Penerbit</th>
                                        <th>Tahun Terbit</th>
                                        <th>Kategori</th>
                                        <th>Jumlah Halaman</th>
                                        <th>ISBN</th>
                                        <th>Lokasi Rak</th>
                                        <th>Jumlah Buku</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $row['id']; ?></td>
                                            <td><?php echo $row['id_buku']; ?></td>
                                            <td><?php echo $row['judul_buku']; ?></td>
                                            <td><?php echo $row['penerbit']; ?></td>
                                            <td><?php echo $row['tahun_terbit']; ?></td>
                                            <td><?php echo $row['kategori']; ?></td>
                                            <td><?php echo $row['jumlah_halaman']; ?></td>
                                            <td><?php echo $row['isbn']; ?></td>
                                            <td><?php echo $row['lokasi_rak']; ?></td>
                                            <td><?php echo $row['jumlah_buku']; ?></td>
                                            <td><?php echo $row['status']; ?></td>
                                            <td>
                                                <!-- Tombol Edit -->
                                                <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $row['id']; ?>">Edit</button>
                                                <!-- Tombol Hapus -->
                                                <button class="btn btn-danger btn-sm" onclick="deleteBuku(<?php echo $row['id']; ?>)">Delete</button>

                                                <!-- Modal Edit -->
                                                <div class="modal fade" id="editModal<?php echo $row['id']; ?>" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="editModalLabel">Edit Buku</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <form id="editBukuForm<?php echo $row['id']; ?>">
                                                                    <input type="hidden" name="action" value="edit_buku">
                                                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                                                    <div class="mb-3">
                                                                        <label>ID Buku</label>
                                                                        <input type="text" class="form-control" name="id_buku" value="<?php echo $row['id_buku']; ?>" required>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label>Judul Buku</label>
                                                                        <input type="text" class="form-control" name="judul_buku" value="<?php echo $row['judul_buku']; ?>" required>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label>Penerbit</label>
                                                                        <input type="text" class="form-control" name="penerbit" value="<?php echo $row['penerbit']; ?>" required>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label>Tahun Terbit</label>
                                                                        <input type="number" class="form-control" name="tahun_terbit" value="<?php echo $row['tahun_terbit']; ?>" required>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label>Kategori</label>
                                                                        <input type="text" class="form-control" name="kategori" value="<?php echo $row['kategori']; ?>" required>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label>Jumlah Halaman</label>
                                                                        <input type="number" class="form-control" name="jumlah_halaman" value="<?php echo $row['jumlah_halaman']; ?>" required>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label>ISBN</label>
                                                                        <input type="text" class="form-control" name="isbn" value="<?php echo $row['isbn']; ?>" required>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label>Lokasi Rak</label>
                                                                        <input type="text" class="form-control" name="lokasi_rak" value="<?php echo $row['lokasi_rak']; ?>" required>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label>Jumlah Buku</label>
                                                                        <input type="number" class="form-control" name="jumlah_buku" value="<?php echo $row['jumlah_buku']; ?>" required>
                                                                    </div>
                                                                    <button type="button" class="btn btn-primary" onclick="editBuku(<?php echo $row['id']; ?>)">Simpan</button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Modal Tambah Buku -->
                    <div class="modal fade" id="tambahBukuModal" tabindex="-1" aria-labelledby="tambahBukuLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="tambahBukuLabel">Tambah Buku</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form id="tambahBukuForm">
                                        <input type="hidden" name="action" value="add_buku">
                                        <div class="mb-3">
                                            <label>ID Buku</label>
                                            <input type="text" class="form-control" name="id_buku" required>
                                        </div>
                                        <div class="mb-3">
                                            <label>Judul Buku</label>
                                            <input type="text" class="form-control" name="judul_buku" required>
                                        </div>
                                        <div class="mb-3">
                                            <label>Penerbit</label>
                                            <input type="text" class="form-control" name="penerbit" required>
                                        </div>
                                        <div class="mb-3">
                                            <label>Tahun Terbit</label>
                                            <input type="number" class="form-control" name="tahun_terbit" required>
                                        </div>
                                        <div class="mb-3">
                                            <label>Kategori</label>
                                            <input type="text" class="form-control" name="kategori" required>
                                        </div>
                                        <div class="mb-3">
                                            <label>Jumlah Halaman</label>
                                            <input type="number" class="form-control" name="jumlah_halaman" required>
                                        </div>
                                        <div class="mb-3">
                                            <label>ISBN</label>
                                            <input type="text" class="form-control" name="isbn" required>
                                        </div>
                                        <div class="mb-3">
                                            <label>Lokasi Rak</label>
                                            <input type="text" class="form-control" name="lokasi_rak" required>
                                        </div>
                                        <div class="mb-3">
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
        function tambahBuku() {
            var form = $('#tambahBukuForm');
            $.ajax({
                url: 'buku.php',
                type: 'POST',
                data: form.serialize(),
                dataType: 'json',
                success: function(response) {
                    Swal.fire({
                        icon: response.status,
                        title: response.message
                    }).then(() => {
                        if (response.status === 'success') {
                            location.reload();
                        }
                    });
                }
            });
        }

        function editBuku(id) {
            var form = $('#editBukuForm' + id);
            $.ajax({
                url: 'buku.php',
                type: 'POST',
                data: form.serialize(),
                dataType: 'json',
                success: function(response) {
                    Swal.fire({
                        icon: response.status,
                        title: response.message
                    }).then(() => {
                        if (response.status === 'success') {
                            location.reload();
                        }
                    });
                }
            });
        }

        function deleteBuku(id) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: 'Data buku ini akan dihapus!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, hapus!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'buku.php',
                        type: 'POST',
                        data: {
                            action: 'delete_buku',
                            id: id
                        },
                        dataType: 'json',
                        success: function(response) {
                            Swal.fire({
                                icon: response.status,
                                title: response.message
                            }).then(() => {
                                if (response.status === 'success') {
                                    location.reload();
                                }
                            });
                        }
                    });
                }
            });
        }

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