import java.sql.*;
import java.util.ArrayList;

public class BarangDAO {

    public void insert(String kode, String nama, int stok, double harga) {
        String sql = "INSERT INTO barang VALUES (?, ?, ?, ?)";
        try (Connection conn = Koneksi.getConnection();
             PreparedStatement ps = conn.prepareStatement(sql)) {

            ps.setString(1, kode);
            ps.setString(2, nama);
            ps.setInt(3, stok);
            ps.setDouble(4, harga);
            ps.executeUpdate();

        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    public void update(String kode, String nama, int stok, double harga) {
        String sql = "UPDATE barang SET nama=?, stok=?, harga=? WHERE kode=?";
        try (Connection conn = Koneksi.getConnection();
             PreparedStatement ps = conn.prepareStatement(sql)) {

            ps.setString(1, nama);
            ps.setInt(2, stok);
            ps.setDouble(3, harga);
            ps.setString(4, kode);
            ps.executeUpdate();

        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    public void delete(String kode) {
        String sql = "DELETE FROM barang WHERE kode=?";
        try (Connection conn = Koneksi.getConnection();
             PreparedStatement ps = conn.prepareStatement(sql)) {

            ps.setString(1, kode);
            ps.executeUpdate();

        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    public ArrayList<String[]> getAll() {
        ArrayList<String[]> list = new ArrayList<>();
        String sql = "SELECT * FROM barang";

        try (Connection conn = Koneksi.getConnection();
             Statement st = conn.createStatement();
             ResultSet rs = st.executeQuery(sql)) {

            while (rs.next()) {
                list.add(new String[]{
                        rs.getString("kode"),
                        rs.getString("nama"),
                        String.valueOf(rs.getInt("stok")),
                        String.valueOf(rs.getDouble("harga"))
                });
            }

        } catch (Exception e) {
            e.printStackTrace();
        }
        return list;
    }
}