<?php

if (!function_exists('mix_vite_path')) {
    /**
     * Generate correct path for Vite-built asset (CSS/JS)
     */
    function mix_vite_path($path)
    {
        $manifestPath = public_path('build/manifest.json');

        // Jika file manifest tidak ada (misal belum build)
        if (!file_exists($manifestPath)) {
            return 'resources/' . ltrim($path, '/');
        }

        $manifest = json_decode(file_get_contents($manifestPath), true);

        // Pastikan path diawali dengan 'resources/'
        $lookupPath = str_starts_with($path, 'resources/')
            ? $path
            : 'resources/' . ltrim($path, '/');

        if (isset($manifest[$lookupPath])) {
            return 'build/' . $manifest[$lookupPath]['file'];
        }

        // fallback
        return 'resources/' . ltrim($path, '/');
    }
}
