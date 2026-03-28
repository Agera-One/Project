import java.sql.Connection;
import java.sql.DriverManager;

public class Koneksi {
    
    // konek ke database yang mana, pakai apa, dan di mana lokasinya
    private static final String URL = "jdbc:mysql://localhost:3306/aplikasi-menu-restoran";
    private static final String USER = "root";
    private static final String PASS = "";

    public static Connection getConnection() {
        try {
            // membuat koneksi dari aplikasi Java ke database
            return DriverManager.getConnection(URL, USER, PASS);
        } catch (Exception e) {
            e.printStackTrace();
            return null;
        }
    }
}