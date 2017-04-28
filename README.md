
This code permits to convert html pages containing Gauquelin data to csv files.

The html pages contain the 5th version of Gauquelin data, and were retrieved from <a href="http://cura.free.fr/gauq/17archg.html">http://cura.free.fr/gauq/17archg.html</a>, 2017-04-26, with the following command :
<code>wget --mirror --wait 1 --page-requisites --cut-dirs=1 --relative --no-parent -A '902gd*.html' --no-host-directories http://cura.free.fr/gauq/17archg.html</code>

This code has been tested on linux (ubuntu 14.4) with php 7.1

File <code>htlm-raw.zip</code> contains the copy of the pages parsed by this code (further modifications of the pages may introduce bugs in the code).

<h2>usage</h2>
- Copy <code>config.yml.dist</code> to <code>config.yml</code> and adapt the values of <code>source-dir</code> and <code>ouptut-dir</code>
- Go to the directory this <code>README</code> and run :
<pre>php run.php</pre>
- Follow the instructions

<h2>Notes on the generated files</h2>

The following remarks apply to all generated files

- The first line contains field names ; other lines contain data.

The following fields are common to several series :
    NUM         | Original NUM record number coming from cura.free.fr
    NAME        | 
    DATE        | ISO 8601 of this form : YYYY-MM-DD HH:MM:SSsHH:MM (timezone offset is included)
    PLACE       | 
    COUNTRY     | ISO 3166 2 letters format
    COD         | Administrative division (département in France ; equivalent of ADM2 in geonames.org)
    LON         | In decimal degrees
    LAT         | In decimal degrees
    PROFESSION  | 


<h3>Series A</h3>



