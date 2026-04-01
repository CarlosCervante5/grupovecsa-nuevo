#!/bin/bash
# Descargar solo wp-content del servidor

cd /Users/strega/Desktop/Aplicaciones/grupovecsa-diseño/vecsaboutique

echo "Conectando al servidor..."
sftp -i ./vecsa_id_rsa vecsaboutique@184.168.21.174 <<EOF
cd public_html
get -r wp-content
bye
EOF

echo "Descarga completada"
