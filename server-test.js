const express = require('express');
const app = express();
const port = 3000;

// Middleware para parsear JSON
app.use(express.json());

// Endpoint para recibir facturas
app.post('/facturas/recibir', (req, res) => {
    console.log('\n✅ FACTURA RECIBIDA:');
    console.log('=====================================');
    console.log('ID:', req.body.data.id);
    console.log('Número:', req.body.data.numero_factura);
    console.log('Fecha:', req.body.data.fecha_emision);
    console.log('Total:', req.body.data.total);
    console.log('Cliente:', req.body.data.cliente.nombre);
    console.log('Email:', req.body.data.cliente.email);
    console.log('Detalles:', req.body.data.detalles.length, 'producto(s)');
    console.log('=====================================');
    
    // Imprimir detalles de productos
    req.body.data.detalles.forEach((detalle, index) => {
        console.log(`  Producto ${index + 1}:`, detalle.producto.nombre);
        console.log(`    Cantidad:`, detalle.cantidad);
        console.log(`    Precio:`, detalle.precio_unitario);
        console.log(`    Subtotal:`, detalle.subtotal);
    });
    console.log('=====================================\n');
    
    // Responder con éxito
    res.status(200).json({
        success: true,
        message: 'Factura recibida correctamente',
        factura_id: req.body.data.id
    });
});

// Endpoint de prueba
app.get('/', (req, res) => {
    res.json({
        status: 'OK',
        message: 'Servidor de prueba de facturas funcionando',
        endpoint: '/facturas/recibir'
    });
});

app.listen(port, () => {
    console.log(`🚀 Servidor de prueba corriendo en http://localhost:${port}`);
    console.log(`📥 Esperando facturas en http://localhost:${port}/facturas/recibir`);
});
