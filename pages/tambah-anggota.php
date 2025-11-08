<?php
//proteksi agar file tidak dapat diakses langsung
if(!defined('MY_APP')) { die('Akses langsung tidak diperbolehkan!'); }
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$pesan = '';
$pesan_error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_lengkap = $_POST['nama_lengkap'];
    $email = $_POST['email'];
    $alamat = $_POST['alamat'];
    $no_telp = $_POST['no_telp'];
    $password = $_POST['password'];
    $foto_profil = null;
    // Proses upload foto
    if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] == 0) {
        $target_dir = 'uploads/anggota/';
        if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }
        $file_extension = strtolower(pathinfo($_FILES['foto_profil']['name'], PATHINFO_EXTENSION));
        $file_name = time() . '.' . $file_extension;
        $target_file = $target_dir . $file_name;
        $allowed_types = array('jpg', 'jpeg', 'png', 'gif');
        if (in_array($file_extension, $allowed_types)) {
            if (move_uploaded_file($_FILES['foto_profil']['tmp_name'], $target_file)) {
                $foto_profil = $file_name;
            } else {
                $pesan_error = 'Gagal upload foto.';
            }
        } else {
            $pesan_error = 'Format foto tidak didukung.';
        }
    }
    if (empty($pesan_error)) {
        $sql = "INSERT INTO anggota (nama_lengkap, email, alamat, no_telepon, password, foto_profil) VALUES (?, ?, ?, ?, ?, ?)";
        if ($stmt = $koneksi->prepare($sql)) {
            $stmt->bind_param('ssssss', $nama_lengkap, $email, $alamat, $no_telp, $password, $foto_profil);
            if ($stmt->execute()) {
                $pesan = "Anggota Dengan Nama <b> . $nama_lengkap . <b> Berhasil di Tambahkan";
                echo "<script>window.location.href = 'index.php?hal=daftar-anggota';</script>";
                exit();
            } else {
                $pesan_error = 'Gagal menambah anggota.';
            }
            $stmt->close();
        }
    }
}
?>
<div class="container-fluid px-4">
    <h1 class="mt-4">Anggota</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Tambah Anggota</li>
    </ol>
    <?php if (!empty($pesan)): ?>
    <div class="alert alert-success" role="alert"><?php echo $pesan; ?></div>
    <?php endif; ?>
    <?php if (!empty($pesan_error)): ?>
    <div class="alert alert-danger" role="alert"><?php echo $pesan_error; ?></div>
    <?php endif; ?>
    <div class="card mb-4">
        <div class="card-body">
            <form method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                    <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" required>
                </div>
                <div class="mb-3">
                    <label for="alamat" class="form-label">Alamat Lengkap</label>
                    <input type="text" class="form-control" id="alamat" name="alamat" required>
                </div>
                <div class="mb-3">
                    <label for="no_telp" class="form-label">No Telepon</label>
                    <input type="text" class="form-control" id="no_telp" name="no_telp" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="mb-3">
                    <label for="foto_profil" class="form-label">Upload Foto Profil</label>
                    <input type="file" class="form-control" id="foto_profil" name="foto_profil" accept="image/*">
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="index.php?hal=daftar-anggota" class="btn btn-secondary ms-2">Kembali</a>
                </div>
            </form>
        </div>
    </div>
</div>