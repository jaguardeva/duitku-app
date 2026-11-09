<?php

return [
    'locale' => [
        'updated' => 'Bahasa berhasil diperbarui.',
    ],
    'profile' => [
        'updated' => 'Informasi profil berhasil diperbarui.',
        'password_updated' => 'Kata sandi Anda berhasil diperbarui.',
    ],
    'auth' => [
        'suspended' => 'Akun Anda ditangguhkan. Silakan hubungi administrator.',
    ],
    'otp' => [
        'wait' => 'Silakan tunggu :seconds detik sebelum meminta kode baru.',
        'no_active_code' => 'Tidak ada kode verifikasi aktif. Silakan minta kode baru.',
        'expired' => 'Kode verifikasi telah kedaluwarsa. Silakan minta kode baru.',
        'attempts_exhausted' => 'Batas percobaan verifikasi telah habis. Silakan minta kode baru.',
        'verified' => 'Email Anda berhasil diverifikasi.',
        'incorrect_exhausted' => 'Kode salah. Batas percobaan habis. Silakan minta kode baru.',
        'incorrect_remaining' => 'Kode verifikasi salah. Sisa percobaan: :remaining.',
        'resent' => 'Kode verifikasi baru telah dikirimkan ke email Anda.',
    ],
    'sessions' => [
        'not_found' => 'Sesi tidak ditemukan atau sudah berakhir.',
        'current' => 'Tidak dapat mengeluarkan sesi perangkat yang sedang Anda gunakan saat ini.',
        'revoked' => 'Sesi perangkat berhasil dikeluarkan.',
        'others_revoked' => 'Semua sesi perangkat lain telah berhasil dikeluarkan dari akun Anda.',
        'all_revoked' => 'Anda telah berhasil keluar dari semua perangkat. Silakan masuk kembali.',
    ],
    'validation' => [
        'password_no_spaces' => 'Kata sandi tidak boleh mengandung spasi.',
    ],
    'wallet' => [
        'created' => 'Dompet ":name" berhasil dibuat.',
        'updated' => 'Dompet ":name" berhasil diperbarui.',
        'archived' => 'Dompet ":name" berhasil diarsipkan.',
        'restored' => 'Dompet ":name" berhasil diaktifkan kembali.',
        'deleted' => 'Dompet ":name" berhasil dihapus.',
        'cannot_delete' => 'Dompet yang memiliki riwayat transaksi atau transfer tidak dapat dihapus. Silakan gunakan fitur arsip.',
    ],
    'category' => [
        'created' => 'Kategori ":name" berhasil dibuat.',
        'updated' => 'Kategori ":name" berhasil diperbarui.',
        'archived' => 'Kategori ":name" berhasil diarsipkan.',
        'restored' => 'Kategori ":name" berhasil diaktifkan kembali.',
        'deleted' => 'Kategori ":name" berhasil dihapus.',
        'cannot_delete' => 'Kategori yang telah digunakan dalam transaksi atau anggaran tidak dapat dihapus. Silakan gunakan fitur arsip.',
        'invalid_budget' => 'Kategori anggaran harus berupa kategori pengeluaran yang valid.',
    ],
    'transaction' => [
        'wallet_invalid' => 'Dompet tidak valid atau sedang diarsipkan.',
        'wallet_missing' => 'Dompet tidak valid.',
        'category_invalid' => 'Kategori tidak sesuai dengan jenis transaksi atau sedang diarsipkan.',
        'category_mismatch' => 'Kategori tidak sesuai dengan jenis transaksi.',
        'income_created' => 'Pemasukan berhasil dicatat.',
        'expense_created' => 'Pengeluaran berhasil dicatat.',
        'updated' => 'Transaksi berhasil diperbarui.',
        'deleted' => 'Transaksi berhasil dihapus.',
    ],
    'transfer' => [
        'source_invalid' => 'Dompet asal tidak valid atau sedang diarsipkan.',
        'destination_invalid' => 'Dompet tujuan tidak valid atau sedang diarsipkan.',
        'currency_mismatch' => 'Transfer antar mata uang berbeda belum didukung. Kedua dompet harus bermata uang sama.',
        'insufficient_balance' => 'Saldo dompet asal tidak mencukupi untuk transfer ini.',
        'created' => 'Transfer antar-dompet berhasil dilakukan.',
        'deleted' => 'Catatan transfer berhasil dihapus.',
    ],
    'budget' => [
        'created' => 'Anggaran berhasil dibuat.',
        'updated' => 'Anggaran berhasil diperbarui.',
        'deleted' => 'Anggaran berhasil dihapus.',
    ],
    'admin' => [
        'cannot_suspend_self' => 'Anda tidak dapat menangguhkan akun administrator Anda sendiri.',
        'user_suspended' => 'Akun pengguna ":name" berhasil ditangguhkan.',
        'user_restored' => 'Akun pengguna ":name" berhasil diaktifkan kembali.',
        'system_category_created' => 'Kategori sistem ":name" berhasil ditambahkan.',
        'system_category_updated' => 'Kategori sistem ":name" berhasil diperbarui.',
        'version_updated' => 'Versi aplikasi berhasil diperbarui.',
    ],
];
