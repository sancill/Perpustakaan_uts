<?php
//proteksi agar file tidak dapat diakses langsung
if(!defined('MY_APP')) {
    die('Akses langsung tidak diperbolehkan!');
}

$sql = "SELECT * FROM kategori ORDER BY id_kategori DESC";

$result = mysqli_query($koneksi, $sql);
if (!$result) {
    die("Query gagal: " . mysqli_error($koneksi));
}

?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Dashboard</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Daftar Kategori</li>
    </ol>

    <div class="card mb-4">
        <div class="card-body">
            <a href="index.php?hal=tambah-kategori" class="btn btn-primary mb-3">Tambah Kategori</a>

            <table class="table table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>No</th>
                        <th>Nama Kategori</th>
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
                        <td><?php echo htmlspecialchars($row['nama_kategori']); ?></td>
                        <td>
                            <a href="index.php?hal=ubah-kategori&id=<?php echo $row['id_kategori']; ?>"
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