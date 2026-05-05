#!/usr/bin/perl
# ============================================
# EJEMPLO BÁSICO DE PERL
# Sintaxis fundamental para la prueba técnica
# ============================================

use strict;
use warnings;
use feature 'say';

# ============================================
# VARIABLES EN PERL
# ============================================

say "=" x 50;
say "📝 VARIABLES EN PERL";
say "=" x 50;

# Escalares ($)
my $nombre = "Juan Pérez";
my $edad = 25;
my $precio = 15000.50;
my $activo = 1;

say "Nombre: $nombre";
say "Edad: $edad";
say "Precio: $precio";

# Arrays (@)
my @colores = ("Rojo", "Verde", "Azul", "Amarillo");
my @numeros = (10, 20, 30, 40, 50);

say "\n📋 Arrays:";
say "Primer color: $colores[0]";
say "Último color: $colores[-1]";
say "Total colores: " . scalar(@colores);

# Hashes (%) - Diccionarios
my %producto = (
    codigo => "PROD001",
    nombre => "Laptop HP",
    precio => 2500000,
    stock  => 10
);

say "\n📦 Hash (diccionario):";
say "Código: $producto{codigo}";
say "Nombre: $producto{nombre}";
say "Precio: $producto{precio}";

# ============================================
# ESTRUCTURAS DE CONTROL
# ============================================

say "\n" . "=" x 50;
say "🔀 ESTRUCTURAS DE CONTROL";
say "=" x 50;

# if/else
if ($edad >= 18) {
    say "✅ $nombre es mayor de edad";
} else {
    say "❌ $nombre es menor de edad";
}

# unless (lo contrario de if)
unless ($activo == 0) {
    say "✅ El producto está activo";
}

# for loop
say "\n📊 For loop (1 al 5):";
for (my $i = 1; $i <= 5; $i++) {
    say "  Iteración $i";
}

# foreach
say "\n🔄 Foreach (colores):";
foreach my $color (@colores) {
    say "  Color: $color";
}

# while
say "\n🔄 While (contando):";
my $contador = 1;
while ($contador <= 3) {
    say "  Contador: $contador";
    $contador++;
}

# ============================================
# FUNCIONES (SUBROUTINES)
# ============================================

say "\n" . "=" x 50;
say "🔧 FUNCIONES (SUBROUTINES)";
say "=" x 50;

sub saludar {
    my ($nombre, $idioma) = @_;
    
    if ($idioma eq "es") {
        return "¡Hola, $nombre!";
    } elsif ($idioma eq "en") {
        return "Hello, $nombre!";
    } else {
        return "Hi, $nombre!";
    }
}

sub calcular_iva {
    my ($monto, $porcentaje) = @_;
    $porcentaje //= 19; # Valor por defecto 19%
    return $monto * ($porcentaje / 100);
}

sub formatear_moneda {
    my ($cantidad) = @_;
    return sprintf("\$%.2f", $cantidad);
}

say saludar("Andrés", "es");
say saludar("John", "en");
say "IVA de 100000: " . formatear_moneda(calcular_iva(100000));
say "IVA 5% de 50000: " . formatear_moneda(calcular_iva(50000, 5));

# ============================================
# EXPRESIONES REGULARES
# ============================================

say "\n" . "=" x 50;
say "🔍 EXPRESIONES REGULARES";
say "=" x 50;

my $texto = "El producto PROD-001 cuesta \$25,000.00";
my $email = "usuario@empresa.com";
my $telefono = "300-123-4567";

# Match
if ($texto =~ m/PROD-\d{3}/) {
    say "✅ Código de producto encontrado en el texto";
}

# Capturar grupos
if ($texto =~ m/(PROD-\d{3})/) {
    say "  Código extraído: $1";
}

# Validar email
if ($email =~ m/^[\w\.-]+@[\w\.-]+\.\w+$/) {
    say "✅ Email válido: $email";
}

# Validar teléfono
if ($telefono =~ m/^\d{3}-\d{3}-\d{4}$/) {
    say "✅ Teléfono válido: $telefono";
}

# Sustitución
my $modificado = $texto;
$modificado =~ s/PROD-/ITEM-/g;
say "  Texto modificado: $modificado";

# ============================================
# OPERACIONES CON STRINGS
# ============================================

say "\n" . "=" x 50;
say "📝 OPERACIONES CON STRINGS";
say "=" x 50;

my $frase = "  hola mundo desde perl  ";
say "Original: '$frase'";
say "Mayúsculas: " . uc($frase);
say "Minúsculas: " . lc($frase);
say "Sin espacios: '" . trim($frase) . "'";
say "Longitud: " . length($frase);
say "Substring (0-10): " . substr($frase, 0, 10);
say "Inversa: " . reverse($frase);

sub trim {
    my $s = shift;
    $s =~ s/^\s+//;
    $s =~ s/\s+$//;
    return $s;
}

# ============================================
# ARCHIVOS
# ============================================

say "\n" . "=" x 50;
say "📁 OPERACIONES CON ARCHIVOS";
say "=" x 50;

# Escribir archivo
open(my $fh, '>', 'ejemplo_perl.txt') or die "Error: $!\n";
print $fh "=== Archivo creado desde Perl ===\n";
print $fh "Fecha: " . localtime() . "\n";
print $fh "Productos disponibles:\n";
foreach my $color (@colores) {
    print $fh "  - $color\n";
}
close($fh);
say "✅ Archivo 'ejemplo_perl.txt' creado";

# Leer archivo
open($fh, '<', 'ejemplo_perl.txt') or die "Error: $!\n";
say "\n📖 Contenido del archivo:";
while (my $linea = <$fh>) {
    print "  $linea";
}
close($fh);

# ============================================
# EJEMPLO PRÁCTICO: CARRITO DE COMPRAS
# ============================================

say "\n" . "=" x 50;
say "🛒 EJEMPLO PRÁCTICO: CARRITO DE COMPRAS";
say "=" x 50;

my @carrito = (
    { producto => "Laptop HP",     precio => 2500000, cantidad => 1 },
    { producto => "Mouse USB",     precio => 35000,  cantidad => 2 },
    { producto => "Teclado RGB",   precio => 85000,  cantidad => 1 },
);

my $subtotal = 0;
say "\nProductos en carrito:";
printf "%-20s %-10s %-10s %-12s\n", "Producto", "Precio", "Cant", "Subtotal";
say "-" x 55;

foreach my $item (@carrito) {
    my $item_subtotal = $item->{precio} * $item->{cantidad};
    $subtotal += $item_subtotal;
    printf "%-20s %-10s %-10d %-12s\n",
           $item->{producto},
           formatear_moneda($item->{precio}),
           $item->{cantidad},
           formatear_moneda($item_subtotal);
}

my $iva = calcular_iva($subtotal);
my $total = $subtotal + $iva;

say "-" x 55;
say "Subtotal: " . formatear_moneda($subtotal);
say "IVA 19%:  " . formatear_moneda($iva);
say "TOTAL:    " . formatear_moneda($total);

say "\n" . "=" x 50;
say "✅ FIN DEL EJEMPLO PERL";
say "=" x 50;
