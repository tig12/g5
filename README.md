
This code converts Gauquelin data to csv files.  
Concerns the version 5 of C.U.R.A Gauquelin archives, available at <a href="http://cura.free.fr/gauq/17archg.html">http://cura.free.fr/gauq/17archg.html</a>  
<b>Status</b> : work in progress - not ready for reliable use  
Works with data retrieved on 2017-04-26  
Code developed with php 7.1 ; tested on linux (ubuntu 14.4)  
Released under the General Public Licence (v2 or later), available at <a href="https://www.gnu.org/licenses/gpl.html">gnu.org/licenses/gpl.html</a>

<h2>Usage</h2>

- Copy the html pages containing the data on your local machine (you can use for example script <code><a href="https://github.com/tig12/gauquelin5/blob/master/tools/get-data">tools/get-data</a></code>)  
- Copy <code>config.yml.dist</code> to <code>config.yml</code> and adapt the values of <code>source-dir</code> and <code>ouptut-dir</code>  
- Go to the directory containing this <code>README</code> and run :
<pre>php run-gauquelin5.php</pre>
- Follow the instructions

<h2>Matching geonames.org</h2>

In some files (like E1 and E3), timezone information is missing. The program tries to associate the names of cities to places of geonames, to compute timezone. To do this, the program needs to have geonames data stored in a postgres database, as done by the program located at <a href="https://github.com/tig12/geonames2postgres">github.com/tig12/geonames2postgres</a>.  

To do this, you need to have postgres installed on your machine, run <code>geonames2postgres.py</code> for all the countries containing birth dates.  
By default, this feature is disabled ; to enable it, edit <code>config.yml</code>, and put :
<pre>geonames: true</pre>
You also need to put the correct values in the <code>postgresql</code> section.

<h2>Notes on the generated csv files</h2>

In all generated csv files, the first line contains field names ; other lines contain data.

The following fields are common to several series :

<table>
    <tr>
        <th>Field name</th>
        <th>Comments</th>
    </tr>
    <tr>
        <td style="vertical-align:top;">NUM</td>
        <td style="vertical-align:top;">
            Original NUM record number coming from cura.free.fr
        </td>
    </tr>
    <tr>
        <td style="vertical-align:top;">NAME</td>
        <td style="vertical-align:top;">
            Person name when available
        </td>
    </tr>
    <tr>
        <td style="vertical-align:top;">PRO</td>
        <td style="vertical-align:top;">
            Profession code, see list below
        </td>
    </tr>
    <tr>
        <td style="vertical-align:top;">DATE</td>
        <td style="vertical-align:top;">
            ISO 8601 of this form : YYYY-MM-DD HH:MM:SSsHH:MM (timezone offset is included)
            <br/>Example : <code>2017-05-03 09:26:11+02:00</code> : timezone is +2 hours
        </td>
    </tr>
    <tr>
        <td style="vertical-align:top;">PLACE</td>
        <td style="vertical-align:top;">
            Place name
        </td>
    </tr>
    <tr>
        <td style="vertical-align:top;">COU</td>
        <td style="vertical-align:top;">
            ISO 3166 country code, 2 letters format
        </td>
    </tr>
    <tr>
        <td style="vertical-align:top;">COD</td>
        <td style="vertical-align:top;">
            Administrative division (département in France ; equivalent of ADM2 in geonames.org)
        </td>
    </tr>
    <tr>
        <td style="vertical-align:top;">LON</td>
        <td style="vertical-align:top;">
            Longitude in decimal degrees
        </td>
    </tr>
    <tr>
        <td style="vertical-align:top;">LAT</td>
        <td style="vertical-align:top;">
            Latitude in decimal degrees
        </td>
    </tr>
</table>

<h3>Profession codes</h3>

Here is a complete list of profession codes that is used in all generated files

| Code | Label (fr) | Label (en) |
| --- | --- | --- |
| ACT | Acteur | Actor | 
| AR | Artiste | Artist | 
| ATH | Athlétisme | Athletism | 
| AUT | Auto-moto | Auto-moto | 
| AVI | Aviation | Aviation | 
| AVR | Aviron | Rowing | 
| BAS | Basketball | Basketball | 
| BIL | Billard | Billard | 
| BOX | Boxe | Boxing | 
| CAN | Canoë-kayak | Canoe-kayak | 
| CAR | Réalisateur de dessins animés | Cartoonist | 
| CMB | Chef d'orchestre militaire | Conductor of military band | 
| CYC | Cyclisme | Cyclism | 
| DAN | Danseur | Dancer | 
| EQU | Equitation | Equestrian | 
| ESC | Escrime | Fencing | 
| EX | Dirigeant | Executive | 
| FOO | Football | Football | 
| GLA | Sports de glace | Bobsleigh and Skating | 
| GOL | Golf | Golf | 
| GYM | Gymnastique | Gymnastic | 
| HAL | Haltérophilie | Weightlifting | 
| HAN | Handball | Handball | 
| HOC | Hockey | Hockey | 
| JO | Journaliste | Journalist | 
| LUT | Lutte | Wrestling | 
| MAR | Marche | Walking | 
| MI | Militaire | Military | 
| MUS | Musicien | Musician | 
| NAT | Natation | Swimming | 
| OPE | Chanteur d'opéra | Opera singer | 
| PAI | Peintre | Painter | 
| PAT | Patin à roulettes | Roller Skates | 
| PEL | Pelote basque | Pelote basque | 
| PH | Médecin | Physician | 
| PHO | Photographe | Photographer | 
| PO | Politicien | Politician | 
| RUG | Rugby et Jeu à XIII | Rugby and Rugby league | 
| SC | Scientifique | Scientist | 
| SKI | Ski | Ski | 
| SP | Sportif | Sport champion | 
| TEN | Tennis | Tennis | 
| TIR | Tir | Shooting | 
| VOI | Voile | Sailing | 
| VOL | Volley ball | Volley ball | 
| WR | Ecrivain | Writer | 
| XX | Divers | Various | 



