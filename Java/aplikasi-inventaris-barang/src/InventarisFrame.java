import javax.swing.*;
import javax.swing.table.DefaultTableModel;
import java.awt.*;

public class InventarisFrame extends JFrame {

    private JTextField tfKode, tfNama, tfStok, tfHarga;
    private JTable table;
    private DefaultTableModel model;
    private BarangDAO dao = new BarangDAO();

    public InventarisFrame() {
        setTitle("Inventaris Barang - MySQL");
        setSize(600, 400);
        setDefaultCloseOperation(EXIT_ON_CLOSE);
        setLocationRelativeTo(null);
        init();
        loadTable();
    }

    private void init() {
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
                new Object[]{"Kode","Nama","Stok","Harga"},0);
        table = new JTable(model);
        add(new JScrollPane(table), BorderLayout.CENTER);

        JPanel btnPanel = new JPanel();
        JButton btnTambah = new JButton("Tambah");
        JButton btnUpdate = new JButton("Update");
        JButton btnHapus = new JButton("Hapus");

        btnPanel.add(btnTambah);
        btnPanel.add(btnUpdate);
        btnPanel.add(btnHapus);

        add(btnPanel, BorderLayout.SOUTH);

        btnTambah.addActionListener(e -> tambah());
        btnUpdate.addActionListener(e -> update());
        btnHapus.addActionListener(e -> hapus());

        table.getSelectionModel().addListSelectionListener(e -> isiForm());
    }

    private void tambah() {
        dao.insert(
                tfKode.getText(),
                tfNama.getText(),
                Integer.parseInt(tfStok.getText()),
                Double.parseDouble(tfHarga.getText())
        );
        loadTable();
        clear();
    }

    private void update() {
        dao.update(
                tfKode.getText(),
                tfNama.getText(),
                Integer.parseInt(tfStok.getText()),
                Double.parseDouble(tfHarga.getText())
        );
        loadTable();
        clear();
    }

    private void hapus() {
        dao.delete(tfKode.getText());
        loadTable();
        clear();
    }

    private void loadTable() {
        model.setRowCount(0);
        for (String[] row : dao.getAll()) {
            model.addRow(row);
        }
    }

    private void isiForm() {
        int row = table.getSelectedRow();
        if (row != -1) {
            tfKode.setText(model.getValueAt(row,0).toString());
            tfNama.setText(model.getValueAt(row,1).toString());
            tfStok.setText(model.getValueAt(row,2).toString());
            tfHarga.setText(model.getValueAt(row,3).toString());
        }
    }

    private void clear() {
        tfKode.setText("");
        tfNama.setText("");
        tfStok.setText("");
        tfHarga.setText("");
    }
}