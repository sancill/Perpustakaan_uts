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

// Ambil data kategori untuk dropdown
$sql = "SELECT * FROM kategori ORDER BY nama_kategori ASC";
$result = mysqli_query($koneksi, $sql);
if (!$result) {
    die("Query gagal: " . mysqli_error($koneksi));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $judul_buku = $_POST['judul'];
    $penulis = $_POST['penulis'];
    $penerbit = $_POST['penerbit'];
    $tahun_terbit = $_POST['tahun_terbit'];
    $stok = $_POST['stok'];

    // Upload cover
    $cover_name = '';
    if (isset($_FILES['cover']) && $_FILES['cover']['error'] == 0) {
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
                $cover_name = $file_name;
            } else {
                $pesan_error = "Maaf, terjadi kesalahan saat mengupload file.";
            }
        } else {
            $pesan_error = "Maaf, hanya file JPG, JPEG, PNG & GIF yang diizinkan.";
        }
    }

    if (empty($pesan_error)) {
        // Query insert buku
        $sql = "INSERT INTO buku (judul, penulis, penerbit, tahun_terbit, stok, cover_buku) VALUES (?, ?, ?, ?, ?, ?)";
        
        if ($stmt = $koneksi->prepare($sql)) {
            $stmt->bind_param("ssssss", $judul_buku, $penulis, $penerbit, $tahun_terbit, $stok, $cover_name);
            
            if ($stmt->execute()) {
                $id_buku = $koneksi->insert_id;
                
                // Simpan kategori buku
                if (!empty($_POST['kategori'])) {
                    foreach ($_POST['kategori'] as $id_kategori) {
                        $sql_kategori = "INSERT INTO buku_kategori (id_buku, id_kategori) VALUES (?, ?)";
                        $stmt_kategori = $koneksi->prepare($sql_kategori);
                        $stmt_kategori->bind_param("ii", $id_buku, $id_kategori);
                        $stmt_kategori->execute();
                        $stmt_kategori->close();
                    }
                }
                
                $_SESSION['pesan'] = "Buku berhasil ditambahkan.";
                echo "<script>window.location.href = 'index.php?hal=daftar-buku';</script>";
                exit();
            } else {
                $pesan_error = "Gagal menambahkan buku: " . $koneksi->error;
            }
            $stmt->close();
        } else {
            $pesan_error = "Error dalam menyiapkan query: " . $koneksi->error;
        }
    }
}
        
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Buku</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Tambah Buku</li>
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
                    <input type="text" class="form-control" id="judul" name="judul" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Pilih Kategori</label><br>
                    <div class="row">
                        <?php 
                        // Gunakan result yang sudah diambil di awal file
                        mysqli_data_seek($result, 0); // Reset pointer hasil query
                        while($kat = mysqli_fetch_assoc($result)): 
                        ?>
                        <div class="col-md-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="kategori[]"
                                    value="<?php echo $kat['id_kategori']; ?>"
                                    id="kategori_<?php echo $kat['id_kategori']; ?>">
                                <label class="form-check-label" for="kategori_<?php echo $kat['id_kategori']; ?>">
                                    <?php echo htmlspecialchars($kat['nama_kategori']); ?>
                                </label>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="penulis" class="form-label">Penulis</label>
                    <input type="text" class="form-control" id="penulis" name="penulis" required>
                </div>
                <div class="mb-3">
                    <label for="penerbit" class="form-label">Penerbit</label>
                    <input type="text" class="form-control" id="penerbit" name="penerbit" required>
                </div>
                <div class="mb-3">
                    <label for="tahun_terbit" class="form-label">Tahun Terbit</label>
                    <input type="text" class="form-control" id="tahun_terbit" name="tahun_terbit" required>
                </div>
                <div class="mb-3">
                    <label for="stok" class="form-label">Stok</label>
                    <input type="text" class="form-control" id="stok" name="stok" required>
                </div>
                <div class="mb-3">
                    <label for="cover" class="form-label">Upload Cover</label>
                    <input type="file" class="form-control" id="cover" name="cover" accept="image/*">
                    <small class="text-muted">Format yang diizinkan: JPG, JPEG, PNG, GIF</small>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Simpan Buku</button>
                    <a href="index.php?hal=daftar-buku" class="btn btn-secondary ms-2">Kembali</a>
                </div>
            </form>
        </div>
    </div>
</div>