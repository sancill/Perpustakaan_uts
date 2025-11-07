<?php
//proteksi agar file tidak dapat diakses langsung
if(!defined('MY_APP')) {
    die('Akses langsung tidak diperbolehkan!');
}

// Pastikan session sudah dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inisialisasi pesan
$pesan = '';
$pesan_error = '';

if (isset($_GET['id']) && !empty ($_GET['id'])) {
    $id_buku = $_GET['id'];
    $sql_buku = "SELECT * FROM buku WHERE id_buku = ?";
    if ($stmt = $koneksi->prepare($sql_buku)) {
        $stmt->bind_param("i", $id_buku);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows == 1) {
                // Buku ditemukan
                $buku = $result->fetch_assoc();
            } else {
                echo "Buku tidak ditemukan";
                exit();
            }
            $stmt->close();
                    // Ambil kategori yang dipilih untuk buku ini
                    $kategori_terpilih = array();
                    $sql_kat_terpilih = "SELECT id_kategori FROM buku_kategori WHERE id_buku = ?";
                    if ($stmt_kat = $koneksi->prepare($sql_kat_terpilih)) {
                        $stmt_kat->bind_param("i", $id_buku);
                        $stmt_kat->execute();
                        $res_kat = $stmt_kat->get_result();
                        while ($r = $res_kat->fetch_assoc()) {
                            $kategori_terpilih[] = $r['id_kategori'];
                        }
                        $stmt_kat->close();
                    }
        }
    } else {
        header("Location: index.php?hal=daftar-buku");
        exit();
    }
} else {
    header("Location: index.php?hal=daftar-buku");
    exit();
}


// Tangani POST untuk update buku
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $judul_buku = isset($_POST['judul_buku']) ? $_POST['judul_buku'] : '';
    $penulis = isset($_POST['penulis']) ? $_POST['penulis'] : '';
    $penerbit = isset($_POST['penerbit']) ? $_POST['penerbit'] : '';
    $tahun_terbit = isset($_POST['tahun_terbit']) ? $_POST['tahun_terbit'] : '';
    $stok = isset($_POST['stok']) ? $_POST['stok'] : '';

    // Siapkan cover default dari buku lama
    $cover_name = isset($buku['cover_buku']) ? $buku['cover_buku'] : '';

    // Upload cover baru jika ada
    if (isset($_FILES['cover']) && $_FILES['cover']['error'] == 0 && !empty($_FILES['cover']['name'])) {
        $target_dir = dirname(dirname(__FILE__)) . "/uploads/buku/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_extension = strtolower(pathinfo($_FILES["cover"]["name"], PATHINFO_EXTENSION));
        $file_name = time() . '.' . $file_extension;
        $target_file = $target_dir . $file_name;

        // Cek tipe file
        $allowed_types = array('jpg', 'jpeg', 'png', 'gif');
        if (in_array(strtolower($file_extension), $allowed_types)) {
            if (move_uploaded_file($_FILES["cover"]["tmp_name"], $target_file)) {
                // Hapus cover lama bila ada
                if (!empty($cover_name) && file_exists($target_dir . $cover_name)) {
                    @unlink($target_dir . $cover_name);
                }
                $cover_name = $file_name;
            } else {
                $pesan_error = "Maaf, terjadi kesalahan saat mengupload file.";
            }
        } else {
            $pesan_error = "Maaf, hanya file JPG, JPEG, PNG & GIF yang diizinkan.";
        }
    }

    if (empty($pesan_error)) {
        // Update data buku
        $sql = "UPDATE buku SET judul = ?, penulis = ?, penerbit = ?, tahun_terbit = ?, stok = ?, cover_buku = ? WHERE id_buku = ?";
        if ($stmt = $koneksi->prepare($sql)) {
            $stmt->bind_param("ssssssi", $judul_buku, $penulis, $penerbit, $tahun_terbit, $stok, $cover_name, $id_buku);
            if ($stmt->execute()) {
                // Update kategori: hapus lalu insert baru
                $sql_delete = "DELETE FROM buku_kategori WHERE id_buku = ?";
                if ($del = $koneksi->prepare($sql_delete)) {
                    $del->bind_param("i", $id_buku);
                    $del->execute();
                    $del->close();
                }

                if (!empty($_POST['kategori'])) {
                    $sql_ins = "INSERT INTO buku_kategori (id_buku, id_kategori) VALUES (?, ?)";
                    if ($ins = $koneksi->prepare($sql_ins)) {
                        foreach ($_POST['kategori'] as $id_kategori) {
                            $ins->bind_param("ii", $id_buku, $id_kategori);
                            $ins->execute();
                        }
                        $ins->close();
                    }
                }

                // Tampilkan pesan sukses dan refresh data pada halaman yang sama
                $pesan = "Buku berhasil diperbarui.";

                // Refresh data buku agar form menampilkan nilai terbaru
                $sql_refresh = "SELECT * FROM buku WHERE id_buku = ?";
                if ($stmt_refresh = $koneksi->prepare($sql_refresh)) {
                    $stmt_refresh->bind_param("i", $id_buku);
                    $stmt_refresh->execute();
                    $res_refresh = $stmt_refresh->get_result();
                    if ($res_refresh && $res_refresh->num_rows == 1) {
                        $buku = $res_refresh->fetch_assoc();
                    }
                    $stmt_refresh->close();
                }

                // Refresh kategori yang dipilih agar checkbox ter-update
                $kategori_terpilih = array();
                $sql_kat_terpilih = "SELECT id_kategori FROM buku_kategori WHERE id_buku = ?";
                if ($stmt_kat = $koneksi->prepare($sql_kat_terpilih)) {
                    $stmt_kat->bind_param("i", $id_buku);
                    $stmt_kat->execute();
                    $res_kat = $stmt_kat->get_result();
                    while ($r = $res_kat->fetch_assoc()) {
                        $kategori_terpilih[] = $r['id_kategori'];
                    }
                    $stmt_kat->close();
                }
            } else {
                $pesan_error = "Gagal memperbarui buku: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $pesan_error = "Error dalam menyiapkan query update: " . $koneksi->error;
        }
    }
}
        
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Buku</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Ubah Buku</li>
    </ol>
    <?php if (!empty($pesan )): ?>
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
            <form method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="judul" class="form-label">Judul Buku</label>
                    <input type="text" class="form-control" id="judul" name="judul_buku"
                        value="<?php echo isset($buku['judul']) ? htmlspecialchars($buku['judul']) : ''; ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Pilih Kategori</label><br>
                    <div class="row">
                        <?php
                        // Ambil daftar kategori dari database (gunakan query sendiri)
                        $sql_kategori = "SELECT * FROM kategori ORDER BY nama_kategori ASC";
                        $result_kategori = mysqli_query($koneksi, $sql_kategori);
                        $kategoris = array();
                        if ($result_kategori) {
                            while ($row_k = mysqli_fetch_assoc($result_kategori)) {
                                $kategoris[] = $row_k;
                            }
                        }

                        foreach ($kategoris as $kat):
                            $id_kat = isset($kat['id_kategori']) ? $kat['id_kategori'] : '';
                            $nama_kat = isset($kat['nama_kategori']) ? $kat['nama_kategori'] : '';
                            $checked = in_array($id_kat, $kategori_terpilih) ? 'checked' : '';
                        ?>
                        <div class="col-md-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="kategori[]"
                                    value="<?php echo htmlspecialchars($id_kat); ?>"
                                    id="kategori_<?php echo htmlspecialchars($id_kat); ?>" <?php echo $checked; ?>>
                                <label class="form-check-label" for="kategori_<?php echo htmlspecialchars($id_kat); ?>">
                                    <?php echo htmlspecialchars($nama_kat); ?>
                                </label>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="penulis" class="form-label">Penulis</label>
                    <input type="text" class="form-control" id="penulis" name="penulis"
                        value="<?php echo isset($buku['penulis']) ? htmlspecialchars($buku['penulis']) : ''; ?>"
                        required>
                </div>
                <div class="mb-3">
                    <label for="penerbit" class="form-label">Penerbit</label>
                    <input type="text" class="form-control" id="penerbit" name="penerbit"
                        value="<?php echo isset($buku['penerbit']) ? htmlspecialchars($buku['penerbit']) : ''; ?>"
                        required>
                </div>
                <div class="mb-3">
                    <label for="tahun_terbit" class="form-label">Tahun Terbit</label>
                    <input type="text" class="form-control" id="tahun_terbit" name="tahun_terbit"
                        value="<?php echo isset($buku['tahun_terbit']) ? htmlspecialchars($buku['tahun_terbit']) : ''; ?>"
                        required>
                </div>
                <div class="mb-3">
                    <label for="stok" class="form-label">Stok</label>
                    <input type="text" class="form-control" id="stok" name="stok"
                        value="<?php echo isset($buku['stok']) ? htmlspecialchars($buku['stok']) : ''; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="cover" class="form-label">Upload Cover</label>
                    <?php if (!empty($buku['cover_buku']) && file_exists(dirname(dirname(__FILE__)) . '/uploads/buku/' . $buku['cover_buku'])): ?>
                    <div class="mb-2">
                        <img src="uploads/buku/<?php echo htmlspecialchars($buku['cover_buku']); ?>"
                            alt="Cover saat ini" style="max-height:200px;">
                        <p class="text-muted">Cover saat ini</p>
                    </div>
                    <?php endif; ?>
                    <input type="file" class="form-control" id="cover" name="cover" accept="image/*">
                    <small class="text-muted">Format yang diizinkan: JPG, JPEG, PNG, GIF</small>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    <a href="index.php?hal=daftar-buku" class="btn btn-secondary ms-2">Kembali</a>
                </div>
            </form>
        </div>
    </div>
</div>