#!/bin/bash
# Subir wp-content al servidor

cd /Users/strega/Desktop/Aplicaciones/grupovecsa-diseño/vecsaboutique

echo "Subiendo wp-content al servidor..."
sftp -i ./vecsa_id_rsa vecsaboutique@184.168.21.174 <<EOF
cd public_html
put -r wp-content
bye
EOF

echo "Subida completada"
