import java.sql.Connection;                                          // Mengimpor kelas Connection untuk merepresentasikan koneksi ke database
import java.sql.DriverManager;                                       // Mengimpor kelas DriverManager untuk mengelola dan membuka driver database

public class Koneksi {                                               // Kelas yang bertugas menangani semua urusan koneksi ke database MySQL

    private static final String URL = "jdbc:mysql://localhost:3306/aplikasi_menu_restoran"; // Alamat server MySQL beserta nama database yang dituju
    private static final String USER = "root";                       // Nama pengguna untuk login ke database MySQL (default: root)
    private static final String PASS = "";                           // Kata sandi untuk login ke database MySQL (dikosongkan karena tidak ada password)

    public static Connection getConnection() {                       // Method statis untuk membuat dan mengembalikan objek koneksi ke database
        try {                                                        // Blok try-catch untuk menangani kemungkinan error saat koneksi
            return DriverManager.getConnection(URL, USER, PASS);     // Membuat koneksi ke database menggunakan URL, USER, dan PASS yang telah ditentukan
        } catch (Exception e) {                                      // Menangkap semua jenis exception yang mungkin terjadi saat proses koneksi
            e.printStackTrace();                                     // Mencetak detail error ke konsol agar programmer bisa mendiagnosis penyebabnya
            return null;                                             // Mengembalikan null sebagai tanda bahwa koneksi gagal dibuat
        }
    }
}
