import './bootstrap';

import Alpine from 'alpinejs';
import { Chart, registerables } from 'chart.js';
import Swal from 'sweetalert2';
import { createIcons, icons } from 'lucide';

Chart.register(...registerables);

window.Alpine = Alpine;
window.Chart = Chart;
window.Swal = Swal;

// Global Toast helper
window.showToast = (icon, title) => {
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: icon,
        title: title,
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
    });
};

// Global confirm action
window.confirmAction = (title, text, confirmButtonText = 'Ya, Lanjutkan!') => {
    return Swal.fire({
        title: title,
        text: text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#10b981', // emerald-500
        cancelButtonColor: '#ef4444',  // red-500
        confirmButtonText: confirmButtonText,
        cancelButtonText: 'Batal',
        reverseButtons: true,
    });
};

document.addEventListener('DOMContentLoaded', () => {
    createIcons({ icons });
});

Alpine.start();
