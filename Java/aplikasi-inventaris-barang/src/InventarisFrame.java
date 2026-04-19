
import javax.swing.*;
import javax.swing.table.DefaultTableModel;
import java.awt.*;
import java.awt.event.*;
import java.sql.*;
import javax.swing.JOptionPane;

public class InventarisFrame extends JFrame {

    JTextField tfKode, tfNama, tfStok, tfHarga;
    JTable table;
    DefaultTableModel model;

    public InventarisFrame() {
        setTitle("Inventaris Barang");
        setSize(600, 400);
        setLocationRelativeTo(null);
        setDefaultCloseOperation(EXIT_ON_CLOSE);

        setLayout(new BorderLayout());

        JPanel form = new JPanel(new GridLayout(4, 2));

        tfKode = new JTextField();
        tfNama = new JTextField();
        tfStok = new JTextField();
        tfHarga = new JTextField();

        tfKode.addKeyListener(new KeyAdapter() {
            @Override
            public void keyTyped(KeyEvent e) {
                char c = e.getKeyChar();
                String kode = tfKode.getText();
                
                if (kode.length() >= 10) {
                    e.consume();
                }

                if (!Character.isLetterOrDigit(c)) {
                    e.consume();
                }
            }
        });

        tfNama.addKeyListener(new KeyAdapter() {
            @Override
            public void keyTyped(KeyEvent e) {
                char c = e.getKeyChar();
                String nama = tfNama.getText();
                
                if (nama.length() >= 100) {
                    e.consume();
                }

                if (nama.isEmpty() && c == ' ') {
                    e.consume();
                } else if (!nama.isEmpty() && nama.endsWith(" ") && c == ' ') {
                    e.consume();
                } else if (!Character.isLetterOrDigit(c) && c != ' ') {
                    e.consume();
                }
            }
        });

        tfStok.addKeyListener(new KeyAdapter() {
            @Override
            public void keyTyped(KeyEvent e) {
                char c = e.getKeyChar();
                String stok = tfStok.getText();

                if (stok.length() > 4) {
                    e.consume();
                }

                if (!Character.isDigit(c)) {
                    e.consume();
                } else if (tfStok.getText().isEmpty() && c == '0') {
                    e.consume();
                }
            }
        });

        tfHarga.addKeyListener(new KeyAdapter() {
            @Override
            public void keyTyped(KeyEvent e) {
                char c = e.getKeyChar();
                String harga = tfHarga.getText();

                if (harga.length() >= 9) {
                    e.consume();
                }

                if (!Character.isDigit(c)) {
                    e.consume();
                } else if (tfHarga.getText().isEmpty() && c == '0') {
                    e.consume();
                }
            }
        });

        form.add(new JLabel("Kode"));
        form.add(tfKode);

        form.add(new JLabel("Nama"));
        form.add(tfNama);

        form.add(new JLabel("Stok"));
        form.add(tfStok);

        form.add(new JLabel("Harga"));
        form.add(tfHarga);

        add(form, BorderLayout.NORTH);

        model = new DefaultTableModel(new String[]{"Kode", "Nama", "Stok", "Harga"}, 0);

        table = new JTable(model);

        add(new JScrollPane(table), BorderLayout.CENTER);

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

        btnTambah.addActionListener(new ActionListener() {
            @Override
            public void actionPerformed(ActionEvent e) {
                if (ValidasiInput()) {
                    tambahData();
                }
            }
        });

        btnUpdate.addActionListener(new ActionListener() {
            @Override
            public void actionPerformed(ActionEvent e) {
                if (ValidasiInput()) {
                    updateData();
                }
            }
        });

        btnHapus.addActionListener(new ActionListener() {
            @Override
            public void actionPerformed(ActionEvent e) {
                int confirm = JOptionPane.showConfirmDialog(null, "Hapus data ini?", "Konfirmasi", JOptionPane.YES_NO_OPTION);
                if (confirm == JOptionPane.YES_OPTION) {
                    hapusData();
                }
            }
        });

        btnClear.addActionListener(new ActionListener() {
            @Override
            public void actionPerformed(ActionEvent e) {
                clearForm();
            }
        });

        table.getSelectionModel().addListSelectionListener(e -> isiForm());

        loadTable();
    }

    boolean ValidasiInput() {
        String kode = tfKode.getText();
        String nama = tfNama.getText();
        String stok = tfStok.getText();
        String harga = tfHarga.getText();

        if (kode.isEmpty() || nama.trim().isEmpty()
                || stok.isEmpty() || harga.isEmpty()) {
            JOptionPane.showMessageDialog(this, "Form tidak boleh kosong");
            return false;
        }
        return true;
    }

    void tambahData() {
        try {
            Connection conn = Koneksi.getConnection();
            String sql = "INSERT INTO barang VALUES(?,?,?,?)";
            PreparedStatement ps = conn.prepareStatement(sql);

            ps.setString(1, tfKode.getText());
            ps.setString(2, tfNama.getText().trim());
            ps.setInt(3, Integer.parseInt(tfStok.getText()));
            ps.setInt(4, Integer.parseInt(tfHarga.getText()));
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

            ps.setString(1, tfNama.getText().trim());
            ps.setInt(2, Integer.parseInt(tfStok.getText()));
            ps.setInt(3, Integer.parseInt(tfHarga.getText()));
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

            while (rs.next()) {
                model.addRow(new Object[]{
                    rs.getString("kode"),
                    rs.getString("nama"),
                    rs.getInt("stok"),
                    rs.getInt("Harga")
                });
            }
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    void isiForm() {
        int row = table.getSelectedRow();
        if (row != -1) {
            tfKode.setText(model.getValueAt(row, 0).toString());
            tfNama.setText(model.getValueAt(row, 1).toString());
            tfStok.setText(model.getValueAt(row, 2).toString());
            tfHarga.setText(model.getValueAt(row, 3).toString());
        }
    }

    void clearForm() {
        tfKode.setText("");
        tfNama.setText("");
        tfStok.setText("");
        tfHarga.setText("");
    }
}
