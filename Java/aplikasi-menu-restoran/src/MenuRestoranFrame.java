import javax.swing.*;
import javax.swing.table.DefaultTableModel;
import java.awt.*;
import java.sql.*;

public class MenuRestoranFrame extends JFrame {

    JTextField tfId, tfNamaMenu, tfHarga;
    JTable table;
    DefaultTableModel model;

    public MenuRestoranFrame() {
        setTitle("Aplikasi Data Menu Restoran");
        setSize(700, 500);
        setLocationRelativeTo(null);
        setDefaultCloseOperation(EXIT_ON_CLOSE);

        setLayout(new BorderLayout());

        // Panel Form Input
        JPanel form = new JPanel(new GridLayout(3, 2, 10, 10));
        form.setBorder(BorderFactory.createEmptyBorder(10, 10, 10, 10));

        tfId = new JTextField();
        tfId.setEditable(false); // ID otomatis, tidak boleh diedit

        tfNamaMenu = new JTextField();
        tfHarga = new JTextField();

        form.add(new JLabel("ID"));
        form.add(tfId);
        form.add(new JLabel("Nama Menu"));
        form.add(tfNamaMenu);
        form.add(new JLabel("Harga (Rp)"));
        form.add(tfHarga);

        add(form, BorderLayout.NORTH);

        // Tabel
        model = new DefaultTableModel(
                new String[]{"ID", "Nama Menu", "Harga (Rp)"}, 0
        );
        table = new JTable(model);
        add(new JScrollPane(table), BorderLayout.CENTER);

        // Panel Tombol
        JPanel panelBtn = new JPanel();
        JButton btnTambah = new JButton("Tambah");
        JButton btnUpdate = new JButton("Update");
        JButton btnHapus = new JButton("Hapus");
        JButton btnClear = new JButton("Clear");

        panelBtn.add(btnTambah);
        panelBtn.add(btnUpdate);
        panelBtn.add(btnHapus);
        panelBtn.add(btnClear);

        add(panelBtn, BorderLayout.SOUTH);

        // Event Listener
        btnTambah.addActionListener(e -> tambahData());
        btnUpdate.addActionListener(e -> updateData());
        btnHapus.addActionListener(e -> hapusData());
        btnClear.addActionListener(e -> clearForm());

        // Klik tabel untuk isi form
        table.getSelectionModel().addListSelectionListener(e -> isiForm());

        loadTable();   // Load data saat aplikasi dibuka
    }

    // ==================== CRUD Methods ====================

    void tambahData() {
        try {
            Connection conn = Koneksi.getConnection();
            if (conn == null) return;

            String sql = "INSERT INTO menu (nama_menu, harga) VALUES (?, ?)";
            PreparedStatement ps = conn.prepareStatement(sql);

            ps.setString(1, tfNamaMenu.getText().trim());
            ps.setInt(2, Integer.parseInt(tfHarga.getText().trim()));

            ps.executeUpdate();

            loadTable();
            clearForm();
            JOptionPane.showMessageDialog(this, "Menu berhasil ditambahkan!");

        } catch (Exception e) {
            JOptionPane.showMessageDialog(this, "Error: " + e.getMessage());
        }
    }

    void updateData() {
        if (tfId.getText().isEmpty()) {
            JOptionPane.showMessageDialog(this, "Pilih menu yang akan diupdate!");
            return;
        }

        try {
            Connection conn = Koneksi.getConnection();
            if (conn == null) return;

            String sql = "UPDATE menu SET nama_menu=?, harga=? WHERE id=?";
            PreparedStatement ps = conn.prepareStatement(sql);

            ps.setString(1, tfNamaMenu.getText().trim());
            ps.setInt(2, Integer.parseInt(tfHarga.getText().trim()));
            ps.setInt(3, Integer.parseInt(tfId.getText()));

            ps.executeUpdate();

            loadTable();
            clearForm();
            JOptionPane.showMessageDialog(this, "Menu berhasil diupdate!");

        } catch (Exception e) {
            JOptionPane.showMessageDialog(this, "Error: " + e.getMessage());
        }
    }

    void hapusData() {
        if (tfId.getText().isEmpty()) {
            JOptionPane.showMessageDialog(this, "Pilih menu yang akan dihapus!");
            return;
        }

        int konfirm = JOptionPane.showConfirmDialog(this, 
                "Yakin ingin menghapus menu ini?", "Konfirmasi", 
                JOptionPane.YES_NO_OPTION);

        if (konfirm == JOptionPane.YES_OPTION) {
            try {
                Connection conn = Koneksi.getConnection();
                if (conn == null) return;

                String sql = "DELETE FROM menu WHERE id=?";
                PreparedStatement ps = conn.prepareStatement(sql);
                ps.setInt(1, Integer.parseInt(tfId.getText()));

                ps.executeUpdate();

                loadTable();
                clearForm();
                JOptionPane.showMessageDialog(this, "Menu berhasil dihapus!");

            } catch (Exception e) {
                JOptionPane.showMessageDialog(this, "Error: " + e.getMessage());
            }
        }
    }

    void loadTable() {
        model.setRowCount(0);

        try {
            Connection conn = Koneksi.getConnection();
            if (conn == null) return;

            Statement st = conn.createStatement();
            ResultSet rs = st.executeQuery("SELECT * FROM menu ORDER BY id");

            while (rs.next()) {
                model.addRow(new Object[]{
                    rs.getInt("id"),
                    rs.getString("nama_menu"),
                    String.format("%,d", rs.getInt("harga"))   // format ribuan
                });
            }
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    void isiForm() {
        int row = table.getSelectedRow();
        if (row != -1) {
            tfId.setText(model.getValueAt(row, 0).toString());
            tfNamaMenu.setText(model.getValueAt(row, 1).toString());
            tfHarga.setText(model.getValueAt(row, 2).toString().replace(".", ""));
        }
    }

    void clearForm() {
        tfId.setText("");
        tfNamaMenu.setText("");
        tfHarga.setText("");
        table.clearSelection();
    }

    public static void main(String[] args) {
        try {
            UIManager.setLookAndFeel(UIManager.getSystemLookAndFeelClassName());
        } catch (Exception e) {}

        SwingUtilities.invokeLater(() -> new MenuRestoranFrame().setVisible(true));
    }
}