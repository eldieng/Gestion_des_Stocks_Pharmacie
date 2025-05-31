// Chart.js dashboard charts for Fawsayni
// Requires Chart.js CDN in dashboard.php

document.addEventListener('DOMContentLoaded', function() {
    // Example: Ventes du mois (bar chart)
    if (document.getElementById('chartVentesMois')) {
        new Chart(document.getElementById('chartVentesMois').getContext('2d'), {
            type: 'bar',
            data: {
                labels: window.dashboardData.ventes_labels,
                datasets: [{
                    label: 'Ventes',
                    data: window.dashboardData.ventes_data,
                    backgroundColor: '#00bcd4',
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });
    }
    // Example: Achats du mois (line chart)
    if (document.getElementById('chartAchatsMois')) {
        new Chart(document.getElementById('chartAchatsMois').getContext('2d'), {
            type: 'line',
            data: {
                labels: window.dashboardData.achats_labels,
                datasets: [{
                    label: 'Achats',
                    data: window.dashboardData.achats_data,
                    borderColor: '#ff9800',
                    backgroundColor: 'rgba(255,152,0,0.2)',
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });
    }
});
