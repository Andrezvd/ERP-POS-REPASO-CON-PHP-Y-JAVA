12#!/usr/bin/perl
# ============================================
# SCRIPT PERL - CRUD COMPLETO ERP-POS
# Conexión a MySQL y operaciones CRUD
# ============================================

use strict;
use warnings;
use DBI;
use JSON;

# Configuración de la base de datos
my $db_host = "localhost";
my $db_name = "erp_pos";
my $db_user = "root";
my $db_pass = "";
my $db_port = "3306";

# Conectar a MySQL
sub conectar {
    my $dsn = "DBI:mysql:database=$db_name;host=$db_host;port=$db_port";
    my $dbh = DBI->connect($dsn, $db_user, $db_pass, {
        RaiseError => 1,
        PrintError => 0,
        mysql_enable_utf8mb4 => 1
    }) or die "Error de conexión: $DBI::errstr\n";
    
    print "✅ Conectado a MySQL: $db_name\n";
    return $dbh;
}

# ============================================
# OPERACIONES CRUD - PRODUCTOS
# ============================================

# Listar todos los productos
sub listar_productos {
    my ($dbh) = @_;
    
    my $sql = "SELECT p.id, p.codigo, p.nombre, p.precio_venta, p.stock_actual, 
                      c.nombre AS categoria
               FROM productos p
               LEFT JOIN categorias c ON p.categoria_id = c.id
               WHERE p.activo = 1
               ORDER BY p.nombre";
    
    my $sth = $dbh->prepare($sql);
    $sth->execute();
    
    print "\n" . "=" x 80 . "\n";
    printf "%-6s %-12s %-30s %-12s %-8s %-20s\n", 
           "ID", "Código", "Nombre", "Precio", "Stock", "Categoría";
    print "-" x 80 . "\n";
    
    while (my $row = $sth->fetchrow_hashref) {
        printf "%-6d %-12s %-30s %-12.2f %-8d %-20s\n",
               $row->{id}, $row->{codigo}, substr($row->{nombre}, 0, 28),
               $row->{precio_venta}, $row->{stock_actual}, 
               $row->{categoria} // "Sin categoría";
    }
    print "=" x 80 . "\n";
    
    $sth->finish;
}

# Obtener un producto por ID
sub obtener_producto {
    my ($dbh, $id) = @_;
    
    my $sql = "SELECT * FROM productos WHERE id = ?";
    my $sth = $dbh->prepare($sql);
    $sth->execute($id);
    
    my $producto = $sth->fetchrow_hashref;
    $sth->finish;
    
    return $producto;
}

# Crear un nuevo producto
sub crear_producto {
    my ($dbh, $datos) = @_;
    
    my $sql = "INSERT INTO productos (codigo, nombre, descripcion, precio_compra, 
                precio_venta, stock_actual, stock_minimo, categoria_id)
               VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    my $sth = $dbh->prepare($sql);
    $sth->execute(
        $datos->{codigo},
        $datos->{nombre},
        $datos->{descripcion} // "",
        $datos->{precio_compra} // 0,
        $datos->{precio_venta},
        $datos->{stock_actual} // 0,
        $datos->{stock_minimo} // 5,
        $datos->{categoria_id} // undef
    );
    
    my $id = $sth->{mysql_insertid};
    $sth->finish;
    
    print "✅ Producto creado con ID: $id\n";
    return $id;
}

# Actualizar un producto
sub actualizar_producto {
    my ($dbh, $id, $datos) = @_;
    
    my @campos;
    my @valores;
    
    foreach my $campo (keys %$datos) {
        push @campos, "$campo = ?";
        push @valores, $datos->{$campo};
    }
    
    push @valores, $id;
    my $sql = "UPDATE productos SET " . join(", ", @campos) . " WHERE id = ?";
    
    my $sth = $dbh->prepare($sql);
    $sth->execute(@valores);
    
    my $afectadas = $sth->rows;
    $sth->finish;
    
    print "✅ Producto ID $id actualizado. Filas afectadas: $afectadas\n";
    return $afectadas;
}

# Eliminar un producto (borrado lógico)
sub eliminar_producto {
    my ($dbh, $id) = @_;
    
    my $sql = "UPDATE productos SET activo = 0 WHERE id = ?";
    my $sth = $dbh->prepare($sql);
    $sth->execute($id);
    
    my $afectadas = $sth->rows;
    $sth->finish;
    
    print "✅ Producto ID $id eliminado (borrado lógico). Filas: $afectadas\n";
    return $afectadas;
}

# ============================================
# OPERACIONES CRUD - CLIENTES
# ============================================

sub listar_clientes {
    my ($dbh) = @_;
    
    my $sql = "SELECT c.id, c.documento, c.nombre, c.telefono, c.email, c.ciudad
               FROM clientes c WHERE c.activo = 1 ORDER BY c.nombre";
    
    my $sth = $dbh->prepare($sql);
    $sth->execute();
    
    print "\n" . "=" x 90 . "\n";
    printf "%-6s %-15s %-30s %-15s %-25s\n", 
           "ID", "Documento", "Nombre", "Teléfono", "Email";
    print "-" x 90 . "\n";
    
    while (my $row = $sth->fetchrow_hashref) {
        printf "%-6d %-15s %-30s %-15s %-25s\n",
               $row->{id}, $row->{documento}, substr($row->{nombre}, 0, 28),
               $row->{telefono} // "-", $row->{email} // "-";
    }
    print "=" x 90 . "\n";
    
    $sth->finish;
}

# ============================================
# REPORTES
# ============================================

sub reporte_stock_bajo {
    my ($dbh) = @_;
    
    my $sql = "SELECT p.codigo, p.nombre, p.stock_actual, p.stock_minimo,
                      (p.stock_minimo - p.stock_actual) AS faltante,
                      c.nombre AS categoria
               FROM productos p
               LEFT JOIN categorias c ON p.categoria_id = c.id
               WHERE p.activo = 1 AND p.stock_actual <= p.stock_minimo
               ORDER BY faltante DESC";
    
    my $sth = $dbh->prepare($sql);
    $sth->execute();
    
    print "\n⚠️  PRODUCTOS CON STOCK BAJO ⚠️\n";
    print "=" x 80 . "\n";
    printf "%-12s %-30s %-8s %-8s %-10s %-20s\n",
           "Código", "Nombre", "Stock", "Mínimo", "Faltante", "Categoría";
    print "-" x 80 . "\n";
    
    while (my $row = $sth->fetchrow_hashref) {
        printf "%-12s %-30s %-8d %-8d %-10d %-20s\n",
               $row->{codigo}, substr($row->{nombre}, 0, 28),
               $row->{stock_actual}, $row->{stock_minimo},
               $row->{faltante}, $row->{categoria} // "-";
    }
    print "=" x 80 . "\n";
    
    $sth->finish;
}

sub reporte_ventas_dia {
    my ($dbh) = @_;
    
    my $sql = "SELECT COUNT(*) AS total_ventas, 
                      COALESCE(SUM(total), 0) AS monto_total
               FROM ventas 
               WHERE DATE(created_at) = CURDATE() AND estado = 'completada'";
    
    my $sth = $dbh->prepare($sql);
    $sth->execute();
    my $row = $sth->fetchrow_hashref;
    
    print "\n📊 RESUMEN DE VENTAS DEL DÍA\n";
    print "=" x 40 . "\n";
    printf "%-20s: %d\n", "Total Ventas", $row->{total_ventas};
    printf "%-20s: \$%.2f\n", "Monto Total", $row->{monto_total};
    print "=" x 40 . "\n";
    
    $sth->finish;
}

# ============================================
# EXPORTAR A JSON
# ============================================

sub exportar_productos_json {
    my ($dbh) = @_;
    
    my $sql = "SELECT p.*, c.nombre AS categoria 
               FROM productos p 
               LEFT JOIN categorias c ON p.categoria_id = c.id 
               WHERE p.activo = 1";
    
    my $sth = $dbh->prepare($sql);
    $sth->execute();
    
    my @productos;
    while (my $row = $sth->fetchrow_hashref) {
        push @productos, $row;
    }
    $sth->finish;
    
    my $json = encode_json(\@productos);
    
    open(my $fh, '>', 'productos_export.json') or die "No se pudo crear el archivo: $!\n";
    print $fh $json;
    close($fh);
    
    print "✅ Productos exportados a productos_export.json\n";
}

# ============================================
# MENÚ PRINCIPAL
# ============================================

sub mostrar_menu {
    print "\n" . "=" x 50 . "\n";
    print "   🖥️  ERP-POS - SCRIPT PERL CRUD\n";
    print "=" x 50 . "\n";
    print "1. Listar productos\n";
    print "2. Crear producto\n";
    print "3. Actualizar producto\n";
    print "4. Eliminar producto\n";
    print "5. Listar clientes\n";
    print "6. Reporte stock bajo\n";
    print "7. Resumen ventas del día\n";
    print "8. Exportar productos a JSON\n";
    print "9. Salir\n";
    print "=" x 50 . "\n";
    print "Seleccione una opción: ";
}

# ============================================
# EJECUCIÓN PRINCIPAL
# ============================================

my $dbh = conectar();

while (1) {
    mostrar_menu();
    my $opcion = <STDIN>;
    chomp $opcion;
    
    last if $opcion eq '9';
    
    if ($opcion eq '1') {
        listar_productos($dbh);
    }
    elsif ($opcion eq '2') {
        print "Código: "; my $codigo = <STDIN>; chomp $codigo;
        print "Nombre: "; my $nombre = <STDIN>; chomp $nombre;
        print "Precio Venta: "; my $precio = <STDIN>; chomp $precio;
        print "Stock: "; my $stock = <STDIN>; chomp $stock;
        
        crear_producto($dbh, {
            codigo => $codigo,
            nombre => $nombre,
            precio_venta => $precio,
            stock_actual => $stock
        });
    }
    elsif ($opcion eq '3') {
        print "ID del producto: "; my $id = <STDIN>; chomp $id;
        my $prod = obtener_producto($dbh, $id);
        
        if ($prod) {
            print "Nuevo nombre (Enter para mantener '$$prod{nombre}'): ";
            my $nombre = <STDIN>; chomp $nombre;
            $nombre = $prod->{nombre} unless $nombre;
            
            print "Nuevo precio (Enter para mantener $$prod{precio_venta}): ";
            my $precio = <STDIN>; chomp $precio;
            $precio = $prod->{precio_venta} unless $precio;
            
            actualizar_producto($dbh, $id, {
                nombre => $nombre,
                precio_venta => $precio
            });
        } else {
            print "❌ Producto no encontrado\n";
        }
    }
    elsif ($opcion eq '4') {
        print "ID del producto a eliminar: "; my $id = <STDIN>; chomp $id;
        eliminar_producto($dbh, $id);
    }
    elsif ($opcion eq '5') {
        listar_clientes($dbh);
    }
    elsif ($opcion eq '6') {
        reporte_stock_bajo($dbh);
    }
    elsif ($opcion eq '7') {
        reporte_ventas_dia($dbh);
    }
    elsif ($opcion eq '8') {
        exportar_productos_json($dbh);
    }
    else {
        print "❌ Opción inválida\n";
    }
    
    print "\nPresione Enter para continuar...";
    <STDIN>;
}

$dbh->disconnect();
print "\n👋 ¡Hasta luego!\n";
