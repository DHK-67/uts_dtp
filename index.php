<?php
require_once 'config.php';

$pesan_sukses = "";
$pesan_error = "";

$nama_pelapor = "";
$nama_fasilitas = "";
$lokasi = "";
$id_kategori = "";
$deskripsi = "";
$prioritas = "";

if (isset($_POST['submit_laporan'])) {
    $nama_pelapor = isset($_POST['nama_pelapor']) ? trim($_POST['nama_pelapor']) : "";
    $nama_fasilitas = isset($_POST['nama_fasilitas']) ? trim($_POST['nama_fasilitas']) : "";
    $lokasi = isset($_POST['lokasi']) ? trim($_POST['lokasi']) : "";
    $id_kategori = isset($_POST['id_kategori']) ? $_POST['id_kategori'] : "";
    $deskripsi = isset($_POST['deskripsi']) ? trim($_POST['deskripsi']) : "";
    $prioritas = isset($_POST['prioritas']) ? $_POST['prioritas'] : "";

    if (empty($nama_pelapor) || empty($nama_fasilitas) || empty($lokasi) || empty($id_kategori) || empty($deskripsi) || empty($prioritas)) {
        $pesan_error = "Error: Semua kolom wajib diisi!";
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

            $pesan_sukses = "Laporan kerusakan berhasil disimpan ke database!";
            
            $nama_pelapor = "";
            $nama_fasilitas = "";
            $lokasi = "";
            $id_kategori = "";
            $deskripsi = "";
            $prioritas = "";
        } catch (PDOException $e) {
            $pesan_error = "Gagal menyimpan data ke database: " . $e->getMessage();
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
    $daftar_laporan = [];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pelaporan Kerusakan Fasilitas Sekolah</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <h2>Form Pelaporan Kerusakan</h2>

    <?php if (!empty($pesan_error)): ?>
        <p class="pesan-error"><?php echo $pesan_error; ?></p>
    <?php endif; ?>

    <?php if (!empty($pesan_sukses)): ?>
        <p class="pesan-sukses"><?php echo $pesan_sukses; ?></p>
    <?php endif; ?>

    <form action="index.php" method="post">
        
        <p>
            <label for="nama_pelapor">Nama Pelapor *:</label><br>
            <input type="text" id="nama_pelapor" name="nama_pelapor" value="<?php echo htmlspecialchars($nama_pelapor); ?>">
        </p>

        <p>
            <label for="nama_fasilitas">Nama Fasilitas *:</label><br>
            <input type="text" id="nama_fasilitas" name="nama_fasilitas" value="<?php echo htmlspecialchars($nama_fasilitas); ?>">
        </p>

        <p>
            <label for="lokasi">Lokasi Kerusakan *:</label><br>
            <input type="text" id="lokasi" name="lokasi" value="<?php echo htmlspecialchars($lokasi); ?>">
        </p>

        <p>
            <label for="id_kategori">Kategori Fasilitas *:</label><br>
            <select id="id_kategori" name="id_kategori">
                <option value="">-- Pilih kategori --</option>
                <?php foreach ($daftar_kategori as $kat): ?>
                    <option value="<?php echo $kat['id_kategori']; ?>" <?php if ($id_kategori == $kat['id_kategori']) echo "selected"; ?>>
                        <?php echo htmlspecialchars($kat['nama_kategori']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>

        <p>
            <label for="deskripsi">Deskripsi Kerusakan *:</label><br>
            <textarea id="deskripsi" name="deskripsi" rows="4"><?php echo htmlspecialchars($deskripsi); ?></textarea>
        </p>

        <p>
            <label for="prioritas">Prioritas *:</label><br>
            <select id="prioritas" name="prioritas">
                <option value="">-- Pilih prioritas --</option>
                <option value="Rendah" <?php if ($prioritas == "Rendah") echo "selected"; ?>>Rendah</option>
                <option value="Sedang" <?php if ($prioritas == "Sedang") echo "selected"; ?>>Sedang</option>
                <option value="Tinggi" <?php if ($prioritas == "Tinggi") echo "selected"; ?>>Tinggi</option>
            </select>
        </p>

        <p>
            <button type="submit" name="submit_laporan">Kirim Laporan</button>
        </p>

    </form>

    <hr>

    <h2>Daftar Laporan Kerusakan</h2>

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
                        <td><?php echo $no++; ?></td>
                        <td><?php echo htmlspecialchars($row['nama_fasilitas']); ?></td>
                        <td><?php echo htmlspecialchars($row['nama_kategori']); ?></td>
                        <td><?php echo htmlspecialchars($row['lokasi']); ?></td>
                        <td><?php echo htmlspecialchars($row['nama_pelapor']); ?></td>
                        <td><?php echo htmlspecialchars($row['prioritas']); ?></td>
                        <td><?php echo htmlspecialchars($row['status']); ?></td>
                        <td><?php echo htmlspecialchars($row['tanggal_laporan']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8">Belum ada data laporan kerusakan.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

</body>
</html>