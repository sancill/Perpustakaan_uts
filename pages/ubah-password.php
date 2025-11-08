<?php
//proteksi agar file tidak dapat diakses langsung
if(!defined('MY_APP')) {
    die('Akses langsung tidak diperbolehkan!');
}

// Initialize variables to avoid undefined warnings
$pesan_error = '';
$pesan = '';
$anggota = null;
$id_anggota = null;

// Accept either 'id' (used by sidebar) or 'id_anggota' (used by daftar-anggota)
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id_anggota = $_GET['id'];
} elseif (isset($_GET['id_anggota']) && !empty($_GET['id_anggota'])) {
    $id_anggota = $_GET['id_anggota'];
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_anggota'])) {
    // Persist id from hidden input after form submit
    $id_anggota = $_POST['id_anggota'];
}

// If we have an id, try fetch anggota data
if ($id_anggota !== null) {
    $sql = "SELECT * FROM anggota WHERE id_anggota = ?";
    if ($stmt = $koneksi->prepare($sql)) {
        $stmt->bind_param("i", $id_anggota);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows == 1) {
                $anggota = $result->fetch_assoc();
            } else {
                $pesan_error = "Data anggota tidak ditemukan.";
            }
        } else {
            $pesan_error = "Query gagal saat mengeksekusi.";
        }
        $stmt->close();
    } else {
        $pesan_error = "Query gagal.";
    }
} else {
    // only show this message when arriving by GET without id
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $pesan_error = "Anggota tidak ditemukan.";
    }
}

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // ensure we have the id from POST
    if (isset($_POST['id_anggota'])) {
        $id_anggota = $_POST['id_anggota'];
    }

    if ($id_anggota === null) {
        $pesan_error = "ID anggota tidak tersedia.";
    } else {
        $password = md5($_POST['password']);

        $sql = "UPDATE anggota SET password = ? WHERE id_anggota = ?";
        if ($stmt = $koneksi->prepare($sql)) {
            $stmt->bind_param("si", $password, $id_anggota);
            if ($stmt->execute()) {
                // If we don't have nama_lengkap loaded, try to fetch it for the success message
                if ($anggota === null) {
                    $stmt->close();
                    if ($s2 = $koneksi->prepare("SELECT nama_lengkap FROM anggota WHERE id_anggota = ?")) {
                        $s2->bind_param("i", $id_anggota);
                        if ($s2->execute()) {
                            $r2 = $s2->get_result();
                            if ($r2 && $r2->num_rows == 1) {
                                $ag2 = $r2->fetch_assoc();
                                $pesan = "Password User <b>" . htmlspecialchars($ag2['nama_lengkap']) . "</b> berhasil di ubah";
                            }
                        }
                        $s2->close();
                    }
                } else {
                    $pesan = "Password User <b>" . htmlspecialchars($anggota['nama_lengkap']) . "</b> berhasil di ubah";
                }
            } else {
                $pesan_error = "Terjadi kesalahan saat menyimpan data";
            }
            if ($stmt) $stmt->close();
        } else {
            $pesan_error = "Query gagal.";
        }
    }
}
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Anggota</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Ubah Password</li>
    </ol>
    <?php if (!empty($pesan)): ?>
    <div class="alert alert-success" role="alert">
        <?php echo $pesan ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($pesan_error)): ?>
    <div class="alert alert-danger" role="alert">
        <?php echo $pesan_error ?>
    </div>
    <?php endif; ?>


    <div class="card mb-4">
        <div class="card-body">
            <form method="post">
                <!-- persist id across POST -->
                <input type="hidden" name="id_anggota" value="<?php echo htmlspecialchars($id_anggota); ?>">

                <div class="mb-3">
                    <label for="nama_lengkap" class="form-label">Nama Anggota</label>
                    <input type="text" class="form-control" id="nama_lengkap"
                        value="<?php echo isset($anggota['nama_lengkap']) ? htmlspecialchars($anggota['nama_lengkap']) : ''; ?>"
                        disabled readonly>
                </div>

                <div>
                    <label for="email" class="form-label">Email Anggota</label>
                    <input type="email" class="form-control" id="email"
                        value="<?php echo isset($anggota['email']) ? htmlspecialchars($anggota['email']) : ''; ?>"
                        disabled readonly>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password Baru</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>

                <button type="submit" class="btn btn-primary">Submit</button>
                <a href="index.php?hal=daftar-anggota" class="btn btn-danger">kembali</a>
            </form>
        </div>
    </div>
</div>