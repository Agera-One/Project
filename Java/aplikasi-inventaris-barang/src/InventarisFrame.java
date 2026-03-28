import javax.swing.*;
import javax.swing.table.DefaultTableModel;
import java.awt.*;
import java.sql.*;

public class InventarisFrame extends JFrame {

    JTextField tfKode, tfNama, tfStok, tfHarga;
    JTable table;
    DefaultTableModel model;

    public InventarisFrame() {

        setTitle("Inventaris Barang");
        setSize(600,400);
        setLocationRelativeTo(null);
        setDefaultCloseOperation(EXIT_ON_CLOSE);

        setLayout(new BorderLayout());

        JPanel form = new JPanel(new GridLayout(4,2));

        tfKode = new JTextField();
        tfNama = new JTextField();
        tfStok = new JTextField();
        tfHarga = new JTextField();

        form.add(new JLabel("Kode"));
        form.add(tfKode);

        form.add(new JLabel("Nama"));
        form.add(tfNama);

        form.add(new JLabel("Stok"));
        form.add(tfStok);

        form.add(new JLabel("Harga"));
        form.add(tfHarga);

        add(form, BorderLayout.NORTH);

        model = new DefaultTableModel(
                new String[]{"Kode","Nama","Stok","Harga"},0
        );

        table = new JTable(model);

        add(new JScrollPane(table), BorderLayout.CENTER);

        JPanel panelBtn = new JPanel();

        JButton btnTambah = new JButton("Tambah");
        JButton btnUpdate = new JButton("Update");
        JButton btnHapus = new JButton("Hapus");

        panelBtn.add(btnTambah);
        panelBtn.add(btnUpdate);
        panelBtn.add(btnHapus);

        add(panelBtn, BorderLayout.SOUTH);

        btnTambah.addActionListener(e -> tambahData());
        btnUpdate.addActionListener(e -> updateData());
        btnHapus.addActionListener(e -> hapusData());

        table.getSelectionModel().addListSelectionListener(e -> isiForm());

        loadTable();
    }

    void tambahData() {

        try {

            Connection conn = Koneksi.getConnection();

            String sql = "INSERT INTO barang VALUES(?,?,?,?)";

            PreparedStatement ps = conn.prepareStatement(sql);

            ps.setString(1, tfKode.getText());
            ps.setString(2, tfNama.getText());
            ps.setInt(3, Integer.parseInt(tfStok.getText()));
            ps.setDouble(4, Double.parseDouble(tfHarga.getText()));

            ps.executeUpdate();

            loadTable();
            clearForm();

        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    void updateData() {

        try {

            Connection conn = Koneksi.getConnection();

            String sql = "UPDATE barang SET nama=?,stok=?,harga=? WHERE kode=?";

            PreparedStatement ps = conn.prepareStatement(sql);

            ps.setString(1, tfNama.getText());
            ps.setInt(2, Integer.parseInt(tfStok.getText()));
            ps.setDouble(3, Double.parseDouble(tfHarga.getText()));
            ps.setString(4, tfKode.getText());

            ps.executeUpdate();

            loadTable();
            clearForm();

        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    void hapusData() {

        try {

            Connection conn = Koneksi.getConnection();

            String sql = "DELETE FROM barang WHERE kode=?";

            PreparedStatement ps = conn.prepareStatement(sql);

            ps.setString(1, tfKode.getText());

            ps.executeUpdate();

            loadTable();
            clearForm();

        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    void loadTable() {

        model.setRowCount(0);

        try {

            Connection conn = Koneksi.getConnection();

            Statement st = conn.createStatement();

            ResultSet rs = st.executeQuery("SELECT * FROM barang");

            while(rs.next()){

                model.addRow(new Object[]{
                        rs.getString("kode"),
                        rs.getString("nama"),
                        rs.getInt("stok"),
                        rs.getDouble("harga")
                });

            }

        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    void isiForm(){

        int row = table.getSelectedRow();

        if(row != -1){

            tfKode.setText(model.getValueAt(row,0).toString());
            tfNama.setText(model.getValueAt(row,1).toString());
            tfStok.setText(model.getValueAt(row,2).toString());
            tfHarga.setText(model.getValueAt(row,3).toString());

        }
    }

    void clearForm(){

        tfKode.setText("");
        tfNama.setText("");
        tfStok.setText("");
        tfHarga.setText("");
    }

}