#!/usr/bin/env perl
use strict;
use warnings;

# FizzBuzz en Perl: imprime Fizz/Buzz/FizzBuzz para 1..100 por defecto.
# Uso: perl fizzbuzz.pl [fin]  o  perl fizzbuzz.pl [inicio] [fin]

my $start = 1;
my $end   = 100;
if (@ARGV == 1) {
	$end = $ARGV[0];
} elsif (@ARGV >= 2) {
	$start = $ARGV[0];
	$end   = $ARGV[1];
}

for my $i ($start .. $end) {
	if ($i % 15 == 0) {
		print "FizzBuzz\n";
	} elsif ($i % 3 == 0) {
		print "Fizz\n";
	} elsif ($i % 5 == 0) {
		print "Buzz\n";
	} else {
		print "$i\n";
	}
}

