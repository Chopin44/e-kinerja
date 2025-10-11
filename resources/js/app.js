import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

import Swal from 'sweetalert2';

window.Swal = Swal;

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('form[data-confirm]').forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            Swal.fire({
                title: 'Konfirmasi Penghapusan Data',
                html: `
                    <p class="text-gray-700 text-sm leading-relaxed">
                        ${this.getAttribute('data-confirm') || 
                        'Apakah Anda yakin ingin menghapus data ini?'} 
                        <br><br>
                        <b>Catatan:</b> Tindakan ini tidak dapat dibatalkan setelah disetujui.
                    </p>
                `,
                icon: 'warning',
                showCancelButton: true,
                focusCancel: true,
                confirmButtonText: '<i class="fas fa-check-circle mr-1"></i> Ya, Hapus',
                cancelButtonText: '<i class="fas fa-times-circle mr-1"></i> Batal',
                customClass: {
                    popup: 'rounded-xl shadow-lg border border-gray-200',
                    title: 'text-lg font-semibold text-gray-800',
                    htmlContainer: 'text-gray-700 text-sm',
                    confirmButton: 'bg-red-600 hover:bg-red-700 text-white font-medium rounded-md px-4 py-2',
                    cancelButton: 'bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium rounded-md px-4 py-2'
                },
                buttonsStyling: false,
                reverseButtons: true
            }).then(result => {
                if (result.isConfirmed) {
                    this.submit();
                }
            });
        });
    });
});


import Chart from 'chart.js/auto';

document.addEventListener('DOMContentLoaded', () => {
    const el = document.getElementById('anggaranPieChart');
    if (!el) return;

    const totalPagu = parseFloat(el.dataset.totalPagu);
    const totalRealisasi = parseFloat(el.dataset.totalRealisasi);
    const totalSisa = totalPagu - totalRealisasi;

    new Chart(el, {
        type: 'doughnut',
        data: {
            labels: ['Realisasi', 'Sisa Anggaran'],
            datasets: [{
                data: [totalRealisasi, totalSisa],
                backgroundColor: [
                    '#16A34A', // hijau kontras (realisasi)
                    '#DC2626'  // merah tegas (sisa anggaran)
                ],
                borderColor: '#ffffff',
                borderWidth: 2,
                hoverOffset: 10,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%', // biar terlihat lebih elegan
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: '#374151', // abu gelap
                        font: {
                            size: 13,
                            family: "'Inter', sans-serif"
                        },
                        padding: 16
                    }
                },
                tooltip: {
                    backgroundColor: '#111827',
                    titleColor: '#fff',
                    bodyColor: '#d1d5db',
                    bodyFont: { size: 13 },
                    callbacks: {
                        label: function(context) {
                            const value = context.raw || 0;
                            return `Rp ${value.toLocaleString('id-ID')}`;
                        }
                    }
                }
            }
        }
    });
});



