<?php
require_once 'config.php';

$pesan_sukses = '';
$pesan_error  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_laporan'])) {
    $nama_pelapor   = isset($_POST['nama_pelapor']) ? trim($_POST['nama_pelapor']) : '';
    $nama_fasilitas = isset($_POST['nama_fasilitas']) ? trim($_POST['nama_fasilitas']) : '';
    $lokasi         = isset($_POST['lokasi']) ? trim($_POST['lokasi']) : '';
    $id_kategori    = isset($_POST['id_kategori']) ? $_POST['id_kategori'] : '';
    $deskripsi      = isset($_POST['deskripsi']) ? trim($_POST['deskripsi']) : '';
    $prioritas      = isset($_POST['prioritas']) ? $_POST['prioritas'] : '';

    if (empty($nama_pelapor) || empty($nama_fasilitas) || empty($lokasi) || empty($id_kategori) || empty($deskripsi) || empty($prioritas)) {
        $pesan_error = "Harap isi semua kolom form pelaporan!";
    } else {
        try {
            $sql = "INSERT INTO laporan_kerusakan (nama_pelapor, nama_fasilitas, lokasi, id_kategori, deskripsi, prioritas) 
                    VALUES (:nama_pelapor, :nama_fasilitas, :lokasi, :id_kategori, :deskripsi, :prioritas)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nama_pelapor'   => $nama_pelapor,
                ':nama_fasilitas' => $nama_fasilitas,
                ':lokasi'         => $lokasi,
                ':id_kategori'    => $id_kategori,
                ':deskripsi'      => $deskripsi,
                ':prioritas'      => $prioritas
            ]);
            $pesan_sukses = "Laporan kerusakan berhasil dikirim!";
        } catch (PDOException $e) {
            $pesan_error = "Gagal menyimpan laporan: " . $e->getMessage();
        }
    }
}

try {
    $stmt_kategori = $pdo->query("SELECT * FROM kategori_fasilitas ORDER BY id_kategori ASC");
    $daftar_kategori = $stmt_kategori->fetchAll();
} catch (PDOException $e) {
    $daftar_kategori = [];
}

try {
    $sql_laporan = "SELECT l.*, k.nama_kategori 
                    FROM laporan_kerusakan l 
                    JOIN kategori_fasilitas k ON l.id_kategori = k.id_kategori 
                    ORDER BY l.tanggal_laporan DESC";
    $stmt_laporan = $pdo->query($sql_laporan);
    $daftar_laporan = $stmt_laporan->fetchAll();
} catch (PDOException $e) {
    $pesan_error = "Gagal mengambil data laporan: " . $e->getMessage();
    $daftar_laporan = [];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pelaporan Kerusakan Fasilitas Sekolah</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <header>
        <h1>Sistem Pelaporan Kerusakan Fasilitas Sekolah</h1>
        <p>Catat dan pantau laporan kerusakan fasilitas sekolah.</p>
        <nav aria-label="Navigasi utama">
            <a href="#form-laporan">Buat Laporan</a>
            <a href="#daftar-laporan">Daftar Laporan</a>
        </nav>
    </header>

    <main>
        <?php if (!empty($pesan_sukses)): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($pesan_sukses); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($pesan_error)): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($pesan_error); ?>
            </div>
        <?php endif; ?>

        <section id="form-laporan">
            <h2>Form Pelaporan Kerusakan</h2>
            <p>Lengkapi data berikut untuk melaporkan fasilitas yang rusak.</p>

            <form action="index.php#form-laporan" method="post">
                <div class="form-row">
                    <div class="form-col form-group">
                        <label for="nama_pelapor">Nama Pelapor</label>
                        <input type="text" id="nama_pelapor" name="nama_pelapor" maxlength="100" required>
                    </div>

                    <div class="form-col form-group">
                        <label for="nama_fasilitas">Nama Fasilitas</label>
                        <input type="text" id="nama_fasilitas" name="nama_fasilitas" maxlength="100" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col form-group">
                        <label for="lokasi">Lokasi Kerusakan</label>
                        <input type="text" id="lokasi" name="lokasi" maxlength="100" required>
                    </div>

                    <div class="form-col form-group">
                        <label for="id_kategori">Kategori Fasilitas</label>
                        <select id="id_kategori" name="id_kategori" required>
                            <option value="">-- Pilih kategori --</option>
                            <?php foreach ($daftar_kategori as $kat): ?>
                                <option value="<?= $kat['id_kategori']; ?>">
                                    <?= htmlspecialchars($kat['nama_kategori']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="deskripsi">Deskripsi Kerusakan</label>
                    <textarea id="deskripsi" name="deskripsi" rows="4" required></textarea>
                </div>

                <div class="form-group">
                    <label for="prioritas">Prioritas</label>
                    <select id="prioritas" name="prioritas" required>
                        <option value="">-- Pilih prioritas --</option>
                        <option value="Rendah">Rendah</option>
                        <option value="Sedang">Sedang</option>
                        <option value="Tinggi">Tinggi</option>
                    </select>
                </div>

                <div class="form-group">
                    <button type="submit" name="submit_laporan">Kirim Laporan</button>
                </div>
            </form>
        </section>

        <section id="daftar-laporan">
            <h2>Daftar Laporan Kerusakan</h2>
            <p>Seluruh laporan yang tercatat pada database ditampilkan di bagian ini.</p>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Fasilitas</th>
                            <th>Kategori</th>
                            <th>Lokasi</th>
                            <th>Pelapor</th>
                            <th>Prioritas</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($daftar_laporan)): ?>
                            <?php $no = 1; ?>
                            <?php foreach ($daftar_laporan as $row): ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($row['nama_fasilitas']); ?></strong>
                                        <div style="font-size: 12px; color: #666;"><?= htmlspecialchars($row['deskripsi']); ?></div>
                                    </td>
                                    <td><span class="badge badge-kategori"><?= htmlspecialchars($row['nama_kategori']); ?></span></td>
                                    <td><?= htmlspecialchars($row['lokasi']); ?></td>
                                    <td><?= htmlspecialchars($row['nama_pelapor']); ?></td>
                                    <td>
                                        <?php
                                            $cls_p = 'badge-rendah';
                                            if ($row['prioritas'] === 'Tinggi') $cls_p = 'badge-tinggi';
                                            elseif ($row['prioritas'] === 'Sedang') $cls_p = 'badge-sedang';
                                        ?>
                                        <span class="badge <?= $cls_p; ?>"><?= htmlspecialchars($row['prioritas']); ?></span>
                                    </td>
                                    <td>
                                        <?php
                                            $cls_s = 'badge-menunggu';
                                            if ($row['status'] === 'Diproses') $cls_s = 'badge-diproses';
                                            elseif ($row['status'] === 'Selesai') $cls_s = 'badge-selesai';
                                        ?>
                                        <span class="badge <?= $cls_s; ?>"><?= htmlspecialchars($row['status']); ?></span>
                                    </td>
                                    <td style="font-size: 12px; color: #666;">
                                        <?= date('d/m/Y H:i', strtotime($row['tanggal_laporan'])); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" style="text-align: center;">Belum ada data laporan kerusakan.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

    </main>

    <footer>
        <p>&copy; 2024 Sistem Pelaporan Kerusakan Fasilitas Sekolah.</p>
    </footer>

</body>
</html>