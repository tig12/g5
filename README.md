
This code converts Gauquelin data to csv files.  
Concerns the version 5 of C.U.R.A Gauquelin archives, available at <a href="http://cura.free.fr/gauq/17archg.html">http://cura.free.fr/gauq/17archg.html</a>  
<b>Status</b> : work in progress - not ready for reliable use  
Works with data retrieved on 2017-04-26  
Code developed with php 7.1 ; tested on linux (ubuntu 14.4)  
Realeased under the General Public Licence (v2 or later), available at <a href="https://www.gnu.org/licenses/gpl.html">gnu.org/licenses/gpl.html</a>

<h2>Usage</h2>

- Copy the html pages containing the data on your local machine (you can use for example script <code>tools/get-data</code>)  
- Copy <code>config.yml.dist</code> to <code>config.yml</code> and adapt the values of <code>source-dir</code> and <code>ouptut-dir</code>  
- Go to the directory containing this <code>README</code> and run :
<pre>php run-gauquelin5.php</pre>
- Follow the instructions

<h2>Matching geonames.org</h2>

In some files (like E1 and E3), timezone information is missing. The program tries to associate the names of cities to places of geonames, to compute timezone. To do this, the program needs to have geonames data stored in a postgres database, as done by the program located at <a href="https://github.com/tig12/geonames2postgres">github.com/tig12/geonames2postgres</a>.  

To do this, you need to have postgres installed on your machine, run <code>geonames2postgres.py</code> for all the countries containing birth dates.  
By default, this feature is disabled ; to enable it, edit <code>config.yml</code>, and put :
<pre>geonames: true</pre>

<h2>Notes on the generated csv files</h2>

In all generated csv files, the first line contains field names ; other lines contain data.

The following fields are common to several series :

|             |                                                                                         |
|-------------|-----------------------------------------------------------------------------------------|
| NUM         | Original NUM record number coming from cura.free.fr                                     |
| NAME        |                                                                                         |
| DATE        | ISO 8601 of this form : YYYY-MM-DD HH:MM:SSsHH:MM (timezone offset is included)         |
| PLACE       |                                                                                         |
| COU         | ISO 3166 country code, 2 letters format                                                 |
| COD         | Administrative division (département in France ; equivalent of ADM2 in geonames.org)    |
| LON         | In decimal degrees                                                                      |
| LAT         | In decimal degrees                                                                      |
| PRO         | Profession code                                                                         | 


<h3>Serie A</h3>



