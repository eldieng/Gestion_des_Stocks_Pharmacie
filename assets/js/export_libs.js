// Librairies pour l'export Excel et PDF
// Inclure ce fichier via <script src="/pharmacie/assets/js/export_libs.js"></script>

// CDN XLSX
var script1 = document.createElement('script');
script1.src = 'https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js';
document.head.appendChild(script1);

// CDN jsPDF
var script2 = document.createElement('script');
script2.src = 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js';
document.head.appendChild(script2);

// CDN jsPDF-AutoTable
var script3 = document.createElement('script');
script3.src = 'https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js';
document.head.appendChild(script3);
