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

// Proses Tambah Anggota
if (isset($_POST['action']) && $_POST['action'] == 'add_anggota') {
    $id_anggota = $_POST['id_anggota'];
    $nama = $_POST['nama'];
    $nomor_telepon = $_POST['nomor_telepon'];
    $email = $_POST['email'];
    $tanggal_pendaftaran = date('Y-m-d'); // Set tanggal pendaftaran ke hari ini
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $tanggal_lahir = $_POST['tanggal_lahir'];

    // Gunakan prepared statement untuk menghindari SQL Injection
    $stmt = $conn->prepare("INSERT INTO anggota (id_anggota, nama, nomor_telepon, email, tanggal_pendaftaran, jenis_kelamin, tanggal_lahir) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $id_anggota, $nama, $nomor_telepon, $email, $tanggal_pendaftaran, $jenis_kelamin, $tanggal_lahir);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Anggota baru berhasil ditambahkan.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $conn->error]);
    }
    $stmt->close();
    exit;
}

// Proses Edit Anggota
if (isset($_POST['action']) && $_POST['action'] == 'edit_anggota') {
    $id = $_POST['id'];
    $id_anggota = $_POST['id_anggota'];
    $nama = $_POST['nama'];
    $nomor_telepon = $_POST['nomor_telepon'];
    $email = $_POST['email'];
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $tanggal_lahir = $_POST['tanggal_lahir'];

    // Gunakan prepared statement untuk update data
    $stmt = $conn->prepare("UPDATE anggota SET id_anggota=?, nama=?, nomor_telepon=?, email=?, jenis_kelamin=?, tanggal_lahir=? WHERE id=?");
    $stmt->bind_param("ssssssi", $id_anggota, $nama, $nomor_telepon, $email, $jenis_kelamin, $tanggal_lahir, $id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Anggota berhasil diperbarui.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $conn->error]);
    }
    $stmt->close();
    exit;
}

// Mendapatkan data anggota berdasarkan ID
if (isset($_POST['action']) && $_POST['action'] == 'get_anggota') {
    $id = $_POST['id'];
    $sql = "SELECT * FROM anggota WHERE id=$id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode(['status' => 'success', 'data' => $row]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Anggota tidak ditemukan.']);
    }
    exit;
}

// Mendapatkan data anggota untuk ditampilkan
$result = $conn->query("SELECT * FROM anggota");

// Proses Hapus Anggota
if (isset($_POST['action']) && $_POST['action'] == 'delete_anggota') {
    $id = $_POST['id'];

    // Gunakan prepared statement untuk menghindari SQL Injection
    $stmt = $conn->prepare("DELETE FROM anggota WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Anggota berhasil dihapus.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $conn->error]);
    }
    $stmt->close();
    exit;
}
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

                        <h1>Kelola Anggota</h1>
                        <p>Halaman untuk mengelola data anggota.</p>

                        <!-- Button to open Add Anggota modal -->
                        <button type="button" class="btn btn-primary mb-3" data-toggle="modal" data-target="#addAnggotaModal">
                            Tambah Anggota
                        </button>

                        <!-- Tabel Daftar Anggota -->
                        <h2>Daftar Anggota</h2>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>ID Anggota</th>
                                    <th>Nama</th>
                                    <th>Nomor Telepon</th>
                                    <th>Email</th>
                                    <th>Tanggal Pendaftaran</th>
                                    <th>Jenis Kelamin</th>
                                    <th>Tanggal Lahir</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $row['id']; ?></td>
                                        <td><?php echo $row['id_anggota']; ?></td>
                                        <td><?php echo $row['nama']; ?></td>
                                        <td><?php echo $row['nomor_telepon']; ?></td>
                                        <td><?php echo $row['email']; ?></td>
                                        <td><?php echo $row['tanggal_pendaftaran']; ?></td>
                                        <td><?php echo $row['jenis_kelamin']; ?></td>
                                        <td><?php echo $row['tanggal_lahir']; ?></td>
                                        <td>
                                            <!-- Button to open Edit Anggota modal -->
                                            <button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editAnggotaModal"
                                                data-id="<?php echo $row['id']; ?>"
                                                data-id_anggota="<?php echo $row['id_anggota']; ?>"
                                                data-nama="<?php echo $row['nama']; ?>"
                                                data-nomor_telepon="<?php echo $row['nomor_telepon']; ?>"
                                                data-email="<?php echo $row['email']; ?>"
                                                data-tanggal_pendaftaran="<?php echo $row['tanggal_pendaftaran']; ?>"
                                                data-jenis_kelamin="<?php echo $row['jenis_kelamin']; ?>"
                                                data-tanggal_lahir="<?php echo $row['tanggal_lahir']; ?>"
                                                onclick="editAnggota(this)">
                                                Edit
                                            </button>

                                            <!-- Button to delete -->

                                            <button type="button" class="btn btn-danger btn-sm" onclick="deleteAnggota(<?php echo $row['id']; ?>)">Hapus</button>

                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>

                        <!-- Add Anggota Modal -->
                        <div class="modal fade" id="addAnggotaModal" tabindex="-1" role="dialog" aria-labelledby="addAnggotaModalLabel" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="addAnggotaModalLabel">Tambah Anggota</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <form id="addAnggotaForm">
                                            <div class="form-group">
                                                <label for="id_anggota">ID Anggota</label>
                                                <input type="text" class="form-control" id="id_anggota" name="id_anggota" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="nama">Nama</label>
                                                <input type="text" class="form-control" id="nama" name="nama" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="nomor_telepon">Nomor Telepon</label>
                                                <input type="text" class="form-control" id="nomor_telepon" name="nomor_telepon" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="email">Email</label>
                                                <input type="email" class="form-control" id="email" name="email" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="jenis_kelamin">Jenis Kelamin</label>
                                                <select class="form-control" id="jenis_kelamin" name="jenis_kelamin" required>
                                                    <option value="Laki-laki">Laki-laki</option>
                                                    <option value="Perempuan">Perempuan</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label for="tanggal_lahir">Tanggal Lahir</label>
                                                <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir" required>
                                            </div>
                                            <button type="submit" class="btn btn-primary">Simpan</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Edit Anggota Modal -->
                        <div class="modal fade" id="editAnggotaModal" tabindex="-1" role="dialog" aria-labelledby="editAnggotaModalLabel" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="editAnggotaModalLabel">Edit Anggota</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <form id="editAnggotaForm">
                                            <input type="hidden" id="edit_id" name="id">
                                            <div class="form-group">
                                                <label for="edit_id_anggota">ID Anggota</label>
                                                <input type="text" class="form-control" id="edit_id_anggota" name="id_anggota" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="edit_nama">Nama</label>
                                                <input type="text" class="form-control" id="edit_nama" name="nama" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="edit_nomor_telepon">Nomor Telepon</label>
                                                <input type="text" class="form-control" id="edit_nomor_telepon" name="nomor_telepon" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="edit_email">Email</label>
                                                <input type="email" class="form-control" id="edit_email" name="email" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="edit_jenis_kelamin">Jenis Kelamin</label>
                                                <select class="form-control" id="edit_jenis_kelamin" name="jenis_kelamin" required>
                                                    <option value="Laki-laki">Laki-laki</option>
                                                    <option value="Perempuan">Perempuan</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label for="edit_tanggal_lahir">Tanggal Lahir</label>
                                                <input type="date" class="form-control" id="edit_tanggal_lahir" name="tanggal_lahir" required>
                                            </div>
                                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                        </form>
                                    </div>
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
    <?php include 'layouts/footer.php'; ?>
    <!-- AJAX script -->
    <script>
        $(document).ready(function() {
            // Proses tambah anggota
            $('#addAnggotaForm').on('submit', function(e) {
                e.preventDefault();

                $.ajax({
                    type: 'POST',
                    url: '', // URL tujuan submit
                    data: $(this).serialize() + '&action=add_anggota',
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message,
                            }).then(function() {
                                location.reload(); // Refresh halaman setelah popup ditutup
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: response.message,
                            });
                        }
                    }
                });
            });

            // Proses edit anggota
            $('#editAnggotaForm').on('submit', function(e) {
                e.preventDefault();

                $.ajax({
                    type: 'POST',
                    url: '', // URL tujuan submit
                    data: $(this).serialize() + '&action=edit_anggota',
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message,
                            }).then(function() {
                                location.reload(); // Refresh halaman setelah popup ditutup
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: response.message,
                            });
                        }
                    }
                });
            });
        });

        // Fungsi untuk mengisi data ke dalam modal edit
        function editAnggota(button) {
            var id = $(button).data('id');
            var id_anggota = $(button).data('id_anggota');
            var nama = $(button).data('nama');
            var nomor_telepon = $(button).data('nomor_telepon');
            var email = $(button).data('email');
            var jenis_kelamin = $(button).data('jenis_kelamin');
            var tanggal_lahir = $(button).data('tanggal_lahir');

            $('#edit_id').val(id);
            $('#edit_id_anggota').val(id_anggota);
            $('#edit_nama').val(nama);
            $('#edit_nomor_telepon').val(nomor_telepon);
            $('#edit_email').val(email);
            $('#edit_jenis_kelamin').val(jenis_kelamin);
            $('#edit_tanggal_lahir').val(tanggal_lahir);

            $('#editAnggotaModal').modal('show');
        }

        // Fungsi untuk menghapus anggota
        function deleteAnggota(id) {
            Swal.fire({
                title: 'Konfirmasi Hapus',
                text: "Apakah Anda yakin ingin menghapus anggota ini?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: 'POST',
                        url: 'anggota.php',
                        data: {
                            id: id,
                            action: 'delete_anggota'
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.status === 'success') {
                                Swal.fire('Berhasil!', response.message, 'success').then(() => {
                                    location.reload(); // Reload page to reflect the changes
                                });
                            } else {
                                Swal.fire('Gagal!', response.message, 'error');
                            }
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