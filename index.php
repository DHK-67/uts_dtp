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
    $nama_pelapor   = isset($_POST['nama_pelapor']) ? trim($_POST['nama_pelapor']) : "";
    $nama_fasilitas = isset($_POST['nama_fasilitas']) ? trim($_POST['nama_fasilitas']) : "";
    $lokasi         = isset($_POST['lokasi']) ? trim($_POST['lokasi']) : "";
    $id_kategori    = isset($_POST['id_kategori']) ? $_POST['id_kategori'] : "";
    $deskripsi      = isset($_POST['deskripsi']) ? trim($_POST['deskripsi']) : "";
    $prioritas      = isset($_POST['prioritas']) ? $_POST['prioritas'] : "";

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

            $pesan_sukses = "Laporan kerusakan berhasil disimpan ke database!";
            
            $nama_pelapor = "";
            $nama_fasilitas = "";
            $lokasi = "";
            $id_kategori = "";
            $deskripsi = "";
            $prioritas = "";
        } catch (PDOException $e) {
            $pesan_error = "Gagal menyimpan data: " . $e->getMessage();
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pelaporan Kerusakan Fasilitas Sekolah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <header class="text-white py-4 mb-4 shadow-sm">
        <div class="container text-center">
            <h1 class="h3 fw-bold">Sistem Pelaporan Kerusakan Fasilitas Sekolah</h1>
            <p class="mb-3 opacity-75">Catat dan pantau laporan kerusakan fasilitas sekolah.</p>
            <nav aria-label="Navigasi utama">
                <a href="#form-laporan" class="btn btn-sm btn-outline-light me-1">Buat Laporan</a>
                <a href="#daftar-laporan" class="btn btn-sm btn-outline-light">Daftar Laporan</a>
            </nav>
        </div>
    </header>

    <main class="container my-4">

        <?php if (!empty($pesan_sukses)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($pesan_sukses); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($pesan_error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($pesan_error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <section id="form-laporan" class="card shadow-sm mb-4">
            <div class="card-body p-4">
                <h2 class="h5 fw-bold mb-1">Form Pelaporan Kerusakan</h2>
                <p class="text-muted small mb-4">Lengkapi data berikut untuk melaporkan fasilitas yang rusak.</p>

                <form action="index.php#form-laporan" method="post">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="nama_pelapor" class="form-label fw-bold small">Nama Pelapor</label>
                            <input type="text" class="form-control" id="nama_pelapor" name="nama_pelapor" value="<?php echo htmlspecialchars($nama_pelapor); ?>" maxlength="100" required>
                        </div>

                        <div class="col-md-6">
                            <label for="nama_fasilitas" class="form-label fw-bold small">Nama Fasilitas</label>
                            <input type="text" class="form-control" id="nama_fasilitas" name="nama_fasilitas" value="<?php echo htmlspecialchars($nama_fasilitas); ?>" maxlength="100" required>
                        </div>

                        <div class="col-md-6">
                            <label for="lokasi" class="form-label fw-bold small">Lokasi Kerusakan</label>
                            <input type="text" class="form-control" id="lokasi" name="lokasi" value="<?php echo htmlspecialchars($lokasi); ?>" maxlength="100" required>
                        </div>

                        <div class="col-md-6">
                            <label for="id_kategori" class="form-label fw-bold small">Kategori Fasilitas</label>
                            <select class="form-select" id="id_kategori" name="id_kategori" required>
                                <option value="">-- Pilih kategori --</option>
                                <?php foreach ($daftar_kategori as $kat): ?>
                                    <option value="<?php echo $kat['id_kategori']; ?>" <?php if ($id_kategori == $kat['id_kategori']) echo "selected"; ?>>
                                        <?php echo htmlspecialchars($kat['nama_kategori']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12">
                            <label for="deskripsi" class="form-label fw-bold small">Deskripsi Kerusakan</label>
                            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3" required><?php echo htmlspecialchars($deskripsi); ?></textarea>
                        </div>

                        <div class="col-12">
                            <label for="prioritas" class="form-label fw-bold small">Prioritas</label>
                            <select class="form-select" id="prioritas" name="prioritas" required>
                                <option value="">-- Pilih prioritas --</option>
                                <option value="Rendah" <?php if ($prioritas == "Rendah") echo "selected"; ?>>Rendah</option>
                                <option value="Sedang" <?php if ($prioritas == "Sedang") echo "selected"; ?>>Sedang</option>
                                <option value="Tinggi" <?php if ($prioritas == "Tinggi") echo "selected"; ?>>Tinggi</option>
                            </select>
                        </div>

                        <div class="col-12 mt-3">
                            <button type="submit" name="submit_laporan" class="btn btn-primary px-4">Kirim Laporan</button>
                        </div>
                    </div>
                </form>
            </div>
        </section>

        <section id="daftar-laporan" class="card shadow-sm">
            <div class="card-body p-4">
                <h2 class="h5 fw-bold mb-1">Daftar Laporan Kerusakan</h2>
                <p class="text-muted small mb-4">Seluruh laporan yang tercatat pada database ditampilkan di bagian ini.</p>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th scope="col" style="width: 50px;">No.</th>
                                <th scope="col">Fasilitas</th>
                                <th scope="col">Kategori</th>
                                <th scope="col">Lokasi</th>
                                <th scope="col">Pelapor</th>
                                <th scope="col">Prioritas</th>
                                <th scope="col">Status</th>
                                <th scope="col">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($daftar_laporan)): ?>
                                <?php $no = 1; ?>
                                <?php foreach ($daftar_laporan as $row): ?>
                                    <tr>
                                        <td class="fw-bold"><?php echo $no++; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($row['nama_fasilitas']); ?></strong>
                                            <div class="text-muted small"><?php echo htmlspecialchars($row['deskripsi']); ?></div>
                                        </td>
                                        <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($row['nama_kategori']); ?></span></td>
                                        <td><?php echo htmlspecialchars($row['lokasi']); ?></td>
                                        <td><?php echo htmlspecialchars($row['nama_pelapor']); ?></td>
                                        <td>
                                            <?php
                                                $badge_p = 'bg-secondary';
                                                if ($row['prioritas'] === 'Tinggi') $badge_p = 'bg-danger';
                                                elseif ($row['prioritas'] === 'Sedang') $badge_p = 'bg-warning text-dark';
                                            ?>
                                            <span class="badge <?php echo $badge_p; ?>"><?php echo htmlspecialchars($row['prioritas']); ?></span>
                                        </td>
                                        <td>
                                            <?php
                                                $badge_s = 'bg-warning text-dark';
                                                if ($row['status'] === 'Diproses') $badge_s = 'bg-info text-dark';
                                                elseif ($row['status'] === 'Selesai') $badge_s = 'bg-success';
                                            ?>
                                            <span class="badge <?php echo $badge_s; ?>"><?php echo htmlspecialchars($row['status']); ?></span>
                                        </td>
                                        <td class="small text-muted">
                                            <?php echo date('d/m/Y H:i', strtotime($row['tanggal_laporan'])); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">Belum ada data laporan kerusakan.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

    </main>

    <footer class="text-center py-4 text-muted border-top mt-5 bg-white">
        <p class="mb-0 small">&copy; 2024 Sistem Pelaporan Kerusakan Fasilitas Sekolah.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>