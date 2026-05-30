-- ============================================================
-- db_artikel.sql — Database Lengkap untuk ArtikelHub
-- Versi: gabungan data asli + patch bcrypt-ready
-- Compatible: MySQL 5.7+ / MariaDB 10.4+
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- ============================================================
-- Database: `db_artikel`
-- ============================================================

CREATE DATABASE IF NOT EXISTS `db_artikel`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;

USE `db_artikel`;

-- ============================================================
-- Tabel: `users`
-- Catatan: kolom `password` ukuran 255 agar support bcrypt
--          (MD5 = 32 char, bcrypt = 60 char, aman di 255)
-- ============================================================

DROP TABLE IF EXISTS `articles`;  -- drop dulu karena ada FK
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id`         int(11)                   NOT NULL AUTO_INCREMENT,
  `username`   varchar(50)               NOT NULL,
  `password`   varchar(255)              NOT NULL,
  `role`       enum('admin','user')      DEFAULT 'user',
  `created_at` timestamp                 NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- Data users asli (password MD5 — akan auto-upgrade ke bcrypt
-- saat masing-masing user melakukan login pertama kali)
-- ------------------------------------------------------------
-- Tabel password plaintext (HANYA untuk referensi dev, JANGAN disimpan di production):
-- admin        → MD5: 0192023a7bbd73250516f069df18b500
-- user         → MD5: 6ad14ba9986e3615423dfca256d04e3f
-- bababa       → MD5: 0822380e44fefbf5ffc25872f45b91e6
-- babaga       → MD5: c77c0c4473fb3a8fe9902c54358bd56a
-- pakfahrizal  → MD5: ecd839f217a127bd9f7ab7e5cb8a5649
-- ronysoeharto → MD5: 7822ffef1339279ec93971ee7cd8158e
-- pakrizal     → MD5: e1dd93a3776735b630792fb2b6fa3b3c
-- ------------------------------------------------------------

INSERT INTO `users` (`id`, `username`, `password`, `role`, `created_at`) VALUES
(1, 'admin',        '0192023a7bbd73250516f069df18b500', 'admin', '2026-02-28 03:37:48'),
(2, 'user',         '6ad14ba9986e3615423dfca256d04e3f', 'user',  '2026-02-28 03:37:48'),
(3, 'bababa',       '0822380e44fefbf5ffc25872f45b91e6', 'user',  '2026-02-28 06:18:03'),
(4, 'babaga',       'c77c0c4473fb3a8fe9902c54358bd56a', 'user',  '2026-03-01 17:03:55'),
(5, 'pakfahrizal',  'ecd839f217a127bd9f7ab7e5cb8a5649', 'user',  '2026-04-23 07:23:38'),
(6, 'ronysoeharto', '7822ffef1339279ec93971ee7cd8158e', 'user',  '2026-04-23 07:30:57'),
(7, 'pakrizal',     'e1dd93a3776735b630792fb2b6fa3b3c', 'user',  '2026-04-23 07:31:57');

ALTER TABLE `users` AUTO_INCREMENT = 8;

-- ============================================================
-- Tabel: `articles`
-- ============================================================

CREATE TABLE `articles` (
  `id`             int(11)      NOT NULL AUTO_INCREMENT,
  `title`          varchar(200) NOT NULL,
  `author_name`    varchar(100) DEFAULT NULL,
  `article_source` varchar(255) DEFAULT NULL,
  `content`        text         NOT NULL,
  `author_id`      int(11)      DEFAULT NULL,
  `created_at`     timestamp    NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `author_id` (`author_id`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `articles_ibfk_1`
    FOREIGN KEY (`author_id`) REFERENCES `users` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- Data artikel asli
-- ------------------------------------------------------------

INSERT INTO `articles` (`id`, `title`, `author_name`, `article_source`, `content`, `author_id`, `created_at`) VALUES
(1,
 'Bahaya Konsumsi Antibiotik Tanpa Resep Dokter',
 'dr.Rizal Fadli',
 'https://www.halodoc.com/artikel/bahaya-konsumsi-antibiotik-tanpa-resep-dokter',
 'Antibiotik merupakan jenis obat yang sering digunakan untuk mengatasi berbagai penyakit yang disebabkan karena infeksi bakteri. Biasanya, jika infeksi yang terjadi masih dalam kategori ringan, dokter tidak perlu meresepkan obat antibiotik.\r\n\r\nSementara untuk kasus infeksi bakteri yang sudah parah, dokter baru akan meresepkan penggunaan obat antibiotik. Kondisi lain yang membutuhkan obat antibiotik, yaitu orang-orang dengan kondisi imun tubuh yang lemah, contohnya seperti pengidap HIV atau kanker.\r\n\r\nHal yang perlu ditegaskan, antibiotik harus dikonsumsi berdasarkan resep dan anjuran dokter. Sebab, obat ini bisa menimbulkan berbagai efek samping bila digunakan secara sembarangan.\r\n\r\nDampak Konsumsi Antibiotik Tanpa Resep Dokter\r\n\r\nSupaya obat ini bisa bekerja lebih aman dan efektif, tentu ada pertimbangan dari dokter sebelum meresepkannya. Contohnya, kondisi medis pengidap, jenis antibiotik yang hendak diresepkan, jenis bakteri yang menjadi penyebab infeksi, hingga dosis dan durasi konsumsinya.\r\n\r\nSetiap jenis antibiotik akan memicu terjadinya efek samping yang berbeda. Efeknya bisa ringan atau justru lebih parah. Menggunakan resep dokter pun tak akan menghindarkan kamu dari efek samping saat mengkonsumsi obat ini, apalagi jika kamu mengonsumsinya tanpa pertimbangan dari pakarnya.\r\n\r\nMau tahu apa saja dampak bila obat ini digunakan secara asal? Berikut ulasannya!\r\n\r\n1. Mempengaruhi kerja otak\r\n\r\nAntibiotik menjadi jenis obat yang memiliki efek keras, tetapi tetap efektif untuk menekan sekaligus mematikan bakteri yang menjadi penyebab munculnya penyakit.\r\n\r\nMeski begitu, kamu tetap perlu tahu bahwa obat satu ini juga mempengaruhi kerja otak sebagai organ penting dalam tubuh. Sangat rentan terjadi depresi dan kecemasan berlebihan hanya dengan satu antibiotik.\r\n\r\n2. Risiko obesitas\r\n\r\nPenggunaan obat antibiotik pada anak tak hanya berdampak pada kenaikan berat badan saja, tetapi ada juga ada efek yang bisa terjadi dalam jangka panjang. Kondisi ini lantas turut dihubungkan dengan masalah diabetes tipe 2. Pasalnya, seseorang dengan kondisi kegemukan atau obesitas memang memiliki risiko lebih tinggi mengalami diabetes tipe 2.\r\n\r\n3. Masalah kesehatan pada usus\r\n\r\nAntibiotik memang efektif untuk membasmi bakteri. Namun, apabila obat ini dikonsumsi dengan dosis berlebihan, bakteri baik yang terdapat dalam tubuh pun akan ikut hilang.\r\n\r\nBeberapa orang mendapati kondisi perut menjadi lebih baik setelah minum antibiotik. Meski begitu, ada pula yang mengalami gangguan perut setelah mengkonsumsinya.\r\n\r\nDalam beberapa kasus, konsumsi antibiotik berlebihan akan meningkatkan risiko terjadinya masalah pada usus, misalnya penyakit Crohn, iritasi pada pencernaan, dan kolitis ulseratif.\r\n\r\n4. Terjadi resistensi antibiotik\r\n\r\nTerjadinya resistensi antibiotik atau kebal juga bisa terjadi saat kamu mengonsumsi antibiotik dalam dosis yang tidak sesuai dengan anjuran dokter. Jadi, pastikan kamu tidak mengkonsumsi obat ini tanpa resep ya. Dokter tentu lebih mengetahui jenis dan takaran obat sesuai dengan kondisimu.\r\n\r\nYuk, konsumsi antibiotik secara bijak agar infeksi bakteri teratasi tanpa menimbulkan efek samping pada tubuh. Bagi kamu yang memiliki masalah kesehatan, tanyakan saja langsung pada dokter ya!',
 1, '2026-02-28 04:01:24'),

(2,
 'Pentingnya Kesehatan Mental di Dunia Kerja dan Cara Mengelolanya',
 'Kusariani Adinda',
 'https://blog.skillacademy.com/pentingnya-kesehatan-mental',
 'Belakangan ini, isu kesehatan mental sering diperbincangkan oleh banyak kalangan, salah satunya di dunia kerja. Di lingkungan kerja sendiri, kesehatan mental yang stabil dan terjaga akan membuat para karyawan lebih bahagia dan produktif dalam bekerja.\r\n\r\nMenurut Celestinus Eigya Munthe selaku Direktur Kesehatan Jiwa dan NAPZA Kementerian Kesehatan, terjadi peningkatan gangguan kesehatan mental saat pandemi terjadi di tahun 2020, seperti 6,8% meningkatnya penderita gangguan kecemasan dan 8,5% mengalami depresi. Kemudian, Kementerian Kesehatan RI juga mencatat bahwa lebih dari 1.000 orang melakukan percobaan bunuh diri. Munculnya gangguan kesehatan jiwa ini berawal dari burnout yang berkepanjangan.\r\n\r\nDalam dunia kerja, hal ini dipicu oleh banyaknya tekanan dari perusahaan yang berimbas secara psikologis dan emosional pekerja. Kemudian, terjadilah gejala psikosomatis yang mempengaruhi kesehatan fisik tubuh. Misalnya, rasa sakit perut hingga mual dan muntah saat perjalanan kantor, sakit kepala menjelang tidur malam.\r\n\r\nTanda-tanda pekerja mengalami gangguan kesehatan mental\r\n\r\nMenurut Jasmine Patel dalam peoplescout.com, berikut adalah tanda-tanda karyawan yang sedang mengalami stres dan gangguan kesehatan pada mental mereka:\r\n\r\nRendahnya produktivitas dan motivasi untuk bekerja.\r\nMood karyawan mudah sekali berubah, seperti mudah nervous, mudah tersinggung, banyak diam.\r\nAbsen dalam beberapa hari karena karyawan butuh istirahat dan menghindari tekanan mental di kantor.\r\nEmosional pekerja tidak stabil, bisa sedih hingga berhari-hari.\r\nMenghindar dari interaksi sosial di kantor, baik dengan para atasan maupun rekan tim.\r\nSulit tidur karena memikirkan hal-hal yang akan terjadi esok hari.\r\n\r\nCara mengelola kesehatan mental yang terganggu\r\n\r\nMenurut seorang psikolog di bidang karir, rasa stres dan cemas yang mengganggu dapat dipulihkan secara perlahan dengan kesadaran diri sendiri. Berikut beberapa contoh aktivitas healing yang bisa dicoba:\r\n\r\nMembiasakan diri untuk hidup work life balance.\r\nOlahraga minimal 3 kali dalam seminggu untuk memperbaiki mood.\r\nMakan makanan yang sehat dan penuh gizi.\r\nLuangkan waktu untuk melakukan hobi yang kamu suka.\r\nMenjalin komunikasi positif dengan keluarga dan sahabat.\r\n\r\nCara perusahaan mengelola kesehatan mental karyawan\r\n\r\nSelain kesadaran diri, kesehatan mental juga perlu disadari oleh manajemen perusahaan. Beberapa langkah yang bisa dilakukan perusahaan:\r\n\r\nMembuat program asistensi karyawan sebagai wadah review kendala dan saran.\r\nMemberlakukan sistem kerja work from home atau hybrid dengan jam kerja fleksibel.\r\nMenyediakan jasa konsultasi bersama psikolog yang biayanya ditanggung perusahaan.\r\nMemberi fasilitas asuransi seperti BPJS Kesehatan dan BPJS Ketenagakerjaan.\r\nMenerima pengembalian dana berobat ke psikolog atau psikiater.',
 1, '2026-02-28 05:24:31'),

(3,
 'Tips Diet Sehat dan Bugar Menurut Dokter Gizi',
 'Tim RS Mitra Keluarga',
 'https://www.mitrakeluarga.com/artikel/tips-diet-sehat',
 'Punya resolusi dan target untuk capai body goals di tahun ini? Tentunya, Anda juga sudah merencanakan untuk diet menurunkan berat badan, bukan?\r\n\r\nBanyak dari Anda yang masih memiliki mindset jika ingin berat badan ideal harus mengonsumsi makanan dengan porsi sangat sedikit, melewati waktu makan dan menahan lapar, serta mengonsumsi pil atau suplemen diet. Akibatnya, tubuh Anda cepat lemah hingga jatuh sakit, dan fungsi organ tubuh Anda menjadi tidak normal.\r\n\r\nPadahal, diet yang sehat tentunya membuat Anda tetap bugar dan bebas lemas selama beraktivitas. Bagaimana caranya? Yuk, simak artikel berikut ini!\r\n\r\n1. Defisit kalori sesuai BMI\r\n\r\nUmumnya, jumlah total kalori yang dibakar setiap hari bagi pria minimal 2.500 kalori, sedangkan wanita minimal 2.000 kalori. Untuk menurunkan berat badan, defisit kalori menjadi kunci utama.\r\n\r\nUntuk mengetahui angka kalori defisit yang tepat, lebih akurat jika Anda berkonsultasi dengan dokter spesialis gizi klinik agar ditimbang dengan alat khusus yang menyesuaikan dengan body mass index (BMI).\r\n\r\n2. Batasi porsi dan jadwal makan\r\n\r\nApabila Anda belum begitu memahami perhitungan defisit kalori, secara sederhana Anda dapat membatasi porsi makan. Ganti piring ukuran standar dengan piring kecil, atau ambil setengah porsi dari sajian normal.\r\n\r\n3. Cukupi kebutuhan air\r\n\r\nKarena Anda ingin menurunkan berat badan dengan diet sehat, alihkan minuman manis mengandung gula tinggi dengan air mineral. Minimal 2 liter sehari, air mineral dapat menekan rasa nafsu makan berlebihan ketika diminum sebelum makan.\r\n\r\n4. Tidak ada salahnya untuk menikmati camilan\r\n\r\nMenyantap camilan saat diet tetap diperlukan. Namun, perlu diperhatikan komposisinya. Lebih baik membuat cemilan sendiri dengan bahan tinggi nutrisi seperti kacang-kacangan, yogurt, buah-buahan, dan madu. Kalori yang disarankan adalah 150 kalori saja.\r\n\r\n5. Aktif Berolahraga\r\n\r\nAda prinsip 80/20 dalam diet: penurunan berat badan terjadi karena 80% pola makan dan 20% dari olahraga serta aktivitas fisik. Selain mempercepat penurunan berat badan, olahraga memiliki fungsi lain bagi kesehatan fisik dan psikologi.\r\n\r\nApabila Anda mengalami kesulitan mengatur pola makan yang sesuai kondisi tubuh, konsultasikan segera ke dokter spesialis gizi terdekat.',
 1, '2026-02-28 06:35:48');

ALTER TABLE `articles` AUTO_INCREMENT = 6;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- ============================================================
-- CATATAN PENTING — PASSWORD AKUN YANG ADA
-- ============================================================
-- Semua password di atas tersimpan dalam format MD5.
-- Aplikasi akan otomatis upgrade ke bcrypt saat user login.
--
-- Untuk mengetahui password plaintext dari hash MD5 yang ada,
-- gunakan tool seperti: https://md5decrypt.net (HANYA untuk dev)
--
-- Atau untuk RESET password admin ke "admin123" (bcrypt),
-- jalankan perintah berikut setelah import:
--
--   UPDATE users
--   SET password = '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
--   WHERE username = 'admin';
--
-- Hash di atas adalah bcrypt dari string: "password"
-- Generate hash kamu sendiri via PHP CLI:
--   php -r "echo password_hash('admin123', PASSWORD_BCRYPT, ['cost'=>12]);"
-- ============================================================
