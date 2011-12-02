= International Aid Transparency Initiative (IATI) CRS Converter=
 
International Aid Transparency Initiative (IATI) CRS Converter is a set of scripts
to generate IATI compliant XML from specific sets of data supplied by CRS data users.
 This may be useful for other datasets and other transformations.

See http://iatistandard.org/ for more info.

==Licence==
GNU General Public License (except where stated)
see the Copying directory

==Install==
These are built as php_cli file, but you could run it on your server

===Get some data===
Included are spreadsheets of data that has been used to run a conversion
in November 2011. 

Included is a country list of ISO 3166 codes taken from:
http://iatistandard.org/codelists/country

==Run==
Change into the appropriate directory.
Run
php parse_csv_<countryname>.php TRUE
NB. Don't forget the TRUE


