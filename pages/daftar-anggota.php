<?php
//proteksi agar file tidak dapat diakses langsung
if(!defined('MY_APP')) {
    die('Akses langsung tidak diperbolehkan!');
}

$sql = "SELECT * FROM anggota ORDER BY id_anggota  DESC";

$result = mysqli_query($koneksi, $sql);
if (!$result) {
    die("Query gagal: " . mysqli_error ($koneksi));
}


?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Anggota</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Daftar Anggota</li>
    </ol>

    <div class="card mb-4">
        <div class="card-body">
            <a href="index.php?hal=tambah-anggota" class="btn btn-primary mb-3">Tambah Anggota</a>

            <table class="table table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>No</th>
                        <th>Nama </th>
                        <th>Email</th>
                        <th>Alamat</th>
                        <th>No.Telepon</th>
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
                                <?php if (!empty($row['foto_profil']) && file_exists("uploads/anggota/" . $row['foto_profil'])): ?>
                                <img src="uploads/anggota/<?php echo htmlspecialchars($row['foto_profil']); ?>"
                                    alt="Profil <?php echo htmlspecialchars($row['nama_lengkap']); ?>"
                                    style="width: 60px; height: 80px; object-fit: cover; margin-right: 10px; border-radius: 5px;">
                                <?php else: ?>
                                <div
                                    style="width: 60px; height: 80px; background: #ddd; display: flex; align-items: center; justify-content: center; margin-right: 10px; border-radius: 5px; text-align: center;">
                                    <small>Cover<br>Profil</small>
                                </div>
                                <?php endif; ?>
                                <div>
                                    <strong><?php echo htmlspecialchars($row['nama_lengkap']); ?></strong>
                                </div>
                            </div>
                        </td>
                        <td><?php echo $row['email'] ?></td>
                        <td><?php echo $row['alamat'] ?></td>
                        <td><?php echo $row['no_telepon'] ?></td>
                        <td>
                            <a href="index.php?hal=ubah-password&id_anggota=<?php echo $row['id_anggota']; ?>"
                                class='btn btn-primary btn-sm'><span class="fas fa-key me-1"></span>ubah</a>
                        </td>
                    </tr>
                    <?php $no++; ?>
                    <?php endwhile; $koneksi->close(); ?>
                </tbody>
            </table>
        </div>
    </div>
</div>