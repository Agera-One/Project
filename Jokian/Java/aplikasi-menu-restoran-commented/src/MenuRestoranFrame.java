import javax.swing.*;                                                // Mengimpor semua komponen Swing (JFrame, JButton, JTable, dll) untuk membangun GUI
import javax.swing.table.DefaultTableModel;                          // Mengimpor DefaultTableModel untuk mengelola data yang ditampilkan dalam JTable
import java.awt.*;                                                   // Mengimpor semua komponen AWT (BorderLayout, GridLayout, dll) untuk tata letak tampilan
import java.sql.*;                                                   // Mengimpor semua kelas SQL (Connection, Statement, ResultSet, dll) untuk operasi database

public class MenuRestoranFrame extends JFrame {                      // Kelas jendela utama yang mewarisi JFrame sehingga berfungsi sebagai tampilan aplikasi

    JTextField tfId, tfNamaMenu, tfHarga;                            // Deklarasi field teks: tfId untuk ID, tfNamaMenu untuk nama menu, tfHarga untuk harga
    JTable table;                                                    // Komponen tabel untuk menampilkan daftar data menu dalam bentuk baris dan kolom
    DefaultTableModel model;                                         // Model yang menjadi sumber data (isi baris & kolom) untuk JTable

    public MenuRestoranFrame() {                                     // Constructor yang dipanggil saat objek jendela dibuat, berisi semua pengaturan tampilan
        setTitle("Aplikasi Data Menu Restoran");                     // Mengatur judul yang tampil di title bar jendela aplikasi
        setSize(700, 500);                                           // Mengatur ukuran jendela dengan lebar 700 piksel dan tinggi 500 piksel
        setLocationRelativeTo(null);                                 // Menempatkan jendela di tengah layar monitor
        setDefaultCloseOperation(EXIT_ON_CLOSE);                     // Mengatur agar program langsung berhenti saat tombol X (tutup) ditekan

        setLayout(new BorderLayout());                               // Mengatur layout jendela menjadi BorderLayout (NORTH, CENTER, SOUTH, dll)

        // Panel Form Input
        JPanel form = new JPanel(new GridLayout(3, 2, 10, 10));      // Membuat panel form dengan GridLayout 3 baris, 2 kolom, jarak antar komponen 10 piksel
        form.setBorder(BorderFactory.createEmptyBorder(10, 10, 10, 10)); // Menambahkan jarak (padding) 10 piksel di sekeliling panel form

        tfId = new JTextField();                                     // Membuat field teks untuk menampilkan ID menu secara otomatis dari database
        tfId.setEditable(false);                                     // Menonaktifkan kemampuan edit agar ID tidak bisa diubah manual oleh user

        tfNamaMenu = new JTextField();                               // Membuat field teks yang dapat diedit untuk memasukkan nama menu
        tfHarga = new JTextField();                                  // Membuat field teks yang dapat diedit untuk memasukkan harga menu

        form.add(new JLabel("ID"));                                  // Menambahkan label "ID" ke panel form sebagai keterangan untuk tfId
        form.add(tfId);                                              // Menambahkan field tfId ke panel form di sebelah kanan label "ID"
        form.add(new JLabel("Nama Menu"));                           // Menambahkan label "Nama Menu" ke panel form sebagai keterangan untuk tfNamaMenu
        form.add(tfNamaMenu);                                        // Menambahkan field tfNamaMenu ke panel form di sebelah kanan label "Nama Menu"
        form.add(new JLabel("Harga (Rp)"));                          // Menambahkan label "Harga (Rp)" ke panel form sebagai keterangan untuk tfHarga
        form.add(tfHarga);                                           // Menambahkan field tfHarga ke panel form di sebelah kanan label "Harga (Rp)"

        add(form, BorderLayout.NORTH);                               // Menempatkan panel form di bagian atas (NORTH) jendela utama

        // Tabel
        model = new DefaultTableModel(                               // Membuat model tabel dengan 3 kolom header dan 0 baris awal (kosong)
                new String[]{"ID", "Nama Menu", "Harga (Rp)"}, 0
        );
        table = new JTable(model);                                   // Membuat komponen JTable yang menggunakan model di atas sebagai sumber datanya
        add(new JScrollPane(table), BorderLayout.CENTER);            // Membungkus tabel dalam JScrollPane lalu menempatkannya di bagian tengah jendela

        // Panel Tombol
        JPanel panelBtn = new JPanel();                              // Membuat panel khusus untuk menampung tombol-tombol aksi CRUD
        JButton btnTambah = new JButton("Tambah");                   // Membuat tombol "Tambah" untuk menyimpan data menu baru ke database
        JButton btnUpdate = new JButton("Update");                   // Membuat tombol "Update" untuk memperbarui data menu yang sudah ada
        JButton btnHapus = new JButton("Hapus");                     // Membuat tombol "Hapus" untuk menghapus data menu yang dipilih dari database
        JButton btnClear = new JButton("Clear");                     // Membuat tombol "Clear" untuk mengosongkan semua field input form

        panelBtn.add(btnTambah);                                     // Menambahkan tombol Tambah ke panel tombol
        panelBtn.add(btnUpdate);                                     // Menambahkan tombol Update ke panel tombol
        panelBtn.add(btnHapus);                                      // Menambahkan tombol Hapus ke panel tombol
        panelBtn.add(btnClear);                                      // Menambahkan tombol Clear ke panel tombol

        add(panelBtn, BorderLayout.SOUTH);                           // Menempatkan panel tombol di bagian bawah (SOUTH) jendela utama

        // Event Listener
        btnTambah.addActionListener(e -> tambahData());              // Saat tombol Tambah diklik, method tambahData() akan dipanggil
        btnUpdate.addActionListener(e -> updateData());              // Saat tombol Update diklik, method updateData() akan dipanggil
        btnHapus.addActionListener(e -> hapusData());                // Saat tombol Hapus diklik, method hapusData() akan dipanggil
        btnClear.addActionListener(e -> clearForm());                // Saat tombol Clear diklik, method clearForm() akan dipanggil

        table.getSelectionModel().addListSelectionListener(e -> isiForm()); // Saat user memilih baris di tabel, method isiForm() otomatis dipanggil

        loadTable();                                                 // Memanggil loadTable() untuk menampilkan data menu dari database saat aplikasi pertama dibuka
    }

    void tambahData() {                                              // Method untuk menyimpan data menu baru yang diinput user ke dalam database
        try {                                                        // Blok try-catch untuk menangani error selama proses penyimpanan data
            Connection conn = Koneksi.getConnection();               // Memanggil getConnection() dari kelas Koneksi untuk mendapatkan koneksi ke database
            if (conn == null) return;                                // Jika koneksi gagal (null), hentikan eksekusi method

            String sql = "INSERT INTO menu (nama_menu, harga) VALUES (?, ?)"; // Query SQL INSERT dengan "?" sebagai placeholder untuk mencegah SQL Injection
            PreparedStatement ps = conn.prepareStatement(sql);       // Membuat PreparedStatement dari query SQL di atas

            ps.setString(1, tfNamaMenu.getText().trim());            // Mengisi placeholder pertama "?" dengan nilai nama menu dari form (trim untuk hapus spasi)
            ps.setInt(2, Integer.parseInt(tfHarga.getText().trim())); // Mengisi placeholder kedua "?" dengan nilai harga yang dikonversi ke bilangan bulat (int)

            ps.executeUpdate();                                      // Menjalankan query INSERT sehingga data benar-benar tersimpan ke database

            loadTable();                                             // Memperbarui tampilan tabel agar data baru langsung terlihat
            clearForm();                                             // Mengosongkan semua field form setelah data berhasil disimpan
            JOptionPane.showMessageDialog(this, "Menu berhasil ditambahkan!"); // Menampilkan dialog informasi bahwa penambahan data berhasil

        } catch (Exception e) {                                      // Menangkap semua jenis exception jika terjadi error saat proses tambah data
            JOptionPane.showMessageDialog(this, "Error: " + e.getMessage()); // Menampilkan dialog berisi pesan error agar user mengetahui apa yang salah
        }
    }

    void updateData() {                                              // Method untuk memperbarui data menu yang sudah ada berdasarkan ID yang dipilih user
        if (tfId.getText().isEmpty()) {                              // Memeriksa apakah field ID kosong (berarti user belum memilih data)
            JOptionPane.showMessageDialog(this, "Pilih menu yang akan diupdate!"); // Menampilkan peringatan agar user memilih data terlebih dahulu
            return;                                                  // Menghentikan eksekusi method jika tidak ada data yang dipilih
        }

        try {                                                        // Blok try-catch untuk menangani error selama proses update data
            Connection conn = Koneksi.getConnection();               // Memanggil getConnection() untuk mendapatkan objek koneksi ke database
            if (conn == null) return;                                // Jika koneksi gagal (null), hentikan eksekusi method

            String sql = "UPDATE menu SET nama_menu=?, harga=? WHERE id=?"; // Query SQL UPDATE untuk mengubah nama_menu dan harga berdasarkan id tertentu
            PreparedStatement ps = conn.prepareStatement(sql);       // Membuat PreparedStatement dari query SQL UPDATE di atas

            ps.setString(1, tfNamaMenu.getText().trim());            // Mengisi placeholder pertama "?" dengan nilai nama menu terbaru dari form
            ps.setInt(2, Integer.parseInt(tfHarga.getText().trim())); // Mengisi placeholder kedua "?" dengan nilai harga terbaru yang dikonversi ke int
            ps.setInt(3, Integer.parseInt(tfId.getText()));          // Mengisi placeholder ketiga "?" dengan nilai ID sebagai kunci pencarian data yang diubah

            ps.executeUpdate();                                      // Menjalankan query UPDATE ke database untuk menyimpan perubahan data

            loadTable();                                             // Memperbarui tampilan tabel agar menampilkan data yang sudah diubah
            clearForm();                                             // Mengosongkan semua field form setelah update berhasil
            JOptionPane.showMessageDialog(this, "Menu berhasil diupdate!"); // Menampilkan dialog informasi bahwa pembaruan data berhasil dilakukan

        } catch (Exception e) {                                      // Menangkap semua jenis exception jika terjadi error saat proses update
            JOptionPane.showMessageDialog(this, "Error: " + e.getMessage()); // Menampilkan pesan error kepada user melalui kotak dialog
        }
    }

    void hapusData() {                                               // Method untuk menghapus data menu yang dipilih user secara permanen dari database
        if (tfId.getText().isEmpty()) {                              // Memeriksa apakah field ID kosong (berarti user belum memilih data)
            JOptionPane.showMessageDialog(this, "Pilih menu yang akan dihapus!"); // Menampilkan peringatan bahwa user harus memilih data terlebih dahulu
            return;                                                  // Menghentikan eksekusi method jika tidak ada data yang dipilih
        }

        int konfirm = JOptionPane.showConfirmDialog(this,           // Menampilkan dialog konfirmasi dengan pilihan YES/NO sebelum data benar-benar dihapus
                "Yakin ingin menghapus menu ini?", "Konfirmasi", 
                JOptionPane.YES_NO_OPTION);

        if (konfirm == JOptionPane.YES_OPTION) {                    // Memeriksa apakah user menekan tombol YES pada dialog konfirmasi
            try {                                                    // Blok try-catch untuk menangani error selama proses penghapusan data
                Connection conn = Koneksi.getConnection();           // Memanggil getConnection() untuk mendapatkan objek koneksi ke database
                if (conn == null) return;                            // Jika koneksi gagal (null), hentikan eksekusi method

                String sql = "DELETE FROM menu WHERE id=?";         // Query SQL DELETE untuk menghapus satu baris data berdasarkan id tertentu
                PreparedStatement ps = conn.prepareStatement(sql);   // Membuat PreparedStatement dari query SQL DELETE di atas
                ps.setInt(1, Integer.parseInt(tfId.getText()));      // Mengisi placeholder "?" dengan nilai ID sebagai kunci data yang akan dihapus

                ps.executeUpdate();                                  // Menjalankan query DELETE ke database untuk benar-benar menghapus data

                loadTable();                                         // Memperbarui tampilan tabel agar data yang dihapus tidak lagi terlihat
                clearForm();                                         // Mengosongkan semua field form setelah penghapusan berhasil
                JOptionPane.showMessageDialog(this, "Menu berhasil dihapus!"); // Menampilkan dialog informasi bahwa data berhasil dihapus

            } catch (Exception e) {                                  // Menangkap semua jenis exception jika terjadi error saat proses hapus data
                JOptionPane.showMessageDialog(this, "Error: " + e.getMessage()); // Menampilkan pesan error kepada user melalui kotak dialog
            }
        }
    }

    void loadTable() {                                               // Method untuk mengambil semua data dari database dan menampilkannya ke JTable
        model.setRowCount(0);                                        // Mengosongkan (reset) semua baris tabel sebelum mengisi ulang dengan data terbaru

        try {                                                        // Blok try-catch untuk menangani error selama proses pengambilan data
            Connection conn = Koneksi.getConnection();               // Memanggil getConnection() untuk mendapatkan objek koneksi ke database
            if (conn == null) return;                                // Jika koneksi gagal (null), hentikan eksekusi method

            Statement st = conn.createStatement();                   // Membuat objek Statement untuk menjalankan query SQL statis (tanpa parameter)
            ResultSet rs = st.executeQuery("SELECT * FROM menu ORDER BY id"); // Menjalankan query SELECT untuk mengambil semua data menu diurutkan berdasarkan id

            while (rs.next()) {                                      // Melakukan perulangan untuk membaca setiap baris hasil query satu per satu
                model.addRow(new Object[]{                           // Menambahkan satu baris data baru ke model tabel dengan 3 kolom
                    rs.getInt("id"),                                 // Mengambil nilai kolom "id" dari hasil query sebagai bilangan bulat (int)
                    rs.getString("nama_menu"),                       // Mengambil nilai kolom "nama_menu" dari hasil query sebagai teks (String)
                    String.format("%,d", rs.getInt("harga"))   // format ribuan  // Mengambil dan memformat harga dengan pemisah ribuan (contoh: 15000 → "15.000")
                });
            }
        } catch (Exception e) {                                      // Menangkap semua jenis exception jika terjadi error saat load data dari database
            e.printStackTrace();                                     // Mencetak detail error ke konsol untuk keperluan debugging oleh programmer
        }
    }

    void isiForm() {                                                 // Method untuk mengisi field form secara otomatis berdasarkan baris yang dipilih di tabel
        int row = table.getSelectedRow();                            // Mendapatkan indeks baris yang sedang dipilih di JTable (-1 jika tidak ada yang dipilih)
        if (row != -1) {                                             // Memeriksa apakah ada baris yang dipilih (bukan -1)
            tfId.setText(model.getValueAt(row, 0).toString());       // Mengisi tfId dengan nilai kolom ke-0 (ID) dari baris yang dipilih
            tfNamaMenu.setText(model.getValueAt(row, 1).toString()); // Mengisi tfNamaMenu dengan nilai kolom ke-1 (Nama Menu) dari baris yang dipilih
            tfHarga.setText(model.getValueAt(row, 2).toString().replace(".", "")); // Mengisi tfHarga dengan harga yang sudah dihapus titik pemisah ribuannya
        }
    }

    void clearForm() {                                               // Method untuk mengosongkan semua field input form dan membatalkan pilihan di tabel
        tfId.setText("");                                            // Mengosongkan field tfId sehingga tidak ada ID yang ditampilkan
        tfNamaMenu.setText("");                                      // Mengosongkan field tfNamaMenu sehingga siap diisi data baru
        tfHarga.setText("");                                         // Mengosongkan field tfHarga sehingga siap diisi data baru
        table.clearSelection();                                      // Membatalkan semua pilihan baris yang aktif di JTable
    }
}
