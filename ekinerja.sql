-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 06, 2025 at 03:51 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ekinerja`
--

-- --------------------------------------------------------

--
-- Table structure for table `bidangs`
--

CREATE TABLE `bidangs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama` varchar(255) NOT NULL,
  `kode` varchar(10) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `kepala_bidang` varchar(255) DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bidangs`
--

INSERT INTO `bidangs` (`id`, `nama`, `kode`, `deskripsi`, `kepala_bidang`, `icon`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Sekretariat', 'SEKT', 'Bidang Administrasi dan Kesekretariatan', 'Irson Morres Nurbela', 'fas fa-cogs', 1, '2025-09-27 11:02:38', '2025-09-27 11:02:38'),
(2, 'Bidang Pemuda', 'INFO', 'Bidang Kepemudaan', 'Ari Arifyanto', 'fas fa-network-wired', 1, '2025-09-27 11:02:38', '2025-09-27 11:02:38'),
(3, 'Bidang Olahraga', 'KOMM', 'Bidang Olahraga', 'Maharani', 'fas fa-broadcast-tower', 1, '2025-09-27 11:02:38', '2025-09-27 11:02:38'),
(4, 'Bidang Pariwisata', 'STAT', 'Bidang Pariwisata', '-', 'fas fa-chart-line', 1, '2025-09-27 11:02:38', '2025-09-27 11:02:38');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('laravel-cache-spatie.permission.cache', 'a:3:{s:5:\"alias\";a:0:{}s:11:\"permissions\";a:0:{}s:5:\"roles\";a:0:{}}', 1759759511);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dokumens`
--

CREATE TABLE `dokumens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `realisasi_id` bigint(20) UNSIGNED NOT NULL,
  `nama_file` varchar(255) NOT NULL,
  `nama_asli` varchar(255) NOT NULL,
  `path` varchar(255) NOT NULL,
  `mime_type` varchar(255) NOT NULL,
  `size` int(11) NOT NULL,
  `jenis` enum('foto','laporan','kwitansi','lainnya') NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `dokumens`
--

INSERT INTO `dokumens` (`id`, `realisasi_id`, `nama_file`, `nama_asli`, `path`, `mime_type`, `size`, `jenis`, `created_at`, `updated_at`) VALUES
(1, 1, '1759325668_persona 5.png', 'persona 5.png', 'dokumen/1759325668_persona 5.png', 'image/png', 222923, 'foto', '2025-10-01 06:34:28', '2025-10-01 06:34:28'),
(2, 2, '1759330035_Bahan Tayang Latsar Agenda 4.pdf', 'Bahan Tayang Latsar Agenda 4.pdf', 'dokumen/1759330035_Bahan Tayang Latsar Agenda 4.pdf', 'application/pdf', 4205417, 'laporan', '2025-10-01 07:47:15', '2025-10-01 07:47:15');

-- --------------------------------------------------------

--
-- Table structure for table `evaluasis`
--

CREATE TABLE `evaluasis` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `kegiatan_id` bigint(20) UNSIGNED NOT NULL,
  `evaluator_id` bigint(20) UNSIGNED NOT NULL,
  `status_evaluasi` enum('on_track','terlambat','tidak_sesuai') NOT NULL,
  `catatan_evaluasi` text NOT NULL,
  `rekomendasi` text DEFAULT NULL,
  `tanggal_evaluasi` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `evaluasis`
--

INSERT INTO `evaluasis` (`id`, `kegiatan_id`, `evaluator_id`, `status_evaluasi`, `catatan_evaluasi`, `rekomendasi`, `tanggal_evaluasi`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 'on_track', 'asaasasasasasasas', NULL, '2025-10-05', '2025-10-05 07:05:19', '2025-10-05 07:05:19');

-- --------------------------------------------------------

--
-- Table structure for table `kegiatans`
--

CREATE TABLE `kegiatans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama` varchar(255) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `bidang_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `kategori` enum('belanja_langsung','belanja_operasional','program_prioritas','kegiatan_rutin') NOT NULL,
  `periode_type` enum('tahunan','bulanan','triwulan') NOT NULL,
  `target_fisik` decimal(5,2) NOT NULL,
  `target_anggaran` decimal(15,2) NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date NOT NULL,
  `tahun` year(4) NOT NULL,
  `status` enum('draft','aktif','selesai','dibatalkan') NOT NULL DEFAULT 'draft',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kegiatans`
--

INSERT INTO `kegiatans` (`id`, `nama`, `deskripsi`, `bidang_id`, `user_id`, `kategori`, `periode_type`, `target_fisik`, `target_anggaran`, `tanggal_mulai`, `tanggal_selesai`, `tahun`, `status`, `created_at`, `updated_at`) VALUES
(2, 'Pembinaan Atlet Profesional', 'Melakukan pembinaan olharaga tentang atlet profesional', 2, 2, 'belanja_langsung', 'triwulan', 1.00, 6000000.00, '2025-09-20', '2025-09-30', '2025', 'aktif', '2025-09-28 07:49:56', '2025-09-28 08:18:37'),
(3, 'sumanto', 'adad', 2, 4, 'belanja_langsung', 'triwulan', 3.00, 4.00, '2025-09-25', '2025-09-30', '2025', 'draft', '2025-09-28 08:12:51', '2025-09-28 08:12:51'),
(4, 'Koordinasi dan sinkronisasi penyediaan prasarana olahraga melalui perencanaan, pengadaan, pemanfaatan, pemeliharaan, dan pengawasan Prasarana Olahraga di tingkat kabupaten/kota', 'Pembinaan dan Pengembangan Olahraga Pendidikan pada Jenjang Pendidikan yang Menjadi Kewenangan Daerah Kabupaten/Kota', 3, 4, 'belanja_langsung', 'triwulan', 5.00, 2332500000.00, '2025-10-04', '2025-10-31', '2025', 'aktif', '2025-10-01 07:45:06', '2025-10-01 07:45:22');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2025_09_27_140038_create_bidangs_table', 1),
(2, '2025_09_27_140039_create_users_table', 1),
(3, '2025_09_27_140120_create_kegiatans_table', 1),
(4, '2025_09_27_140139_create_realisasis_table', 1),
(5, '2025_09_27_140156_create_dokumens_table', 1),
(6, '2025_09_27_140210_create_evaluasis_table', 1),
(7, '2025_09_27_175420_create_sessions_table', 2),
(8, '2025_09_27_175741_create_cache_table', 3),
(9, '2025_09_28_143542_create_permission_tables', 4);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 1);

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `realisasis`
--

CREATE TABLE `realisasis` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `kegiatan_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `realisasi_fisik` decimal(5,2) NOT NULL,
  `realisasi_anggaran` decimal(15,2) NOT NULL,
  `tanggal_realisasi` date NOT NULL,
  `lokasi` varchar(255) DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `status` enum('draft','submitted','approved','rejected') NOT NULL DEFAULT 'draft',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `realisasis`
--

INSERT INTO `realisasis` (`id`, `kegiatan_id`, `user_id`, `realisasi_fisik`, `realisasi_anggaran`, `tanggal_realisasi`, `lokasi`, `catatan`, `status`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 5.00, 200000.00, '2025-10-25', 'Linggoasri', 'Ejiat ah', 'submitted', '2025-10-01 06:34:28', '2025-10-01 06:34:28'),
(2, 4, 1, 5.00, 122183200.00, '2025-10-25', 'Semarang', NULL, 'submitted', '2025-10-01 07:47:15', '2025-10-01 07:47:15');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'web', '2025-10-05 07:03:58', '2025-10-05 07:03:58'),
(2, 'staf', 'web', '2025-10-05 07:03:58', '2025-10-05 07:03:58');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('vSX03uwkNSMwVMcTzrjYHOfeLQwJ3VCim3to3qKD', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiTjRicDhGSlVYbVh1UGVaVHQxSUM3NjROM3hRSWM2U2VuUDdqdXhUOSI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjI5OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvbGFwb3JhbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7fQ==', 1759678703);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `nip` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `bidang_id` bigint(20) UNSIGNED NOT NULL,
  `role` enum('admin','staf') NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `nip`, `phone`, `bidang_id`, `role`, `email_verified_at`, `password`, `is_active`, `last_login_at`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Admin System', 'admin@kominfo.go.id', '198001012005011001', '081234567890', 1, 'admin', NULL, '$2y$12$K2TXGWJqisBv46UBfGv1VemD00ce7WsMoQV3KNQs0MM0fhV.ANi/W', 1, NULL, NULL, '2025-09-27 11:02:41', '2025-09-27 11:02:41'),
(2, 'Ahmad Pratama', 'ahmad.p@kominfo.go.id', '198502152010011002', '081234567891', 2, 'staf', NULL, '$2y$12$B/Wp8.LrpXc26KoeAGhac.4rqcjGCXdSBGAkm0ifOi5X0Ialgd9oe', 1, NULL, NULL, '2025-09-27 11:02:41', '2025-09-27 11:02:41'),
(3, 'Siti Nurjannah', 'siti.n@kominfo.go.id', '198703202012012001', '081234567940', 1, 'staf', NULL, '$2y$12$zNZVEjIF8Xre24YDu7WJYuAJBRUIOUeXtn/JtrGmm700AdK.gMHXK', 1, NULL, NULL, '2025-09-27 11:02:41', '2025-09-27 11:02:41'),
(4, 'Rudi Hermawan', 'rudi.h@kominfo.go.id', '198904252015011001', '081234567493', 3, 'staf', NULL, '$2y$12$XLWY0Q6EHLPt9Bf7ksIJROFJLZTKt7IC51BADhOQMcuqiT1fECF.i', 1, NULL, NULL, '2025-09-27 11:02:41', '2025-09-27 11:02:41'),
(5, 'Lisa Andriani', 'lisa.a@kominfo.go.id', '199001102018012001', '081234567276', 4, 'staf', NULL, '$2y$12$fIWoa8Bf4vWzwOMY0.C7zeEq.LgMJUS6TPFJE9oj5RUpseGaU7XoG', 1, NULL, NULL, '2025-09-27 11:02:41', '2025-09-27 11:02:41');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bidangs`
--
ALTER TABLE `bidangs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `dokumens`
--
ALTER TABLE `dokumens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dokumens_realisasi_id_foreign` (`realisasi_id`);

--
-- Indexes for table `evaluasis`
--
ALTER TABLE `evaluasis`
  ADD PRIMARY KEY (`id`),
  ADD KEY `evaluasis_kegiatan_id_foreign` (`kegiatan_id`),
  ADD KEY `evaluasis_evaluator_id_foreign` (`evaluator_id`);

--
-- Indexes for table `kegiatans`
--
ALTER TABLE `kegiatans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kegiatans_bidang_id_foreign` (`bidang_id`),
  ADD KEY `kegiatans_user_id_foreign` (`user_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `realisasis`
--
ALTER TABLE `realisasis`
  ADD PRIMARY KEY (`id`),
  ADD KEY `realisasis_kegiatan_id_foreign` (`kegiatan_id`),
  ADD KEY `realisasis_user_id_foreign` (`user_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_bidang_id_foreign` (`bidang_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bidangs`
--
ALTER TABLE `bidangs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `dokumens`
--
ALTER TABLE `dokumens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `evaluasis`
--
ALTER TABLE `evaluasis`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `kegiatans`
--
ALTER TABLE `kegiatans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `realisasis`
--
ALTER TABLE `realisasis`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `dokumens`
--
ALTER TABLE `dokumens`
  ADD CONSTRAINT `dokumens_realisasi_id_foreign` FOREIGN KEY (`realisasi_id`) REFERENCES `realisasis` (`id`);

--
-- Constraints for table `evaluasis`
--
ALTER TABLE `evaluasis`
  ADD CONSTRAINT `evaluasis_evaluator_id_foreign` FOREIGN KEY (`evaluator_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `evaluasis_kegiatan_id_foreign` FOREIGN KEY (`kegiatan_id`) REFERENCES `kegiatans` (`id`);

--
-- Constraints for table `kegiatans`
--
ALTER TABLE `kegiatans`
  ADD CONSTRAINT `kegiatans_bidang_id_foreign` FOREIGN KEY (`bidang_id`) REFERENCES `bidangs` (`id`),
  ADD CONSTRAINT `kegiatans_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `realisasis`
--
ALTER TABLE `realisasis`
  ADD CONSTRAINT `realisasis_kegiatan_id_foreign` FOREIGN KEY (`kegiatan_id`) REFERENCES `kegiatans` (`id`),
  ADD CONSTRAINT `realisasis_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_bidang_id_foreign` FOREIGN KEY (`bidang_id`) REFERENCES `bidangs` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
