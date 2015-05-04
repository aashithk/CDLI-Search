# This script is a tool to check transliteration and email the results to the user.
# It is called by signfilter.php.
#
# let's start sending error output to /dev/null
open STDERR, '>/Library/WebServer/Documents/cdli/search/signlist/error.txt';

$signfile=$ARGV[1];
$wordfile=$ARGV[2];
$testfile=$ARGV[0];

@wordbounds=(' ','_','\"');

open(SIGNINF, "$signfile") || die "ERROR 001: Cannot open(\"$file\")\n";
open(WORDINF, "$wordfile") || die "ERROR 002: Cannot open(\"$file\")\n";
open(TESTINF, "$testfile") || die "ERROR 003: Cannot open(\"$file\")\n";


($sec,$min,$hour,$mday,$mon,$year,$wday,$yday,$isdst) = localtime(time);

open(OUT, ">$ARGV[3]") || die "ERROR 004: Cannot open(\"out.txt\")\n";
open(OUT2, ">$ARGV[4]") || die "ERROR 005: Cannot open(\"out.txt\")\n";
open(OUT3, ">$ARGV[5]") || die "ERROR 006: Cannot open(\"out.txt\")\n";
open(OUT4, ">$ARGV[6]") || die "ERROR 007: Cannot open(\"out.txt\")\n";
open(OUT5, ">$ARGV[7]") || die "ERROR 007: Cannot open(\"out.txt\")\n";
open(OUT6, ">$ARGV[8]") || die "ERROR 007: Cannot open(\"out.txt\")\n";


my %wordhash;
my %signhash;


$line = 0;
for (<WORDINF>) {
	chomp;
   	$wordhash{$_} = 1;
}

for (<SIGNINF>) {
	chomp;
   	$signhash{$_} = 1;
}
$st = "...";
$signhash{$st} = 1;



my $linecount=0 ;
my @newWord;
my @newSign;
my @allWord;
my @allSign;


while (<TESTINF>) {
	chomp;
	$linecount++;
	unless($_=~m/^(\$|\@|\#|\&|\>)/){     # unless means "if not", this line is used to escape non-transliteration line, m/ / return true or false
		$_=~s/^\d*\. //;                  # s means replace the first occurrence,  s/foo/bar/;  replace 1xx. by empty string, 
		$_=~s/^\d*'\. //;
		$_=~s/\(\$ .* \$\)//;

		# $_=~s/([|\!\[\]\#\?\*\<\>])//g ;  # replaces any occurrence of the exact character sequence
		# $_=~s/([|\!\#\?\*\<\>])//g ;  # replaces any occurrence of the exact character sequence
		$_=~s/([\[\]\!\#\?\*\<\>])//g ;  # replaces any occurrence of the exact character sequence
		my @words = split(/[, _\"]/, $_); # word splitters
		
		foreach $lineword (@words) {
		    
		    push @allWord, $lineword ;
		    my @signs = split(/[-\{\}]/, $lineword) ;
		    foreach (@signs){
					push @allSign, $_ ;
			}

		    
			if ((!exists $signhash{$lineword}) && (!exists $wordhash{$lineword}) && ($lineword!~m/^\d+'\./)){
				push @newWord, $lineword ;
				print OUT2 "line ", $linecount, ": ", $lineword, "\n" ;
				
				my @signs = split(/[-\{\}]/, $lineword) ;
				$numofSign = @signs;
				if ($numofSign<=1) {
					@signs = ($lineword) ;
				}
				
				foreach (@signs){
					if ((!exists $signhash{$_})&&($_=~m/\w/)) {
						push @newSign, $_ ;
						print OUT4 "line ", $linecount, ": ", $_, "\tFROM $lineword\n" ;
					}
				}
			}
		}
		
	}
}
close(OUT2);
close(OUT4);

my %allwordhash = map{$_,1} @allWord;
my @uniqueWord = keys %allwordhash ;
foreach $word (sort(@uniqueWord)){	
	print OUT5 "$word\n";
}
close(OUT5) ;

my %allsignhash = map{$_,1} @allSign ;
my @uniqueSign = keys %allsignhash ;
foreach $sign (sort(@uniqueSign)){
	print OUT6 "$sign\n" ;
}
close(OUT6) ;


my %newWordhash = map{$_,1} @newWord ;
my @uniqueWord = keys %newWordhash ;
foreach $word (sort(@uniqueWord)){	
	print OUT "$word\n";
}


my %NewSignhash = map{$_,1} @newSign;
my @uniqueSign = keys %NewSignhash  ;
foreach $sign (sort(@uniqueSign)){
	print OUT3 "$sign\n";
}

close(OUT);
close(OUT3);

close(WORDINF);
close(SIGNINF);
close(TESTINF);



