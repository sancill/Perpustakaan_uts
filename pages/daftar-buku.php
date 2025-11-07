<?php
//proteksi agar file tidak dapat diakses langsung
if(!defined('MY_APP')) {
    die('Akses langsung tidak diperbolehkan!');
}

$sql = "SELECT * FROM buku ORDER BY id_buku  DESC";

$result = mysqli_query($koneksi, $sql);
if (!$result) {
    die("Query gagal: " . mysqli_error($koneksi));
}

$kategori_per_buku = [];
$sql_kategori = "SELECT bk.id_buku, kb.nama_kategori FROM buku_kategori bk JOIN kategori kb ON bk.id_kategori = kb.id_kategori";

$result_kategori = mysqli_query($koneksi, $sql_kategori);
if ($result_kategori) {
    while ($row = mysqli_fetch_assoc($result_kategori)) {
        $kategori_per_buku[$row['id_buku']][] = $row['nama_kategori'];
    }
}
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Buku</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Daftar Buku</li>
    </ol>

    <div class="card mb-4">
        <div class="card-body">
            <a href="index.php?hal=tambah-buku" class="btn btn-primary mb-3">Tambah Buku</a>

            <table class="table table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>No</th>
                        <th>Judul </th>
                        <th>Kategori</th>
                        <th>Penulis</th>
                        <th>Penerbit</th>
                        <th>Tahun</th>
                        <th>Stok</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    while ($row = mysqli_fetch_assoc($result)):
                    ?>
                    <tr>
                        <td><?php echo $no; ?></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <?php if (!empty($row['cover_buku']) && file_exists("uploads/buku/" . $row['cover_buku'])): ?>
                                <img src="uploads/buku/<?php echo htmlspecialchars($row['cover_buku']); ?>"
                                    alt="Cover <?php echo htmlspecialchars($row['judul']); ?>"
                                    style="width: 50px; height: 70px; object-fit: cover; margin-right: 10px; border-radius: 5px;">
                                <?php else: ?>
                                <div
                                    style="width: 50px; height: 70px; background: #eee; display: flex; align-items: center; justify-content: center; margin-right: 10px; border-radius: 5px; text-align: center;">
                                    <small>No<br>Cover</small>
                                </div>
                                <?php endif; ?>
                                <div>
                                    <strong><?php echo htmlspecialchars($row['judul']); ?></strong>
                                </div>
                            </div>
                        </td>
                        <?php
                        if (isset($kategori_per_buku[$row['id_buku']])) {
                            echo '<td>' . implode(', ', $kategori_per_buku[$row['id_buku']]) . '</td>';
                        } else {
                            echo '<td>Tidak ada kategori</td>';
                        }
                        ?>

                        <td><?php echo $row['penulis'] ?></td>
                        <td><?php echo $row['penerbit'] ?></td>
                        <td><?php echo $row['tahun_terbit'] ?></td>
                        <td><?php echo $row['stok'] ?></td>
                        <td>
                            <a href="index.php?hal=ubah-buku&id=<?php echo $row['id_buku']; ?>"
                                class="btn btn-warning btn-sm">edit</a>
                        </td>
                    </tr>
                    <?php $no++; ?>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>