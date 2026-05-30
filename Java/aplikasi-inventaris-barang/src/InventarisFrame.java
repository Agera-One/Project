
import javax.swing.*;
import javax.swing.table.DefaultTableModel;
import java.awt.*;
import java.awt.event.*;
import java.sql.*;
import javax.swing.JOptionPane;

public class InventarisFrame extends JFrame {
    JTextField tfKode, tfNama, tfStok, tfHarga;
    JButton btnTambah, btnUpdate, btnHapus, btnClear;
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
            public void keyTyped(KeyEvent ke) {
                char c = ke.getKeyChar();
                String kode = tfKode.getText();

                if (kode.length() >= 10) {
                    ke.consume();
                }

                if (!Character.isLetterOrDigit(c)) {
                    ke.consume();
                }
            }
        });
        
        tfNama.addKeyListener(new KeyAdapter() {
            public void keyTyped(KeyEvent ke) {
                char c = ke.getKeyChar();
                String nama = tfNama.getText();

                if (nama.length() >= 100) {
                    ke.consume();
                }

                if (nama.isEmpty() && c == ' ') {
                    ke.consume();
                } else if (!nama.isEmpty() && nama.endsWith(" ") && c == ' ') {
                    ke.consume();
                } else if (!Character.isLetterOrDigit(c) && c != ' ') {
                    ke.consume();
                }
            }
        });
        
        tfStok.addKeyListener(new KeyAdapter() {
            public void keyTyped(KeyEvent ke) {
                char c = ke.getKeyChar();
                String stok = tfStok.getText();

                if (stok.length() >= 4) {
                    ke.consume();
                }

                if (!Character.isDigit(c)) {
                    ke.consume();
                } else if (stok.isEmpty() && c == '0') {
                    ke.consume();
                }
            }
        });
        
        tfHarga.addKeyListener(new KeyAdapter() {
            public void keyTyped(KeyEvent ke) {
                char c = ke.getKeyChar();
                String harga = tfHarga.getText();

                if (harga.length() >= 9) {
                    ke.consume();
                }

                if (!Character.isDigit(c)) {
                    ke.consume();
                } else if (harga.isEmpty() && c == '0') {
                    ke.consume();
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

        model = new DefaultTableModel() {
            @Override
            public boolean isCellEditable(int row, int column) {
                return false;
            }
        };

        model.addColumn("Kode");
        model.addColumn("Nama");
        model.addColumn("Stok");
        model.addColumn("Harga");

        table = new JTable(model);

        add(new JScrollPane(table), BorderLayout.CENTER);

        JPanel panelBtn = new JPanel();

        btnTambah = new JButton("Tambah");
        btnUpdate = new JButton("Update");
        btnHapus = new JButton("Hapus");
        btnClear = new JButton("Clear");

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
                int selectedRow = table.getSelectedRow();
                String kode = tfKode.getText();

                if (kode.isEmpty()) {
                    JOptionPane.showMessageDialog(null,
                            "Jika ingin menghapus data, tolong double click",
                            "Peringatan",
                            JOptionPane.WARNING_MESSAGE);
                } else {
                    int confirm = JOptionPane.showConfirmDialog(null,
                            "Hapus data ini?",
                            "Konfirmasi",
                            JOptionPane.YES_NO_OPTION);

                    if (confirm == JOptionPane.YES_OPTION) {
                        hapusData();
                    }
                }
            }
        });

        btnClear.addActionListener(new ActionListener() {
            @Override
            public void actionPerformed(ActionEvent e) {
                clearForm();
            }
        });

        table.addMouseListener(new MouseAdapter() {
            @Override
            public void mouseClicked(MouseEvent e) {
                if (e.getClickCount() == 2) {
                    int baris = table.getSelectedRow();

                    if (baris != -1) {
                        isiForm();
                    }
                }
            }
        });

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
            JOptionPane.showMessageDialog(this, "Data yang ditambahkan tidak valid ");
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
            JOptionPane.showMessageDialog(this, "Data yang diubah tidak valid ");
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
            JOptionPane.showMessageDialog(this, "Data yang dihapus tidak valid");
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
            JOptionPane.showMessageDialog(this, "Data yang ditampilkan tidak ada");
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
