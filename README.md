
This code converts Gauquelin data to csv files.  
Concerns the version 5 of C.U.R.A Gauquelin archives, available at <a href="http://cura.free.fr/gauq/17archg.html">http://cura.free.fr/gauq/17archg.html</a>

Code developed on linux (ubuntu 14.4) with php 7.1  
Works with data retrieved on 2017-04-26

<h2>usage</h2>
- Copy the html pages containing the data on your local machine (you can use for example script <code>tools/get-data</code>)
- Copy <code>config.yml.dist</code> to <code>config.yml</code> and adapt the values of <code>source-dir</code> and <code>ouptut-dir</code>
- Go to the directory containing this <code>README</code> and run :
<pre>php run.php</pre>
- Follow the instructions

<h2>Notes on the generated csv files</h2>

In all generated csv files, the first line contains field names ; other lines contain data.

The following fields are common to several series :

|             |                                                                                         |
|-------------|-----------------------------------------------------------------------------------------|
| NUM         | Original NUM record number coming from cura.free.fr                                     |
| NAME        |                                                                                         |
| DATE        | ISO 8601 of this form : YYYY-MM-DD HH:MM:SSsHH:MM (timezone offset is included)         |
| PLACE       |                                                                                         |
| COUNTRY     | ISO 3166 2 letters format                                                               |
| COD         | Administrative division (département in France ; equivalent of ADM2 in geonames.org)    |
| LON         | In decimal degrees                                                                      |
| LAT         | In decimal degrees                                                                      |
| PROFESSION  |                                                                                         | 


<h3>Serie A</h3>



