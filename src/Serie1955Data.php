<?php
/******************************************************************************
    Definition of groups used by Gauquelin in the book of 1955
    Generated on 2017-05-12T05:47:23+02:00
    @license    GPL
********************************************************************************/

namespace gauquelin5;

class Serie1955Data{

    /** Groups ; format : group code => [name, serie] **/
    const GROUPS = [
        '576MED' => ["576 membres associés et correspondants de l'académie de médecine", 'A2'],
        '508MED' => ['508 autres médecins notables', 'A2'],
        '570SPO' => ['570 sportifs', 'A1'],
        '676MIL' => ['676 militaires', 'A3'],
        '906PEI' => ['906 peintres', 'A4'],
        '361PEI' => ['361 peintres mineurs', 'ZZ'],
        '500ACT' => ['500 acteurs', 'A5'],
        '494DEP' => ['494 députés', 'A5'],
        '349SCI' => ["349 membres, associés et correspondants de l'académie des sciences", 'A2'],
        '884PRE' => ['884 prêtres', 'ZZ'],
    ];
    
    /** 1
        format : group code => [ values of NUM in this group's serie ],
    **/
    const DATA = [
        '576MED' => [
            '1', // 1873-12-15 15:59:40+00:00 TARBES - Abadie Joseph
            '2', // 1876-08-12 22:57:36+00:00 BLAYES - Abadie Jules
            '3', // 1864-03-10 06:44:28+00:00 BEDARIEUX - Abelous Jacques
            '6', // 1810-01-23 09:44:28+00:00 ST-CHINIAN - Albert Paul
            '8', // 1866-08-26 20:57:28+00:00 ST-SARDOS - Aloy Francois
            '9', // 1876-02-16 13:38:32+00:00 MARSEILLE - Ambart Leon
            '10', // 1873-09-21 10:35:12+00:00 NANCY - Ancel Paul
            '11', // 1839-03-30 01:06:20+00:00 NANTES - Andouard Ambroise
            '12', // 1875-05-19 06:54:16+00:00 TOULOUSE - Argaud Rene
            '13', // 1876-02-28 02:54:16+00:00 TOULOUSE - Gauquelin-A2-13
            '14', // 1846-01-03 10:16:40+00:00 CUSSET - Arloing Saturnin
            '15', // 1842-04-30 12:57:36+00:00 ST-CIERS - Armaingaud Antoine
            '17', // 1830-09-16 00:35:16+00:00 SALONNES - Arnould Jules
            '18', // 1852-11-12 13:57:36+00:00 BORDEAUX - Arnozan Charles
            '19', // 1851-06-08 09:55:00+00:00 LA PORCHERIE - Arsonval Arsene
            '20', // 1862-01-09 08:02:08+00:00 ANGERS - Arthus Maurice
            '21', // 1875-01-29 07:49:44+00:00 ST-LAURENT-DOLT - Astruc Albert
            '22', // 1838-07-01 02:16:24+00:00 BREST - Auffret Charles
            '23', // 1868-06-01 07:01:28+00:00 BERNIERES-S-MER - Auvray Maurice
            '24', // 1822-05-28 09:57:36+00:00 BORDEAUX - Azam Charles
            '26', // 1809-03-25 21:06:40+00:00 MONTBAZON - Baillarger Jules
            '27', // 1834-09-07 09:50:48+00:00 AMIENS - Baillet Louis
            '28', // 1820-09-10 09:51:28+00:00 VERSAILLES - Baillet Casimir
            '30', // 1845-01-16 03:39:08+00:00 ST-JULIEN-S/REY - Balland Joseph
            '31', // 1853-03-29 04:55:00+00:00 AMBAZAC - Ballet Gilbert
            '33', // 1849-04-04 23:06:20+00:00 CHATEAUBRIAND - Balzer Felix
            '34', // 1814-09-01 02:34:08+00:00 TOULON - Barallier Auguste
            '35', // 1867-06-16 15:57:36+00:00 BORDEAUX - Barbary Jean
            '36', // 1857-05-10 00:37:08+00:00 MENS - Gauquelin-A2-36
            '37', // 1870-03-18 00:54:16+00:00 ISLE-EN-DODON - Bardier Emile
            '38', // 1814-06-04 20:55:00+00:00 LIMOGES - Bardinet Alphonse
            '39', // 1832-01-17 21:54:40+00:00 BLOIS - Barnsby Robert
            '40', // 1853-03-25 01:35:52+00:00 BAUME-LES-DAMES - Barrier Gustave
            '41', // 1806-09-24 01:05:16+00:00 SARREGUEMINES - Barth Jean
            '42', // 1857-12-04 17:58:40+00:00 COUHE-VERAC - Barthe Joseph
            '43', // 1811-08-06 06:50:36+00:00 NARBONNE - Barthez Antoine
            '44', // 1876-09-13 05:29:00+00:00 BONE - Gauquelin-A2-44
            '45', // 1806-05-07 20:45:32+00:00 COMPIEGNE - Baudrimont Alexandre
            '46', // 1854-06-23 00:44:28+00:00 LODEVE - Baumel Leopold
            '48', // 1853-03-28 01:53:40+00:00 STE-CROIX - Bazy Pierre
            '50', // 1806-05-08 09:39:08+00:00 COLLONGES - Beau Joseph
            '51', // 1816-10-15 06:35:16+00:00 BASSING - Gauquelin-A2-51
            '53', // 1867-06-23 06:04:40+00:00 REAUX - Begouin Paul
            '54', // 1859-03-30 12:48:56+00:00 LENS - Behal Auguste
            '56', // 1850-02-04 08:43:40+00:00 NOGENT-S - Benjamin Henri
            '57', // 1832-05-09 19:30:56+00:00 ST-PAUL-DE-VAR - Beranger Laurent
            '58', // 1870-02-17 18:37:48+00:00 MOREZ - Gauquelin-A2-58
            '59', // 1845-01-06 14:32:32+00:00 BEAUCOURT - Berger Paul
            '60', // 1817-08-27 18:49:20+00:00 MORET - Bergeron Jules
            '61', // 1857-10-07 00:57:28+00:00 CASSENEUIL - Gauquelin-A2-61
            '62', // 1813-07-12 03:10:40+00:00 CHATENAY - Bernard Claude
            '65', // 1815-02-09 23:41:04+00:00 BAZEILLE - Bertherand Alphonse
            '66', // 1833-08-27 15:06:20+00:00 NANTES - Bertin Georges
            '68', // 1851-08-04 16:38:32+00:00 ARLES - Bertrand Louis
            '69', // 1831-04-20 12:01:28+00:00 HONFLEUR - Besnier Ernest
            '70', // 1868-02-23 01:06:16+00:00 BOULOGNE-S-SEINE - Bezancon Fernand
            '71', // 1876-07-03 05:39:52+00:00 ROUVRAY - Bierry Henri
            '72', // 1873-07-18 14:54:16+00:00 MONTBRUN - Billard Gabriel
            '75', // 1884-12-03 02:42:36+00:00 VAUVERT - Blanc Georges
            '76', // 1857-02-28 07:06:40+00:00 ST-CHRISTOPHE - Blanchard Raphael
            '78', // 1844-12-02 17:45:32+00:00 MARLE-S - Blanquinque Paul
            '79', // 1838-12-16 09:30:36+00:00 COLMAR - Bleicher Marie
            '82', // 1848-10-26 22:29:00+00:00 STRASBOURG - Boeckel Jules
            '84', // 1809-03-20 19:39:08+00:00 AMBERIEU - Gauquelin-A2-84
            '85', // 1866-10-21 16:32:08+00:00 ANGERS - Gauquelin-A2-85
            '86', // 1812-01-03 08:40:40+00:00 LYON - Bouchacourt Antoine
            '87', // 1837-09-06 02:39:28+00:00 MONTIER-EN-DER - Bouchard Charles
            '88', // 1833-12-18 00:30:36+00:00 RIBEAUVILLE - Bouchard Henri
            '89', // 1806-07-23 19:45:40+00:00 ISLE-S/SEREIN - Bouchardat Appolinaire
            '91', // 1830-10-27 07:39:08+00:00 COLIGNY - Boudet Marie
            '92', // 1828-01-06 00:54:00+00:00 GARNAY - Boudier Jean
            '93', // 1870-01-04 16:06:20+00:00 LIGNE - Gauquelin-A2-93
            '94', // 1870-06-11 04:41:04+00:00 VENDRESSE - Bouin Adrien
            '95', // 1822-04-02 03:18:20+00:00 PERPIGNAN - Bouis Jules
            '96', // 1814-05-25 14:35:12+00:00 PONT-A-MOUSSON - Bourdon Alexis
            '97', // 1836-05-26 06:45:40+00:00 ST-CYR-LES-COLO - Bourgoin Alfred
            '98', // 1816-12-08 04:49:44+00:00 BRUSQUE - Bourguet Eugene
            '99', // 1851-06-21 07:41:04+00:00 JANDUN - Bourquelot Emile
            '100', // 1852-03-13 11:47:40+00:00 ISSOIRE - Bousquet Jean
            '101', // 1819-05-20 13:47:24+00:00 NEUVY-S/LOIRE - Boutet Antoine
            '103', // 1858-04-06 00:29:00+00:00 STRASBOURG - Braemer Louis
            '104', // 1852-05-01 16:51:28+00:00 MONTFORT-LAMAUR - Brault Albert
            '106', // 1869-09-27 04:01:28+00:00 BRETTEVILLE-S L - Bridou Jules
            '107', // 1867-05-01 22:06:20+00:00 NANTES - Brindeau Auguste
            '108', // 1852-04-15 13:05:52+00:00 BESANCON - Brissaud Edouard
            '110', // 1824-06-28 19:57:36+00:00 STE-FOY-LA-GRAN - Broca Pierre
            '113', // 1837-02-13 15:45:32+00:00 ST-QUENTIN - Brouardel Paul
            '115', // 1854-08-13 05:25:40+00:00 ROUEN - Brunon Raoul
            '116', // 1829-08-14 03:50:48+00:00 PERONNE - Bucquoy Jules
            '117', // 1815-03-02 05:49:20+00:00 CHELLES - Buignet Henri
            '118', // 1818-01-24 08:50:28+00:00 VIERZON - Burdel Simon
            '119', // 1830-05-20 15:06:20+00:00 NANTES - Bureau Louis
            '120', // 1873-10-11 12:42:32+00:00 MAURUPT - Burnet Etienne
            '121', // 1858-10-10 06:59:40+00:00 POUY - Cadeac Jean
            '122', // 1858-07-13 13:35:12+00:00 BATTIGNY - Cadiot Pierre
            '123', // 1863-07-12 22:30:56+00:00 NICE - Calmette Joseph
            '124', // 1872-08-27 21:49:20+00:00 NEMOURS - Camus Jean
            '125', // 1867-06-17 12:49:20+00:00 NEMOURS - Camus Lucien
            '127', // 1845-05-29 16:54:40+00:00 MOISSAC - Carles Pierre
            '128', // 1845-02-19 15:39:52+00:00 DIJON - Gauquelin-A2-128
            '129', // 1869-01-16 01:55:00+00:00 LIMOGES - Gauquelin-A2-129
            '130', // 1873-06-28 22:40:40+00:00 STE-FOY-LES-LYO - Carrel Alexis
            '131', // 1871-02-27 22:50:28+00:00 BASSAC - Gauquelin-A2-131
            '135', // 1808-03-25 03:57:36+00:00 BORDEAUX - Cazeaux Pierre
            '136', // 1852-01-10 18:40:40+00:00 LYON - Cazeneuve Paul
            '137', // 1836-10-17 08:48:56+00:00 SAMER - Cazin Pierre
            '138', // 1872-04-06 01:51:28+00:00 GAILLAC - Cestan Raymond
            '141', // 1851-03-12 09:37:48+00:00 CHILLY-LA-VIGNO - Chamberland Charles
            '142', // 1885-04-18 05:34:08+00:00 UZEMAIN - Champy P
            '143', // 1851-10-13 02:44:28+00:00 LE PUY - Chantemesse Andre
            '144', // 1809-10-31 03:53:16+00:00 CHATILLON-S/IND - Charcellay-Laplace L
            '145', // 1867-07-15 09:51:16+00:00 NEUILLY - Charcot Jean
            '148', // 1852-06-15 22:53:16+00:00 ARGENTON - Charpentier Pierre
            '149', // 1804-12-22 08:06:20+00:00 NANTES - Gauquelin-A2-149
            '150', // 1813-11-30 09:37:08+00:00 TULLINS - Chatin Gaspard
            '152', // 1823-05-18 03:10:40+00:00 AVIGNON - Chauffard Emile
            '153', // 1855-08-22 21:40:40+00:00 AVIGNON - Chauffard Emile
            '154', // 1853-12-04 17:57:12+00:00 ST-FLOVIER - Chaumier Edmond
            '155', // 1827-11-21 11:45:40+00:00 VILLENEUVE-LA-G - Chauveau Auguste
            '156', // 1841-06-09 20:11:00+00:00 QUINTIN - Chauvel Jules
            '157', // 1866-06-04 15:57:36+00:00 BORDEAUX - Chavanaz Georges
            '158', // 1834-07-27 01:58:40+00:00 MONTS-S/GUEUSE - Chedevergne Antoine
            '159', // 1817-08-23 08:43:40+00:00 BAR-S-SEINE - Chereau Achille
            '160', // 1877-10-28 19:37:48+00:00 LONS-LE-SAUNIER - Chevassu Maurice
            '162', // 1841-09-28 21:35:40+00:00 MOUILLERON-EN-P - Gauquelin-A2-162
            '165', // 1870-11-04 21:54:16+00:00 TOULOUSE - Cluzet Joseph
            '166', // 1825-05-12 18:35:32+00:00 MOLLANS - Colin Gabriel
            '167', // 1830-04-16 00:35:16+00:00 ST-QUIRIN - Gauquelin-A2-167
            '168', // 1856-02-16 13:35:16+00:00 METZ - Collignon Rene
            '169', // 1880-07-07 21:05:12+00:00 FROUARD - Collin Remy
            '170', // 1875-04-23 06:02:00+00:00 ORAN - Colombani Jules
            '171', // 1834-03-10 00:44:28+00:00 POUSSAN - Combalat Barthelemy
            '172', // 1846-10-04 23:39:28+00:00 IS-EN-BASSIGNY - Cornevin Charles
            '173', // 1837-06-17 04:46:40+00:00 CUSSET - Cornil Andre
            '174', // 1884-04-05 06:35:00+00:00 CONSTANTINE - Costantini Joseph
            '175', // 1807-05-10 13:44:28+00:00 CASTRIES - Coste Jean
            '180', // 1869-03-04 12:46:40+00:00 SAULZET - Coutiere Henri
            '181', // 1873-09-02 10:09:08+00:00 BOURG-EN-BRESSE - Gauquelin-A2-181
            '182', // 1842-09-12 19:40:24+00:00 LAROCHE-CHALIAS - Gauquelin-A2-182
            '183', // 1868-05-24 18:53:16+00:00 CHATEAUROUX - Crespin Marie
            '184', // 1850-07-31 14:59:12+00:00 CONLIE - Crie Louis
            '186', // 1873-10-28 17:34:08+00:00 TOULON - Cuneo Bernard
            '188', // 1812-03-19 15:47:40+00:00 ST-AMAND-LES-EA - Davaine Casimir
            '189', // 1853-10-31 20:50:48+00:00 ETELFAY - Debierre Marie
            '191', // 1882-12-07 02:41:04+00:00 SEDAN - Debre Robert
            '192', // 1813-07-30 17:10:56+00:00 PONTIVY - Debrou Toussaint
            '194', // 1805-02-13 21:47:24+00:00 ST-AMAND-EN-PUI - Delafond Henri
            '196', // 1875-01-08 03:21:28+00:00 VERSAILLES - Delamare Gabriel
            '199', // 1861-11-15 07:49:20+00:00 LA FERTE-GAUCHE - Delbet Pierre
            '200', // 1871-09-19 13:55:40+00:00 ST-MARTIN-LE-GA - Gauquelin-A2-200
            '201', // 1868-10-06 21:47:40+00:00 GENECH - Delezenne Camille
            '202', // 1828-04-07 10:40:40+00:00 FLEURIE - Delore Xavier
            '203', // 1847-08-02 04:35:12+00:00 LUNEVILLE - Delorme Edmond
            '205', // 1846-04-22 05:05:12+00:00 NANCY - Demange Joseph
            '206', // 1842-09-12 14:12:36+00:00 ST-CIERS - Gauquelin-A2-206
            '207', // 1859-12-25 06:57:36+00:00 BORDEAUX - Deniges Georges
            '209', // 1824-01-21 04:57:36+00:00 AMBARES - Denuce Jean
            '210', // 1811-07-26 01:01:28+00:00 MORLAAS - Depaul Jean
            '211', // 1879-08-19 15:32:00+00:00 ORAN - Derrien Eugene
            '212', // 1883-08-07 06:12:32+00:00 ESTERNAY - Gauquelin-A2-212
            '213', // 1819-09-16 23:10:40+00:00 LOIRE - Desgranges Antoine
            '214', // 1863-07-15 00:39:28+00:00 BANNES - Desgrez Alexandre
            '215', // 1828-09-21 11:59:40+00:00 ALENCON - Desnos Louis
            '216', // 1872-11-10 15:21:40+00:00 BEAUVAIS - Gauquelin-A2-216
            '219', // 1832-05-16 16:02:08+00:00 ST-PIERRE-MONTL - Dezanneau Alfred
            '221', // 1812-01-02 17:09:08+00:00 BOURG-EN-BRESSE - Diday Paul
            '222', // 1839-11-18 14:54:16+00:00 TOULOUSE - Dieulafoy Georges
            '223', // 1852-12-22 01:01:28+00:00 LEMBEYE - Doleris Amedee
            '224', // 1889-12-24 15:02:00+00:00 MONT-DE-MARSAN - Donatien Andre
            '226', // 1858-06-10 21:57:04+00:00 THENON - Doumer Jean
            '227', // 1882-03-18 16:47:40+00:00 LEZOUX - Douris Roger
            '228', // 1863-07-28 13:37:08+00:00 URIAGE - Doyon Maurice
            '229', // 1827-11-01 06:37:08+00:00 GRENOBLE - Doyon Pierre
            '230', // 1851-10-01 15:47:40+00:00 LILLE - Gauquelin-A2-230
            '231', // 1879-03-22 09:40:40+00:00 CHAROLLES - Dubreuil Georges
            '232', // 1857-05-09 04:57:36+00:00 BORDEAUX - Dubreuilh William
            '233', // 1846-02-27 08:35:48+00:00 AMIENS - Ducastel Auguste
            '234', // 1843-02-19 03:50:48+00:00 MONTDIDIER - Du Cazal Leon
            '235', // 1814-04-03 08:45:40+00:00 AUXERRE - Duche Emile
            '236', // 1840-06-24 16:50:16+00:00 AURILLAC - Duclaux Pierre
            '237', // 1822-12-16 12:57:12+00:00 TOURS - Duclos Michel
            '238', // 1837-05-12 00:42:32+00:00 CHAMERY - Gauquelin-A2-238
            '240', // 1870-02-14 01:39:08+00:00 LOMPNES - Dumarest Frederic
            '241', // 1866-03-06 22:42:36+00:00 LEDIGAN - Gauquelin-A2-241
            '242', // 1826-03-08 09:01:28+00:00 HONFLEUR - Dumont-Pallier Victor
            '244', // 1862-03-07 07:38:32+00:00 MARSEILLE - Dupre Ernest
            '245', // 1844-04-12 15:57:04+00:00 VERGT - Dupuy Pierre
            '247', // 1849-07-07 23:31:28+00:00 VIRE - Duret Henri
            '248', // 1844-02-07 17:30:56+00:00 GRASSE - Duval Mathias
            '251', // 1836-01-06 11:30:36+00:00 RIXHEIM - Ehrmann Jules
            '252', // 1861-06-06 11:44:28+00:00 MONTPELLIER - Estor Eugene
            '253', // 1806-07-30 10:59:12+00:00 LE MANS - Etoc-Demazy Gustave
            '254', // 1882-04-27 20:44:28+00:00 MONTPELLIER - Euziere Jules
            '256', // 1845-08-08 22:50:36+00:00 LIMOUX - Fabre Paul
            '258', // 1841-05-06 02:49:20+00:00 BANNOST - Faraboeuf Louis
            '259', // 1822-05-22 19:02:08+00:00 ANGERS - Farge Emile
            '260', // 1863-10-27 04:57:36+00:00 STE-FOY-LA-GRAN - Faure Jean
            '262', // 1813-02-20 06:40:40+00:00 LYON - Favre Pierre
            '263', // 1861-12-13 04:47:24+00:00 FOURS - Favrel Georges
            '265', // 1835-08-23 10:51:28+00:00 MONTFORT-LAMAUR - Ferrand Ernest
            '266', // 1857-04-05 13:29:00+00:00 MUTZIG - Fiessinger Charles
            '267', // 1814-10-07 03:54:16+00:00 TOULOUSE - Filhol Edouard
            '268', // 1843-05-11 21:54:16+00:00 TOULOUSE - Filhol Henri
            '269', // 1833-12-30 12:01:48+00:00 CHENAY - Fleury Gustave
            '270', // 1860-10-20 18:57:36+00:00 BORDEAUX - Fleury Maurice
            '271', // 1851-04-23 17:30:36+00:00 MUNSTER - Florence Albert
            '272', // 1880-11-10 08:59:40+00:00 CAUTERETS - Flurin Henri
            '273', // 1845-09-12 09:37:08+00:00 BOURGOIN - Fochier Louis
            '274', // 1843-01-08 22:47:40+00:00 LILLE - Folet Henri
            '275', // 1823-11-25 15:55:40+00:00 HARFLEUR - Follin Francois
            '276', // 1823-03-12 22:55:00+00:00 LIMOGES - Fonssagrives Jean
            '278', // 1849-10-22 00:34:08+00:00 TOULON - Fontan Jules
            '279', // 1872-10-04 14:01:28+00:00 BIARRITZ - Fourneau Ernest
            '281', // 1870-04-02 18:47:40+00:00 CLERMONT FERRAND - Gauquelin-A2-281
            '282', // 1814-02-22 11:51:28+00:00 VERSAILLES - Fremy Edmond
            '283', // 1876-01-27 10:35:12+00:00 BAYON - Furinsholz Albert
            '284', // 1848-05-29 01:51:40+00:00 GRANDVILLIERS - Galippe Victor
            '285', // 1828-02-10 22:52:32+00:00 GUERET - Gallard Theophile
            '287', // 1846-10-12 20:46:00+00:00 LANGOGNE - Galtier Victor
            '289', // 1812-03-16 15:40:40+00:00 AUTUN - Garreau Lazare
            '290', // 1861-08-26 12:55:40+00:00 ROUEN - Gascard Louis
            '291', // 1854-07-26 21:47:24+00:00 CHAMPLEMY - Gaucher Ernest
            '292', // 1866-04-06 06:40:40+00:00 MACON - Gaudier Henri
            '293', // 1837-09-23 21:20:36+00:00 NARBONNE - Gautier Armand
            '294', // 1809-01-28 03:57:28+00:00 ASTAFFORT - Gavarret Louis
            '295', // 1833-05-19 01:40:40+00:00 ST-GENIS-LAVAL - Gayet Charles
            '296', // 1863-02-23 18:51:40+00:00 MOUY - Gerard Ernest
            '297', // 1809-03-20 01:43:40+00:00 LOCHES - Gauquelin-A2-297
            '298', // 1858-02-15 03:41:04+00:00 BUZANCY - Gilbert Augustin
            '299', // 1836-01-11 03:46:40+00:00 MOULINS - Gilbert Claude
            '300', // 1857-01-25 06:54:40+00:00 MOLIERES - Gilis Jean
            '301', // 1820-02-03 10:57:36+00:00 BORDEAUX - Gintrac Henri
            '304', // 1816-05-31 11:04:40+00:00 LA ROCHELLE - Giraud-Teulon Marc
            '305', // 1818-11-22 04:40:40+00:00 LYON - Glenard Alexandre
            '306', // 1848-12-23 07:40:40+00:00 LYON - Glenard Claude
            '307', // 1857-01-16 04:34:08+00:00 EPINAL - Gley Marcel
            '310', // 1874-07-30 10:47:40+00:00 CATILLON-S/SAMB - Gauquelin-A2-310
            '311', // 1872-01-21 10:55:40+00:00 FECAMP - Gauquelin-A2-311
            '312', // 1843-09-29 18:52:32+00:00 FELLETIN - Grancher Joseph
            '313', // 1849-03-18 18:44:28+00:00 MONTPELLIER - Grasset Joseph
            '314', // 1838-04-02 14:45:32+00:00 LAON - Grehant Louis
            '315', // 1860-03-14 00:06:40+00:00 CREPY - Grimbert Louis
            '316', // 1811-02-10 04:34:08+00:00 FREJUS - Grisolle Augustin
            '317', // 1831-02-15 02:00:36+00:00 WESSERLING - Gros Jean
            '318', // 1844-06-05 05:29:00+00:00 STRASBOURG - Gross Charles
            '319', // 1871-12-19 09:44:28+00:00 MONTPELLIER - Grynfelt Edouard
            '320', // 1821-04-04 16:35:16+00:00 METZ - Gubler Adolphe
            '321', // 1832-11-08 21:34:08+00:00 TIGNECOURT - Gueniot Alexandre
            '322', // 1816-08-09 10:40:56+00:00 PLOERMEL - Guerin Alphonse
            '323', // 1872-12-22 21:58:40+00:00 POITIERS - Guerin Camille
            '324', // 1870-07-04 03:15:32+00:00 CHATEAU-THIERRY - Guiart Jules
            '325', // 1852-04-13 18:37:48+00:00 MONT-S/S-VAUDRE - Guinard Leon
            '326', // 1868-03-28 10:36:00+00:00 EMBRUN - Guigues Pierre
            '327', // 1876-03-03 03:55:40+00:00 ROUEN - Guillain Georges
            '328', // 1868-05-18 14:35:52+00:00 ROUGEMONT - Guilloz Theodore
            '330', // 1866-11-21 05:25:40+00:00 ROUEN - Halipre Andre
            '331', // 1849-03-07 14:30:36+00:00 FELLERINGEN - Haller Albin
            '332', // 1862-04-08 09:35:12+00:00 BACCARAT - Hallion Louis
            '334', // 1854-03-29 15:21:28+00:00 CONFLANS-STE-HO - Hanriot Maurice
            '337', // 1863-04-30 17:04:40+00:00 BURIE - Hedon Edouard
            '339', // 1838-05-22 05:42:32+00:00 REIMS - Henrot Henri
            '341', // 1819-10-01 13:45:40+00:00 SENS - Herard Hippolyte
            '342', // 1873-05-13 19:55:16+00:00 EVREUX - Herissey Eugene
            '343', // 1814-09-12 15:00:36+00:00 GUEBWILLER - Herrgott Francois
            '344', // 1849-04-22 06:32:32+00:00 BELFORT - Herrgott Louis
            '345', // 1818-09-04 19:55:16+00:00 LOUVIERS - Hervieux Jacques
            '346', // 1832-03-27 15:06:20+00:00 NANTES - Heurtaux Alfred
            '348', // 1809-12-01 15:29:00+00:00 WURTZENHEIM - Hirtz Mathieu
            '349', // 1844-04-05 07:43:40+00:00 AUXON - Huchard Henri
            '350', // 1860-02-21 20:44:28+00:00 LODEVE - Gauquelin-A2-350
            '351', // 1804-09-04 20:42:32+00:00 SEZANNE - Huguier Pierre
            '352', // 1849-04-15 15:39:52+00:00 CHATILLON S-SEIN - Hutinel Victor
            '353', // 1868-08-08 15:10:40+00:00 ORANGE - Imbert Jacques
            '354', // 1850-09-11 11:35:00+00:00 SEYNE-LES-ALPES - Imbert Jean
            '355', // 1850-04-30 02:35:32+00:00 IGNY - Gauquelin-A2-355
            '356', // 1806-01-16 12:39:08+00:00 CESSY - Jacquemier Jean
            '357', // 1828-01-23 08:29:00+00:00 SCHIRMECK - Jacquemin Eugene
            '358', // 1853-03-24 00:54:40+00:00 MONTAUBAN - Jalaguier Adolphe
            '360', // 1873-10-01 22:42:36+00:00 ALES - Gauquelin-A2-360
            '361', // 1850-02-01 18:57:36+00:00 BORDEAUX - Jeannel Francois
            '362', // 1874-01-20 01:06:20+00:00 NANTES - Jeannin Cyrille
            '364', // 1844-12-16 22:39:20+00:00 STAINVILLE - Joffroy Alexis
            '365', // 1870-08-06 13:19:20+00:00 MELUN - Jolly Justin
            '366', // 1841-01-10 11:40:40+00:00 PIERRE-EN-BROSS - Jolyet Felix
            '367', // 1836-11-06 01:16:24+00:00 BREST - Jouon Francois
            '368', // 1882-08-02 16:25:00+00:00 CERVIONE - Gauquelin-A2-368
            '370', // 1856-06-17 14:30:36+00:00 HIRTZBACH - Kaufmann Maurice
            '371', // 1841-01-26 20:29:00+00:00 SCHILTIGHEIM - Kelch Louis
            '372', // 1848-07-18 08:06:20+00:00 NANTES - Gauquelin-A2-372
            '373', // 1851-04-27 13:01:28+00:00 ORTHEZ - Labat Jean
            '375', // 1870-12-04 15:55:40+00:00 LE HAVRE - Gauquelin-A2-375
            '376', // 1830-12-04 10:57:28+00:00 BUZET - Laborde Jean
            '377', // 1825-08-24 02:57:28+00:00 AGEN - Laboulbene Alexandre
            '379', // 1843-08-17 09:54:16+00:00 CAHORS - Lacassagne Jean
            '380', // 1823-10-09 13:39:52+00:00 CHATILLON S-SEIN - Ladrey Claude
            '381', // 1830-03-12 12:06:20+00:00 NANTES - Laennec Theophile
            '383', // 1857-01-22 17:57:28+00:00 SOUMENSAC - Lagrange Felix
            '384', // 1861-04-23 04:39:52+00:00 DIJON - Laguesse Edouard
            '385', // 1875-09-12 22:55:16+00:00 EVREUX - Laignel-Lavastine Max
            '387', // 1853-03-30 18:57:36+00:00 LA TESTE-DE-BUC - Lalesque Fernand
            '388', // 1857-11-10 18:29:00+00:00 BISCHWILLER - Lambling Eugene
            '389', // 1872-07-20 08:47:40+00:00 AVESNES - Lambert Oscar
            '390', // 1845-03-27 11:42:32+00:00 REIMS - Landouzy Louis
            '391', // 1812-01-06 22:42:32+00:00 EPERNAY - Landouzy Marc
            '392', // 1839-08-01 11:57:36+00:00 CASSEUIL - Lanelongue Jean
            '394', // 1840-12-04 06:02:16+00:00 CASTERA-VERDUZA - Lannelongue Odilon
            '395', // 1856-11-08 11:36:16+00:00 CLICHY - Lannois Maurice
            '396', // 1866-08-01 13:34:08+00:00 EPINAL - Lapicque Louis
            '397', // 1831-11-17 09:37:08+00:00 VIENNE - Laroyenne Lucien
            '400', // 1870-04-29 16:35:24+00:00 ROCHE - Lasnet Alexandre
            '402', // 1877-08-20 04:39:52+00:00 DIJON - Latarjet Andre
            '403', // 1805-06-12 05:54:16+00:00 TOULOUSE - Latour Amedee
            '404', // 1872-11-11 21:45:40+00:00 ST-FLORENTIN - Laubry Charles
            '405', // 1850-09-25 13:57:28+00:00 AGEN - Laulanie Ferdinand
            '408', // 1803-05-15 20:06:20+00:00 NANTES - Lecadre Adolphe
            '409', // 1848-08-14 18:41:04+00:00 ROCROY - Le Double Anatole
            '410', // 1853-11-09 02:06:20+00:00 NANTES - Leduc Stephane
            '411', // 1819-07-25 06:46:40+00:00 BOURBON-LARCHAM - Lefort Jules
            '412', // 1829-12-05 16:47:40+00:00 LILLE - Lefort Leon
            '413', // 1869-03-30 18:47:40+00:00 LILLE - Lefort Rene
            '415', // 1820-05-01 18:35:16+00:00 METZ - Legouest Antoine
            '416', // 1861-05-07 19:31:28+00:00 CAEN - Legrand Hermann
            '418', // 1863-08-12 12:32:08+00:00 ANGERS - Legueu Felix
            '419', // 1863-01-30 22:54:00+00:00 UNVERRE - Lejars Felix
            '422', // 1847-04-17 11:55:00+00:00 AIXE-S/VIENNE - Lemaistre Justin
            '423', // 1880-04-14 14:55:16+00:00 BERNAY - Lemaitre Fernand
            '425', // 1856-08-27 01:54:40+00:00 VENDOME - Lemoine Georges
            '426', // 1864-11-03 04:22:24+00:00 MONTARGIS - Lenoble Emile
            '428', // 1840-07-06 21:40:40+00:00 LYON - Lepine Jacques
            '430', // 1879-10-12 14:42:28+00:00 ROANNE - Leriche Rene
            '431', // 1858-07-24 02:02:40+00:00 CAMBRAI - Gauquelin-A2-431
            '432', // 1825-10-13 20:50:48+00:00 ABBEVILLE - Le Roy Mericourt Alfred
            '433', // 1872-12-16 22:50:00+00:00 ST-DENIS - Le Roy Barres Adrien
            '434', // 1858-03-12 22:16:40+00:00 EBREUIL - Lesbre Francois
            '435', // 1871-02-11 04:57:36+00:00 BORDEAUX - Lesne Edmond
            '436', // 1853-03-19 08:59:40+00:00 MORTAGNE - Letulle Maurice
            '437', // 1825-03-14 09:55:40+00:00 ROUEN - Leudet Theodore
            '438', // 1818-11-23 11:57:36+00:00 BORDEAUX - Levieux Jean
            '441', // 1854-08-21 10:34:08+00:00 BAINVILLE-AUX-S - Liegeois Charles
            '442', // 1833-04-04 14:34:08+00:00 DOMREMY - Lietard Alexandre
            '443', // 1868-07-26 18:39:20+00:00 ST-MIHIEL - Lignieres Joseph
            '446', // 1850-05-19 10:38:32+00:00 MARSEILLE - Livon Charles
            '447', // 1878-08-27 00:35:52+00:00 ROUGEMONT - Lobstein Ernest
            '451', // 1811-05-25 10:51:28+00:00 ST-GERMAIN-EN-L - Gauquelin-A2-451
            '452', // 1836-08-22 02:40:40+00:00 OULLINS - Lortet Louis
            '454', // 1858-10-27 19:52:24+00:00 COURTENAY - Lucet Adrien
            '455', // 1862-10-19 09:05:52+00:00 BESANCON - Lumiere Auguste
            '456', // 1822-03-26 01:57:12+00:00 SORIGNY - Lunier Ludger
            '459', // 1835-03-16 07:48:20+00:00 PERPIGNAN - Magnan Jacques
            '460', // 1804-07-15 14:49:44+00:00 SAUVETERRE - Magne Jean
            '461', // 1830-06-24 09:11:00+00:00 TREDANIEL - Mahe Jean
            '462', // 1878-02-04 03:35:12+00:00 PONT-A-MOUSSON - Maillard Louis
            '463', // 1852-02-18 17:35:52+00:00 BADEVEL - Mairet Albert
            '465', // 1845-11-21 14:06:20+00:00 NANTES - Malherbe Albert
            '466', // 1857-01-01 12:16:40+00:00 VICHY - Gauquelin-A2-466
            '467', // 1805-02-12 09:18:56+00:00 CALAIS - Malle Pierre
            '468', // 1848-06-05 23:32:40+00:00 VALENCIENNES - Manouvriez Anatole
            '469', // 1853-12-02 00:37:08+00:00 GRENOBLE - Gauquelin-A2-469
            '470', // 1816-08-26 09:55:40+00:00 FECAMP - Marchand Eugene
            '471', // 1862-03-24 18:59:20+00:00 ST-AMANT-DE-BOI - Marchoux Francois
            '472', // 1830-03-05 19:39:52+00:00 BEAUNE - Marey Etienne
            '473', // 1858-06-23 01:50:36+00:00 CASTELNAUDARY - Marfan Jean
            '475', // 1869-06-01 11:39:52+00:00 FIXIN - Gauquelin-A2-475
            '476', // 1873-06-20 02:35:12+00:00 LOREY - Marotel Gabriel
            '477', // 1822-09-08 19:00:36+00:00 NEUF-BRISACH - Marquez Pierre
            '478', // 1808-11-06 15:51:28+00:00 VERSAILLES - Marrotte Joseph
            '479', // 1870-04-10 18:47:40+00:00 BASUEL - Martel Pierre
            '480', // 1843-05-17 00:42:28+00:00 ST-ETIENNE - Martin Claude
            '481', // 1864-09-20 04:44:28+00:00 LE PUY - Martin Louis
            '483', // 1844-05-21 01:04:40+00:00 ST-JEAN-D'ANGELY - Marvaud Angel
            '484', // 1857-11-12 03:44:28+00:00 MONTPELLIER - Massol Noel
            '485', // 1880-11-12 02:24:52+00:00 DIJON - Gauquelin-A2-485
            '486', // 1871-09-19 12:34:08+00:00 OLLIOULES - Gauquelin-A2-486
            '488', // 1850-10-05 02:54:00+00:00 CHARTRES - Maunoury Victor
            '489', // 1841-12-30 02:34:08+00:00 LUC - Maurel Edouard
            '490', // 1882-11-21 04:57:36+00:00 BORDEAUX - Mauriac Pierre
            '491', // 1833-01-07 01:10:56+00:00 VANNES - Gauquelin-A2-491
            '494', // 1866-02-11 05:46:40+00:00 MOULINS - Meige Henri
            '495', // 1860-01-10 02:02:32+00:00 BELFORT - Meillere Gedeon
            '496', // 1846-08-03 01:52:24+00:00 BEAUGENCY - Menard Toussaint
            '497', // 1871-01-04 18:02:08+00:00 VERNOILLE-FOURR - Mercier Raoul
            '498', // 1819-08-16 07:57:36+00:00 BORDEAUX - Merget Antoine
            '499', // 1874-04-25 19:30:36-01:00 GUEBWILLER - Gauquelin-A2-499
            '500', // 1862-10-17 17:24:00+00:00 CHARTRES - Mery Charles
            '501', // 1825-03-26 22:02:08+00:00 SAUMUR - Mesnet Urbain
            '502', // 1868-12-12 23:04:20+00:00 OMONVILLE-LA-PE - Mesnil Felix
            '503', // 1847-04-02 03:47:40+00:00 MARCQ-EN-BAROEU - Mesureur Gustave
            '504', // 1865-10-16 00:47:40+00:00 AVESNES - Gauquelin-A2-504
            '505', // 1807-11-05 21:51:28+00:00 VABRE - Mialhe Louis
            '506', // 1823-08-30 23:46:40+00:00 CHANTELLE - Gauquelin-A2-506
            '507', // 1866-02-21 15:06:20+00:00 NANTES - Mirallie Charles
            '509', // 1852-02-17 15:47:40+00:00 LE QUESNOY - Moniez Romain
            '512', // 1857-10-07 07:02:08+00:00 ST-GEORGES-S/LO - Gauquelin-A2-512
            '513', // 1804-05-07 14:44:28+00:00 MONTPELLIER - Moquin-Tandon Christian
            '514', // 1837-10-18 22:50:00+00:00 ST-DENIS - Morache Georges
            '515', // 1846-04-18 23:10:40+00:00 ST-SORLIN - Morat Jean
            '516', // 1822-10-28 10:59:12+00:00 LE MANS - Mordret Ambroise
            '518', // 1819-02-07 11:16:24+00:00 LANILIS - Morvan Augustin
            '519', // 1852-06-20 04:44:28+00:00 SETE - Mosse Alphonse
            '520', // 1846-03-10 19:06:40+00:00 MONTFORT - Motais Ernest
            '521', // 1832-09-06 13:59:12+00:00 LA FLECHE - Motet Auguste
            '522', // 1842-08-26 10:50:28+00:00 GRACAY - Mouchet Alphonse
            '524', // 1855-01-08 19:57:36+00:00 BORDEAUX - Moure Jean
            '525', // 1863-04-19 03:01:28+00:00 MOURENX - Moureux Charles
            '526', // 1873-10-08 12:42:36+00:00 VEZENOBRES - Mourier Louis
            '527', // 1880-06-18 00:40:24+00:00 BEAUFORT-S-GERV - Mouriquand Georges
            '529', // 1842-03-07 01:42:32+00:00 SEZANNE - Napias Henri
            '530', // 1855-09-20 17:29:00+00:00 STRASBOURG - Netter Juste
            '532', // 1838-05-10 17:42:32+00:00 MAREUIL-LE-PORT - Nicaise Jules
            '534', // 1861-03-01 08:05:12+00:00 PONT-A-MOUSSON - Nicolas Adolphe
            '536', // 1866-09-21 04:55:40+00:00 ROUEN - Nicolle Charles
            '537', // 1809-05-26 02:47:40+00:00 AIGUEPERSE - Nivet Annet
            '539', // 1850-01-29 16:49:20+00:00 PROVINS - Nocard Edmond
            '540', // 1824-02-27 10:51:28+00:00 FOURQUEUX - Notta Alphonse
            '541', // 1860-06-06 07:38:32+00:00 MARSEILLE - Oddo Pierre
            '542', // 1830-12-03 21:41:36+00:00 VANS - Ollier Leopold
            '543', // 1854-10-09 20:36:20+00:00 NANTES - Ollive Gustave
            '544', // 1833-05-13 19:59:12+00:00 ST-CALAIS - Gauquelin-A2-544
            '546', // 1828-02-15 10:57:36+00:00 BORDEAUX - Ore Pierre
            '547', // 1876-11-10 15:55:40+00:00 ROUEN - Oudard Pierre
            '548', // 1815-03-29 08:04:08+00:00 EPINAL - Oulmont Nathan
            '549', // 1867-05-24 08:47:40+00:00 CLERMONT FERRAND - Pachon Victor
            '550', // 1837-05-12 15:40:40+00:00 AVIGNON - Gauquelin-A2-550
            '551', // 1815-08-28 10:39:52+00:00 BEAUREGARD - Parise Jean
            '552', // 1882-06-15 17:35:12+00:00 NANCY - Parisot Jacques
            '553', // 1859-02-09 21:05:12+00:00 NANCY - Parisot Pierre
            '554', // 1829-11-10 09:57:04+00:00 EXCIDEUIL - Parrot Joseph
            '555', // 1822-12-27 01:37:48+00:00 DOLE - Pasteur Louis
            '559', // 1828-11-09 17:44:28+00:00 MONTPELLIER - Paulet Vincent
            '560', // 1876-08-03 02:08:32+00:00 AUBAGNE - Pautrier Lucien
            '562', // 1830-11-29 00:54:00+00:00 MARBOUE - Pean Jules
            '566', // 1835-09-23 10:27:36+00:00 PAUILLAC - Perier Jean
            '567', // 1844-05-09 05:52:56+00:00 TULLE - Perrier Edmond
            '568', // 1853-04-18 15:38:32+00:00 MARSEILLE - Perrin Joseph
            '569', // 1826-04-14 08:35:12+00:00 VEZELISE - Perrin Maurice
            '570', // 1867-08-14 13:42:32+00:00 MARCILLY-S/SEIN - Perrot Emile
            '571', // 1816-10-17 19:39:52+00:00 SAULIEU - Personne Jacques
            '573', // 1870-02-21 12:58:40+00:00 POITIERS - Petit Gabriel
            '575', // 1843-11-19 11:57:04+00:00 PERIGUEUX - Peyrot Jean
            '576', // 1801-03-04 07:42:32+00:00 MARFAUX - Philippe Adrien
            '577', // 1863-10-03 08:48:00+00:00 DOUERA - Pic Adrien
            '578', // 1839-10-29 16:35:12+00:00 ST-NICOLAS-DU-P - Picot Jean
            '579', // 1808-10-02 13:37:48+00:00 ORGELET - Pidoux Hermann
            '582', // 1816-01-28 17:18:56+00:00 BREBIERES - Pilat Eugene
            '583', // 1844-02-04 17:43:40+00:00 MERY-S-SEINE - Pinard Adolphe
            '585', // 1857-12-07 02:35:24+00:00 CHARCENNES - Piot Jean
            '586', // 1848-08-26 05:57:36+00:00 BORDEAUX - Gauquelin-A2-586
            '587', // 1833-10-29 20:44:28+00:00 GANGES - Planchon Francois
            '588', // 1823-03-21 13:44:28+00:00 GANGES - Planchon Jules
            '589', // 1828-08-16 04:05:12+00:00 NANCY - Poincare Emile
            '590', // 1853-02-09 04:04:20+00:00 GRANVILLE - Poirier Paul
            '592', // 1836-02-17 04:40:40+00:00 LYON - Polaillon Joseph
            '595', // 1849-03-28 08:39:08+00:00 ST-TRIVIER-S/MO - Poncet Antonin
            '597', // 1866-05-22 11:13:40+00:00 BAR-S-SEINE - Portier Paul
            '599', // 1853-06-08 10:04:40+00:00 SAINTES - Pousson Eugene
            '600', // 1841-10-03 13:57:04+00:00 BERGERAC - Pozzi Samuel
            '601', // 1861-11-05 05:40:40+00:00 LYON - Prenant Louis
            '602', // 1834-03-18 22:54:00+00:00 ILLIERS - Proust Adrien
            '603', // 1841-08-26 19:48:56+00:00 ARRAS - Prunier Leon
            '605', // 1842-02-26 18:38:32+00:00 MARSEILLE - Queirel Auguste
            '606', // 1852-07-21 03:48:56+00:00 MARQUISE - Quenu Edouard
            '608', // 1841-12-26 21:52:32+00:00 LAFAT - Quinquaud Charles
            '609', // 1861-01-18 19:59:12+00:00 PRUILLE-LEGUILL - Radais Maxime
            '610', // 1852-03-11 17:41:04+00:00 LA NEUVILLE-LES - Raillet Alcide
            '611', // 1886-09-30 07:45:40+00:00 BELLECHAUME - Ramon Gaston
            '612', // 1834-07-12 08:57:28+00:00 RAZIMET - Ranse Felix
            '613', // 1835-10-02 03:40:40+00:00 LYON - Ranvier Louis
            '615', // 1872-08-02 18:51:16+00:00 ST-CLOUD - Gauquelin-A2-615
            '616', // 1844-09-29 04:06:40+00:00 ST-CHRISTOPHE - Raymond Fulgence
            '617', // 1866-11-02 18:34:08+00:00 PIGNANS - Raynaud Lucien
            '619', // 1847-03-07 16:46:28+00:00 ORTHEZ - Reclus Paul
            '620', // 1855-04-29 11:54:16+00:00 AUTERIVE - Gauquelin-A2-620
            '621', // 1850-11-07 11:39:52+00:00 CHATILLON S-SEIN - Regnard Paul
            '623', // 1871-12-29 19:35:16-01:00 BERTRANGE - Remlinger Paul
            '624', // 1864-08-02 04:47:24+00:00 PREMERY - Renault Jules
            '625', // 1805-02-11 17:52:00+00:00 ST-OUEN-LAUMONE - Renault Thomas
            '626', // 1844-12-07 01:06:40+00:00 LA HAYE-DESCART - Renaut Joseph
            '629', // 1846-08-28 11:59:12+00:00 LA FLECHE - Renou Joseph
            '630', // 1803-08-15 08:40:40+00:00 LYON - Requin Achille
            '631', // 1816-10-31 06:02:16+00:00 VIC-FEZENSAC - Reynal Jean
            '633', // 1829-02-03 03:35:24+00:00 ROCHE - Riche Alfred
            '635', // 1849-01-17 00:54:00+00:00 CHARTRES - Richer Paul
            '636', // 1816-03-16 02:39:52+00:00 DIJON - Richet Alfred
            '637', // 1850-08-26 21:20:40+00:00 PARIS - Gauquelin-A2-637
            '638', // 1827-11-25 21:24:52+00:00 CHATILLON S-SEIN - Riembault Alfred
            '639', // 1871-03-16 15:29:00-01:00 STRASBOURG - Rist Edouard
            '640', // 1801-11-17 06:38:32+00:00 MARSEILLE - Robert Cesar
            '641', // 1847-09-19 04:39:52+00:00 DIJON - Robin Albert
            '644', // 1853-10-29 12:16:24+00:00 BREST - Rochard Eugene
            '645', // 1819-10-30 03:11:00+00:00 ST-BRIEUC - Rochard Jules
            '649', // 1824-08-19 21:55:16+00:00 GISORS - Rouget Charles
            '650', // 1816-07-28 01:46:00+00:00 ORFEUILLETTES - Roussel Theophile
            '651', // 1873-10-10 05:57:28+00:00 ASTAFFORT - Routier Edmond
            '652', // 1875-12-23 04:46:00+00:00 BLEYMARD - Gauquelin-A2-652
            '653', // 1875-12-12 02:45:32+00:00 TROSLY-LOIRE - Gauquelin-A2-653
            '654', // 1853-12-17 21:59:20+00:00 CONFOLENS - Roux Emile
            '655', // 1867-01-12 03:48:20+00:00 ST-PAUL-DE-FENO - Gauquelin-A2-655
            '656', // 1874-03-14 03:18:56+00:00 NIELLES-LES-ARD - Sacquepee Ernest
            '657', // 1810-08-10 08:39:08+00:00 BOURG-EN-BRESSE - Sappey Constant
            '658', // 1833-08-10 02:48:56+00:00 CALAIS - Sarazin Charles
            '659', // 1881-05-22 00:55:00+00:00 LIMOGES - Gauquelin-A2-659
            '660', // 1830-01-07 12:29:00+00:00 STRASBOURG - Schlagdenhauffen Charles
            '661', // 1829-12-23 03:29:00+00:00 STRASBOURG - Schutzenberger Paul
            '662', // 1852-02-16 22:59:00+00:00 BONE - Gauquelin-A2-662
            '663', // 1860-10-18 09:57:36+00:00 ST-FORT - Sebileau Pierre
            '665', // 1818-02-06 00:30:36+00:00 RIBEAUVILLE - See Germain
            '666', // 1827-02-18 16:30:36+00:00 RIBEAUVILLE - See Marc
            '668', // 1878-03-25 11:35:12+00:00 VITERNE - Sencert Louis
            '669', // 1872-06-17 22:54:16+00:00 CORRONSAC - Sendrail Jean
            '671', // 1802-10-28 20:42:36+00:00 UZES - Serre Auguste
            '672', // 1816-07-30 11:38:32+00:00 MARSEILLE - Seux Louis
            '673', // 1843-06-01 01:39:00+00:00 LOUVILLE - Sevestre Louis
            '675', // 1860-12-27 08:59:20+00:00 LA FAYE - Sieur Celestin
            '676', // 1866-11-23 09:57:28+00:00 STE-BAZEILLE - Sigalas Clement
            '677', // 1858-07-30 10:40:24+00:00 BEAUFORT-S-GERV - Simond Paul
            '678', // 1856-04-03 09:39:52+00:00 LA VILLENEUVE-L - Siredey Armand
            '679', // 1831-02-22 18:39:52+00:00 LA VILLENEUVE-L - Siredey Francois
            '680', // 1834-05-12 10:37:08+00:00 VIENNE - Soulier Henri
            '681', // 1860-02-06 01:49:44+00:00 PEYRE - Souques Alexandre
            '682', // 1875-08-20 17:35:12+00:00 NANCY - Gauquelin-A2-682
            '683', // 1844-02-16 15:35:12+00:00 NANCY - Spillmann Paul
            '684', // 1803-02-16 06:29:00+00:00 STRASBOURG - Stoeber Daniel
            '685', // 1803-12-14 09:29:00+00:00 ANDLAU-AU-VAL - Stoltz Joseph
            '686', // 1845-03-24 12:29:00+00:00 DAMBACH - Strauss Isidore
            '687', // 1852-09-23 19:35:24+00:00 RONCHAMP - Strauss Paul
            '688', // 1887-03-20 05:58:40+00:00 POITIERS - Strohl Andre
            '689', // 1826-08-08 08:45:32+00:00 BETHANCOURT - Surmay Charles
            '693', // 1828-04-29 05:39:52+00:00 AIZERAY - Tarnier Etienne
            '694', // 1813-04-13 14:40:40+00:00 LYON - Teissier Benoit
            '695', // 1861-09-09 13:50:28+00:00 NERONDES - Temoin Henri
            '696', // 1872-04-24 06:50:48+00:00 AMIENS - Terrien Felix
            '699', // 1839-03-09 19:45:40+00:00 TONNERRE - Gauquelin-A2-699
            '700', // 1869-09-09 01:55:40+00:00 ROUEN - Thiroux Andre
            '701', // 1839-05-21 16:57:12+00:00 LIGUEIL - Thomas Louis
            '702', // 1843-05-04 21:40:40+00:00 DUERNE - Thomas Philippe
            '703', // 1873-11-07 19:51:40+00:00 MOUY - Tiffeneau Adolphe
            '704', // 1834-12-08 21:01:28+00:00 AUNAY-S/ODON - Tillaux Paul
            '705', // 1829-10-17 01:25:40+00:00 ROUEN - Tillot Emile
            '706', // 1851-10-01 00:40:40+00:00 LYON - Gauquelin-A2-706
            '707', // 1849-06-08 02:57:04+00:00 BEAUMONT - Testut Jean
            '708', // 1810-01-21 00:29:00+00:00 STRASBOURG - Tourdes Gabriel
            '709', // 1881-01-12 05:04:40+00:00 LA ROCHELLE - Tournade Andre
            '710', // 1838-05-08 16:54:40+00:00 LAMOTTE-BEUVRON - Trasbot Laurent
            '711', // 1828-05-15 04:05:40+00:00 MONTAIGU - Trastour Etienne
            '712', // 1801-12-12 00:06:20+00:00 NANTES - Gauquelin-A2-712
            '715', // 1843-02-22 05:42:36+00:00 SURMENE - Triaire Paul
            '716', // 1861-02-14 04:37:08+00:00 PONT-DE-BEAUVOI - Trillat Auguste
            '718', // 1857-03-26 01:59:40+00:00 BELLEME - Tuffier Theodore
            '719', // 1850-10-03 00:54:40+00:00 MONTAUBAN - Vaillard Louis
            '720', // 1874-06-16 21:39:52+00:00 DIJON - Vallee Henri
            '722', // 1833-11-27 09:06:20+00:00 NANTES - Vallin Emile
            '725', // 1870-11-10 21:47:40+00:00 LILLE - Vanverts Julien
            '727', // 1826-01-13 18:57:28+00:00 FUMEL - Vedrenes Jean
            '728', // 1887-04-13 09:47:24+00:00 ALLIGNY-COSNE - Velu Henri
            '730', // 1859-12-22 07:37:08+00:00 VIENNE - Vialleton Louis
            '731', // 1834-07-26 22:34:08+00:00 TRANS - Vidal Emile
            '734', // 1827-01-24 13:34:08+00:00 PREY - Villemin Jean
            '736', // 1862-12-22 05:57:36+00:00 BORDEAUX - Vincent Hyacinthe
            '737', // 1842-06-29 09:16:24+00:00 BREST - Vincent Louis
            '738', // 1809-10-05 10:39:28+00:00 VIGNORY - Voillemier Leon
            '739', // 1887-02-25 01:38:32+00:00 MARSEILLE - Volmar Victor
            '742', // 1863-07-01 11:38:32+00:00 MARSEILLE - Wallich Victor
            '743', // 1855-02-23 10:04:40+00:00 ROCHEFORT S-MER - Walther Charles
            '744', // 1858-02-08 05:29:00+00:00 HAGUENAU - Weill Edmond
            '746', // 1859-04-26 10:29:00+00:00 BISCHWILLER - Weiss Georges
            '747', // 1852-07-24 10:29:00+00:00 ROSHEIM - Wertheimer Emile
            '748', // 1826-09-25 22:30:36+00:00 WINTZENHEIM - Widal Henri
            '749', // 1811-01-19 17:48:56+00:00 MONTREUIL-S-MER - Woillez Eugene
            '750', // 1817-11-26 02:29:00+00:00 STRASBOURG - Wurtz Adolphe
            '751', // 1848-01-18 02:54:40+00:00 SELOMMES - Yvon Paul
        ],
        '508MED' => [
            '5', // 1890-06-12 10:16:40+00:00 VERNEIX - Alajouanine Theophile
            '132', // 1898-05-30 05:50:40+00:00 BORDEAUX - Cathala Louis
            '139', // 1883-06-13 15:50:16+00:00 CONDAT-EN-FENIE - Chabrol Etienne
            '140', // 1882-01-01 20:16:00+00:00 MENDE - Chalier Andre
            '277', // 1899-06-05 04:00:00-01:00 PISTORF - Fontaine Rene
            '303', // 1888-10-10 05:41:36+00:00 PRIVAS - Giraud Gaston
            '420', // 1875-01-20 10:16:24+00:00 BREST - Le Lorier Victor
            '439', // 1877-01-20 10:15:32+00:00 MONT-SAINT-PERE - Lhermitte Jean
            '440', // 1882-01-04 01:45:40+00:00 TREIGNY - Lian Camille
            '508', // 1885-05-20 04:50:16+00:00 CERNIN - Mondor Henri
            '594', // 1889-05-25 17:00:36-01:00 MULHOUSE - Polonovsky Michel
            '674', // 1880-12-26 07:48:00+00:00 ALGER - Sezary Albert
            '690', // 1895-07-19 03:10:40+00:00 LILLE - Surmont Jean
            '735', // 1879-09-26 05:37:24+00:00 INGRE - Vincent Clovis
            '745', // 1875-03-14 11:51:28+00:00 VERSAILLES - Weill-Halle Benjamin
            '753', // 1885-06-05 00:39:28+00:00 BOURBONNE-LES-B - Abel Emile
            '754', // 1883-08-14 15:38:32+00:00 AIX-EN-PROVENCE - Abram Paul
            '755', // 1897-01-23 18:50:40+00:00 MARSEILLE - Acquaviva Eugene
            '756', // 1877-07-02 07:51:28+00:00 ALBI - Agasse-Lafont Jean
            '757', // 1886-01-03 03:15:00+00:00 SETIF - Gauquelin-A2-757
            '758', // 1885-09-24 01:50:16+00:00 ST-SIMON - Alary Emile
            '759', // 1894-11-30 05:50:40+00:00 SABLE - Allard Marcel
            '760', // 1862-10-06 07:44:28+00:00 CAZOULS-LES-BEZ - Alquier Augustin
            '762', // 1876-06-05 10:10:40+00:00 CHALON S-SAONE - Arcelin Fabien
            '763', // 1889-09-05 04:34:08+00:00 POITIERS - Arnould Jean
            '764', // 1907-11-02 02:50:40+00:00 BOULAY - Aron Emile
            '765', // 1893-03-30 00:50:40+00:00 CHATEAU-DOLERON - Gauquelin-A2-765
            '766', // 1903-05-18 15:50:40+00:00 MARSEILLE - Assada Marc
            '767', // 1876-11-12 02:38:32+00:00 AIX-EN-PROVENCE - Aubert Victor
            '768', // 1894-12-07 12:20:40+00:00 JOIGNY - Aubertin Emile
            '769', // 1888-03-10 12:48:56+00:00 BETHUNE - Aubertot Valery
            '770', // 1890-03-03 18:34:08+00:00 DRAGUIGNAN - Aublant Louis
            '771', // 1877-12-23 15:38:32+00:00 MARSEILLE - Audibert Victor
            '772', // 1865-09-30 09:39:08+00:00 MONTMERLE - Augros Francis
            '773', // 1870-12-25 09:31:24+00:00 BREST - Averous Joseph
            '774', // 1898-01-10 19:50:40+00:00 ORAN - Azerad Elie
            '775', // 1876-06-29 17:52:32+00:00 AUBUSSON - Babonneix Leon
            '776', // 1886-11-13 18:40:40+00:00 THIZY - Gauquelin-A2-776
            '777', // 1900-07-03 02:50:40+00:00 AMIENS - Baledent Maurice
            '778', // 1900-02-05 07:50:40+00:00 SAINT-PORCHAIRE - Balland Henri
            '779', // 1873-06-08 01:01:28+00:00 BAYONNE - Ball Victor
            '780', // 1883-09-05 08:30:56+00:00 ANTIBES - Baloux Paul
            '781', // 1884-03-08 08:40:24+00:00 VALENCE - Gauquelin-A2-781
            '782', // 1859-12-05 02:04:08+00:00 BRUYERE - Barbier Henri
            '783', // 1894-03-13 04:50:40+00:00 VIENNE - Barbier Jean
            '784', // 1895-12-10 07:20:40+00:00 ST-DIZIER - Barraud Jean
            '785', // 1905-05-17 23:50:40+00:00 SALLAUMINES - Gauquelin-A2-785
            '786', // 1892-06-23 12:50:40+00:00 SURY-LE-COMTAL - Barret Francisque
            '787', // 1882-08-16 03:00:36-01:00 BELFORT - Batier Gabriel
            '788', // 1854-09-11 01:06:20+00:00 NANTES - Baudoin Georges
            '789', // 1899-06-17 05:50:40+00:00 VUILLECIN - Baverel Gustave
            '790', // 1878-01-21 03:49:20+00:00 FONTAINEBLEAU - Bayard Joseph
            '791', // 1890-08-23 13:40:40+00:00 CAVAILLON - Bayol Pierre
            '792', // 1883-11-18 15:47:40+00:00 AMBERT - Gauquelin-A2-792
            '793', // 1867-10-01 21:27:04+00:00 MAREUIL-S/BELLE - Beaussemat Maurice
            '794', // 1878-12-03 10:35:00+00:00 MEZEL - Bec Fortune
            '795', // 1896-07-05 21:20:40+00:00 GONNEHEM - Gauquelin-A2-795
            '796', // 1891-02-08 22:13:56+00:00 ARRAS - Behague Pierre
            '797', // 1876-07-23 06:45:32+00:00 CHATEAU-THIERRY - Beliard Marcel
            '798', // 1885-08-27 01:22:24+00:00 PITHIVIERS - Belin Marcel
            '799', // 1876-04-15 15:46:40+00:00 YZEURE - Belot Joseph
            '800', // 1888-05-28 13:59:40+00:00 TARBES - Benech Jean
            '801', // 1896-02-26 00:50:40+00:00 NANCY - Benoit Jacques
            '802', // 1887-08-21 19:05:40+00:00 FONTENAY-LE-COM - Beraud Armand
            '803', // 1883-11-18 19:00:36-01:00 MULHOUSE - Gauquelin-A2-803
            '804', // 1884-11-10 18:09:52+00:00 MARCILLY-OGNY - Gauquelin-A2-804
            '805', // 1884-10-14 04:54:16+00:00 TOULOUSE - Berranger Paul
            '806', // 1877-12-08 16:39:08+00:00 THOISSEY - Berret Henri
            '807', // 1895-08-25 03:20:40+00:00 REMUZAT - Berthier Germain
            '808', // 1884-01-17 17:42:32+00:00 REIMS - Bettinger Lucien
            '809', // 1894-06-06 14:50:40+00:00 VIGEOIS - Beynes Edmond
            '810', // 1893-05-27 16:05:40+00:00 BESANCON - Gauquelin-A2-810
            '811', // 1893-02-24 06:50:40+00:00 MALESHERBES - Billard Jean
            '812', // 1882-02-03 19:53:16+00:00 CHATEAUROUX - Billet Henry
            '814', // 1887-05-05 15:44:20+00:00 CHASSENEUIL - Blanchier Denise
            '815', // 1903-09-09 08:50:40+00:00 AIX-LES-BAINS - Bleicher Maurice
            '816', // 1903-07-19 21:50:40+00:00 NANTES - Blineau Eugene
            '820', // 1891-05-28 07:50:40+00:00 BERGERAC - Boissiere-Lacroix Joseph
            '821', // 1872-09-11 22:49:44+00:00 DECAZEVILLE - Bondouy Theophile
            '822', // 1884-07-02 08:42:36+00:00 ALES - Gauquelin-A2-822
            '823', // 1859-09-07 05:19:44+00:00 AURIAC - Bonnefous Louis
            '824', // 1891-07-02 01:50:40+00:00 BOULOGNE-S-SEINE - Boppe Marcel
            '825', // 1877-02-27 09:52:32+00:00 DUNOIS - Bord Bejamin
            '826', // 1871-11-09 13:52:56+00:00 BRIVE - Bosche Charles
            '827', // 1880-08-23 18:37:08+00:00 VOIRON - Gauquelin-A2-827
            '828', // 1888-10-28 14:52:24+00:00 BEAUCHAMP - Bouchet Maurice
            '829', // 1892-02-07 01:50:40+00:00 PAU - Bouchoo Rene
            '830', // 1902-11-19 08:35:40+00:00 SENLIS - Boucomont Roger
            '831', // 1862-10-02 08:48:56+00:00 ST-OMER - Bouffe Saint Blaise Gabriel
            '832', // 1899-08-01 15:50:40+00:00 LUCHON - Boularan Jean
            '833', // 1878-11-23 03:40:40+00:00 AUTUN - Bourdier Ferdinand
            '834', // 1857-10-03 04:54:40+00:00 BLOIS - Boureau Eugene
            '836', // 1897-09-23 03:50:40+00:00 TOURNON-S-RHONE - Gauquelin-A2-836
            '837', // 1879-05-26 13:47:40+00:00 DOUAI - Bouvaist Joseph
            '838', // 1895-12-20 21:50:40+00:00 AGREVE - Boyer Paul
            '839', // 1907-12-11 12:50:40+00:00 AMIENS - Braillon Jean
            '840', // 1889-10-27 09:06:40+00:00 ST-MALO - Brault Pierre
            '841', // 1879-09-23 14:48:56+00:00 HARDINGHEM - Gauquelin-A2-841
            '843', // 1881-01-01 18:40:24+00:00 CREST - Bremond Maurice
            '844', // 1887-01-25 05:54:00+00:00 NOGENT-LE-ROTROU - Brisard Jules
            '845', // 1886-04-13 00:50:28+00:00 BOURGES - Brissaud Eugene
            '846', // 1872-06-03 01:59:40+00:00 LA COULONCHE - Brizard Charles
            '848', // 1883-01-31 16:05:48+00:00 AMIENS - Brule Marcel
            '849', // 1901-02-11 01:50:40+00:00 FONTAINES - Brunerie Albert
            '850', // 1877-06-22 10:39:28+00:00 BOURMONT - Bruntz Louis
            '851', // 1869-01-30 07:48:56+00:00 VAULX - Bue Vincent
            '852', // 1895-08-16 10:20:40+00:00 VALENCIENNES - Buisson Jean
            '853', // 1908-07-15 11:50:40+00:00 ST-MARTIN-DES-B - Buot Henri
            '854', // 1886-07-07 22:55:00+00:00 EYMOUTIERS - Bureau Louis
            '855', // 1879-11-21 05:02:16+00:00 CASTELNAU-DAUZA - Busquet Hector
            '856', // 1896-09-23 05:50:40+00:00 PAU - Cabille Henri
            '857', // 1901-03-07 01:50:40+00:00 LE HAVRE - Caillard Pierre
            '858', // 1873-03-03 18:37:48+00:00 LARNAUD - Caillon Louis
            '859', // 1888-01-18 07:38:32+00:00 MARSEILLE - Cambon Emile
            '860', // 1870-11-19 01:47:40+00:00 COUTICHES - Camelot Emile
            '861', // 1896-11-14 04:50:40+00:00 PLOUIGNEAU - Camus Jean
            '862', // 1879-09-15 01:49:20+00:00 FONTAINEBLEAU - Cantonnet Paul
            '863', // 1873-05-21 02:54:40+00:00 TOUFFAILLES - Capmas Albert
            '864', // 1901-06-05 10:50:40+00:00 AIX-EN-PROVENCE - Carcassonne Fernand
            '865', // 1873-03-27 08:40:24+00:00 MONTELIMAR - Carle Marius
            '866', // 1887-06-28 12:16:24+00:00 BREST - Carlerre Ernest
            '867', // 1894-09-24 07:20:40+00:00 GENERAC - Carlier Paul
            '868', // 1896-04-05 06:50:40+00:00 NICE - Carlotto Charles
            '869', // 1887-11-01 06:55:40+00:00 LE HAVRE - Carpentier William
            '870', // 1887-01-03 17:44:28+00:00 MONTPELLIER - Carrieu Marcel
            '871', // 1889-10-30 05:25:00+00:00 AJACCIO - Casabianca Jerome
            '872', // 1868-07-05 06:38:32+00:00 MARSEILLE - Cassoute Emile
            '873', // 1890-03-29 09:01:28+00:00 PAU - Castaing Louis
            '874', // 1885-05-25 08:02:00+00:00 ST-SEVER-S/ADOU - Castera Hector
            '875', // 1881-06-24 10:34:08+00:00 HYERES - Castueil Octave
            '876', // 1878-10-11 03:53:40+00:00 PAMIERS - Caujolle Paul
            '877', // 1885-01-23 17:35:24+00:00 BOUHANS-LES-AUT - Caussade Louis
            '878', // 1891-06-28 06:50:40+00:00 ARGELES-GAZOST - Cenac Michel
            '879', // 1875-12-28 17:47:24+00:00 MONTAMBERT - Chaix Achille
            '880', // 1895-06-21 01:50:40+00:00 VIRE - Chaperon Robert
            '881', // 1878-11-05 19:35:52+00:00 BESANCON - Gauquelin-A2-881
            '883', // 1884-12-17 00:57:36+00:00 BORDEAUX - Charbonnel Maurice
            '885', // 1903-03-16 18:50:40+00:00 CLERMONT FERRAND - Charpentier Roger
            '886', // 1888-07-21 03:52:56+00:00 BRIVE - Charrier Jean
            '887', // 1882-11-23 04:52:56+00:00 ST-YRIEIX-LE-DE - Chassagnard Jean
            '888', // 1881-07-18 19:07:48+00:00 BRACON - Gauquelin-A2-888
            '889', // 1886-09-04 02:40:40+00:00 APT - Chauvin Emile
            '890', // 1892-04-03 16:50:40+00:00 CONDAT - Chavany Jean
            '891', // 1895-04-10 08:50:40+00:00 CHAMPDENIERS - Chenilleau Andre
            '892', // 1877-01-27 03:35:40+00:00 FONTENAY-LE-COM - Chevrier Louis
            '894', // 1902-02-24 08:20:40+00:00 THIERS - Chosson Pierre
            '895', // 1873-01-17 15:50:28+00:00 GRACAY - Chretien Joseph
            '896', // 1874-04-18 10:47:40+00:00 CLERMONT FERRAND - Cisternes Ernest
            '897', // 1898-08-25 08:50:40+00:00 BARGEMON - Claudel Jean
            '898', // 1886-04-02 22:02:08+00:00 SAUMUR - Closier Louis
            '899', // 1890-07-01 01:59:12+00:00 CONLIE - Codet Henri
            '900', // 1893-08-30 05:20:40+00:00 ROUBAIX - Coliez Robert
            '901', // 1899-09-19 13:50:40+00:00 GRENOBLE - Comte Henri
            '902', // 1891-12-13 02:20:40+00:00 VALENCIENNES - Cordier Pierre
            '903', // 1890-06-30 10:59:12+00:00 LE MANS - Gauquelin-A2-903
            '904', // 1875-02-26 00:50:48+00:00 AMIENS - Corret Pierre
            '905', // 1896-03-19 03:50:40+00:00 NANCY - Gauquelin-A2-905
            '906', // 1871-11-10 00:50:28+00:00 BOURGES - Gauquelin-A2-906
            '908', // 1886-11-08 14:54:16+00:00 AZAS - Gauquelin-A2-908
            '909', // 1877-07-12 04:51:28+00:00 ALBI - Coste Jules
            '910', // 1882-10-19 02:39:28+00:00 DOULAINCOURT - Gauquelin-A2-910
            '911', // 1901-01-06 05:20:40+00:00 FESCHES-LE-CHAT - Cottet Pierre
            '912', // 1887-06-23 05:55:40+00:00 LE HAVRE - Coty Auguste
            '913', // 1887-07-12 05:24:08+00:00 BOURG-EN-BRESSE - Couinaud Paul
            '914', // 1891-10-24 08:50:40+00:00 ST-MAGNE - Courbin Pierre
            '915', // 1863-01-24 02:02:16+00:00 AUCH - Cournet Jean
            '916', // 1863-08-07 03:45:40+00:00 ST-JULIEN-DE-SA - Courtillier Leon
            '917', // 1852-07-05 10:59:20+00:00 FONTAINEBLEAU - Coutan Ferdinand
            '918', // 1899-12-12 10:50:40+00:00 LONGWY - Crehange Jean
            '919', // 1874-07-16 13:18:56+00:00 ARQUES - Gauquelin-A2-919
            '920', // 1871-05-11 15:42:28+00:00 ST-GERMAIN-LAVA - Crozet Joannes
            '921', // 1901-07-24 23:30:00-01:00 BRUMATT - Damm Louis
            '922', // 1901-08-25 02:50:40+00:00 SARS-POTERIE - Danhiez Pierre
            '925', // 1873-11-22 01:40:40+00:00 CHAROLLES - Darbois Paul
            '927', // 1881-07-31 13:47:24+00:00 GUERIGNY - Dariaux Andre
            '928', // 1881-07-23 02:37:08+00:00 GRENOBLE - Debon Albert
            '932', // 1884-10-24 21:17:40+00:00 BOURBOURG - Dehorter Leon
            '933', // 1888-02-07 20:49:44+00:00 MONTCLAR - Dejean Charles
            '934', // 1896-02-12 15:20:40+00:00 LE MANS - Delageniere Yves
            '935', // 1883-07-18 05:46:00+00:00 MENDE - Delater Gabriel
            '936', // 1888-04-11 23:41:04+00:00 DOM-LE-MESNIL - Gauquelin-A2-936
            '938', // 1906-07-03 01:20:40+00:00 BORDEAUX - Delluc Paul
            '939', // 1880-08-12 09:07:40+00:00 MARCOING - Gauquelin-A2-939
            '940', // 1898-08-04 07:50:40+00:00 DAX - Delmas-Marsalet Paul
            '941', // 1885-01-03 21:40:40+00:00 ST-JULIEN-DE-CI - Demole Louis
            '942', // 1891-08-13 09:50:40+00:00 CAUMONT - Denoyelle Lucien
            '943', // 1900-06-05 08:20:40+00:00 COMPIEGNE - Deruas Pierre
            '944', // 1880-07-18 13:57:28+00:00 ARGUILLON - Gauquelin-A2-944
            '945', // 1880-08-26 06:52:32+00:00 ST-BARD - Gauquelin-A2-945
            '946', // 1892-08-14 08:50:40+00:00 HURIEL - Desmaroux Louis
            '947', // 1876-12-03 11:47:40+00:00 LILLE - Desplats Rene
            '948', // 1889-03-28 15:50:48+00:00 AMIENS - Dherissart Jean
            '949', // 1897-08-21 09:50:40+00:00 ESPALY-ST-MARCEL - Digonnet Louis
            '950', // 1890-01-20 17:24:16+00:00 CAHORS - Dillenseger Rene
            '951', // 1888-04-26 01:57:12+00:00 TOURS - Diocles Louis
            '952', // 1898-08-03 05:50:40+00:00 SELESTAT - Diss Marius
            '953', // 1906-09-19 07:50:40+00:00 FOUGERES - Divet Henri
            '954', // 1862-10-05 03:38:32+00:00 WASSELONNE - Dollinger Ferdinand
            '955', // 1899-09-22 15:50:40+00:00 PARLEBOSQ - Dorbes Maurice
            '956', // 1882-10-27 00:13:40+00:00 BRIENNE-LE-CHAT - Dournay Jean
            '957', // 1890-07-02 22:42:32+00:00 PONT-FAVERGER - Resch Pierre
            '960', // 1869-12-28 07:47:40+00:00 EONES - Ducamp Louis
            '961', // 1860-03-09 01:59:40+00:00 LESCURRY - Duco Alexandre
            '963', // 1899-06-08 08:50:40+00:00 TOULOUSE - Dulac Jean
            '964', // 1882-02-11 00:42:28+00:00 ST-ETIENNE - Dumas Antoine
            '965', // 1880-03-07 13:37:08+00:00 PONT-DE-CHERUY - Gauquelin-A2-965
            '966', // 1884-11-10 05:35:00+00:00 ST-PONS - Gauquelin-A2-966
            '967', // 1888-02-04 14:30:56+00:00 NICE - Duplay Marcel
            '968', // 1902-11-20 15:50:40+00:00 CLERMONT FERRAND - Duranton Raoul
            '969', // 1890-04-25 13:42:28+00:00 ST-GENEST-LERPT - Durupt Auguste
            '970', // 1906-08-30 15:20:40+00:00 BOURESSE - Dussouil Rene
            '971', // 1900-06-03 01:00:00-01:00 STRASBOURG - Eber Edgar
            '972', // 1870-05-12 10:30:36+00:00 GUEBERSCHWILER - Ehret Henri
            '973', // 1881-04-13 05:35:24+00:00 LURE - Ehringer Charles
            '975', // 1904-03-09 00:50:40+00:00 BARCELONNETTE - Emperaire Roger
            '976', // 1865-06-08 09:54:16+00:00 TOULOUSE - Escat Etienne
            '977', // 1900-02-19 08:50:40+00:00 TALENCE - Gauquelin-A2-977
            '978', // 1871-06-16 10:39:08+00:00 NANTUA - Etienne-Martin
            '980', // 1894-07-26 23:20:40+00:00 STE BAZEILLE - Fabre Jean
            '981', // 1895-03-20 03:20:40+00:00 ST-OMER - Faillie Robert
            '982', // 1880-03-06 08:54:00+00:00 CHATEAUDUN - Fayolle Henri
            '983', // 1871-12-05 19:44:28+00:00 LE PUY - Ferry Pierre
            '984', // 1897-10-09 21:20:40+00:00 ARRAS - Fevre Marcel
            '986', // 1898-03-27 12:20:40+00:00 FARGUIERS - Filachet Rene
            '988', // 1890-07-24 13:28:40+00:00 LA TREMOUILLE - Fombeure Georges
            '989', // 1877-11-05 07:42:28+00:00 ST-ETIENNE - Fontanilles Eugene
            '991', // 1878-09-01 15:00:36-01:00 MASEVAUX - Forster Andre
            '992', // 1893-07-21 04:20:40+00:00 FONTAINEBLEAU - Foucault Paul
            '993', // 1889-01-21 12:19:20+00:00 CHERBOURG - Fouque Charles
            '994', // 1885-02-05 00:53:40+00:00 ST-GIRONS - Fourcade Maurice
            '995', // 1873-09-02 05:37:08+00:00 VIENNE - Gauquelin-A2-995
            '996', // 1869-01-08 03:29:00+00:00 STRASBOURG - Freysz Maurice
            '997', // 1895-05-06 17:50:40+00:00 NIEULLE-VIROUIL - Fumeau Pierre
            '998', // 1878-05-24 11:39:52+00:00 DIJON - Galimard Joseph
            '999', // 1900-06-25 14:50:40+00:00 ROUILLAC - Gallais Pierre
            '1001', // 1861-11-04 10:45:32+00:00 SEBONCOURT - Gand Charles
            '1002', // 1875-07-30 00:34:08+00:00 PUGET-S/ARGENT - Gauquelin-A2-1002
            '1003', // 1888-08-03 14:40:24+00:00 VALENCE - Gaucherand Jules
            '1005', // 1875-10-24 01:22:24+00:00 ORLEANS - Gaultier Rene
            '1006', // 1893-02-07 14:50:40+00:00 CHENILLE - Gautier Jean
            '1007', // 1887-05-04 08:36:00+00:00 GAP - Gerard Felix
            '1008', // 1902-07-06 10:50:40+00:00 TOULOUSE - Geraud Louis
            '1009', // 1898-07-28 10:50:40+00:00 EPINAL - Gerbaut Pierre
            '1010', // 1904-06-07 15:50:40+00:00 ROUBAIX - Gernez Louis
            '1011', // 1883-02-07 12:42:28+00:00 ST-ETIENNE - Gery Louis
            '1012', // 1890-10-05 08:50:16+00:00 CHAUDES-AIGUES - Gauquelin-A2-1012
            '1013', // 1897-02-05 11:50:40+00:00 VALENSOLE - Ginsburg Benjamin
            '1014', // 1898-06-18 19:50:40+00:00 MONTPEZAT - Girard Ismael
            '1015', // 1888-08-22 09:55:16+00:00 VERNEUIL - Giroux Rene
            '1017', // 1880-09-30 07:40:40+00:00 BOURBON-LANCY - Glenard Roger
            '1018', // 1875-07-05 00:40:40+00:00 ST-GEORGES-DE-R - Gonnet Charles
            '1019', // 1891-01-11 09:01:28+00:00 CAEN - Gosselin Louis
            '1020', // 1882-04-08 08:35:12+00:00 NANCY - Grandgerard Roger
            '1021', // 1878-11-05 08:46:40+00:00 MONTLUCON - Gauquelin-A2-1021
            '1023', // 1892-01-10 10:50:40+00:00 RIOM - Gauquelin-A2-1023
            '1025', // 1853-08-05 09:52:24+00:00 ORLEANS - Greffier Paul
            '1026', // 1902-06-05 19:05:40+00:00 VICHY - Grenaud Marcel
            '1027', // 1875-02-10 05:57:28+00:00 VILLEREAL - Gauquelin-A2-1027
            '1028', // 1866-12-20 15:30:56+00:00 NICE - Grinda Edouard
            '1029', // 1899-02-06 20:20:40+00:00 MILLY-LA-FORET - Grognot Germain
            '1030', // 1890-04-05 02:59:00-01:00 STRASBOURG - Gross Albert
            '1031', // 1883-10-29 03:06:40+00:00 LA HAYE-DESCART - Guerithault Bernard
            '1032', // 1884-09-22 09:44:28+00:00 AIGUILHE - Guichard Paul
            '1033', // 1891-07-14 11:50:40+00:00 NANCY - Guillemin Andre
            '1034', // 1874-04-25 23:26:04+00:00 VOUZIERS - Gauquelin-A2-1034
            '1035', // 1881-09-04 10:04:40+00:00 COZES - Guimbellot Marcel
            '1038', // 1876-11-19 07:29:00-01:00 STRASBOURG - Gunsett Auguste
            '1039', // 1882-07-20 11:51:28+00:00 LABESSONNIE - Guy Maurice
            '1040', // 1874-09-04 15:36:20+00:00 BASSE-GOULAINE - Guyard Georges
            '1041', // 1876-12-18 13:46:40+00:00 ESENROLLES - Gauquelin-A2-1041
            '1042', // 1869-08-24 11:35:16+00:00 LUTZELBOURG - Haller Prosper
            '1043', // 1901-09-01 17:50:40+00:00 PAULHAN - Harant Herve
            '1044', // 1877-02-03 11:47:40+00:00 GRAVELINES - Hautefeuille Jules
            '1046', // 1901-07-04 09:50:40+00:00 LONS-LE-SAUNIER - Heitz Jean
            '1047', // 1893-05-27 14:50:40+00:00 ORAN - Gauquelin-A2-1047
            '1048', // 1880-01-10 21:35:12+00:00 NANCY - Herbier Paul
            '1049', // 1892-12-19 19:50:40+00:00 LUNEVILLE - Hermann Henri
            '1050', // 1885-04-29 00:50:28+00:00 BOURGES - Hervoche Charles
            '1052', // 1883-05-28 13:46:00+00:00 FLORAC - Heymann Paul
            '1053', // 1906-01-20 00:20:40+00:00 BOULOGNE-S-MER - Houzel Guy
            '1056', // 1887-12-16 18:49:44+00:00 ST-AFFRIQUE - Gauquelin-A2-1056
            '1057', // 1882-12-29 23:19:40+00:00 SAINTES - Jaulin Seutre Auguste
            '1058', // 1901-07-21 01:50:40+00:00 PLAISANCE-DU-GE - Jaymes Bernard
            '1059', // 1900-06-04 16:50:40+00:00 ROUEN - Jean Bernard
            '1060', // 1891-01-05 16:43:40+00:00 TROYES - Jeannet Pierre
            '1061', // 1894-07-11 04:50:40+00:00 BREIL - Jeudon Robert
            '1062', // 1878-07-17 06:01:28+00:00 CONDE-S/NOIREAU - Jouvain Andre
            '1064', // 1881-09-11 06:39:20+00:00 MAXEY-S/VAISE - Joyeux Charles
            '1065', // 1899-08-14 10:20:40+00:00 ST-POL-DE-LEON - Jube Louis
            '1067', // 1883-08-29 21:59:00-01:00 SAVERNE - Keller Raymond
            '1068', // 1886-05-11 21:34:08+00:00 RUPT - Gauquelin-A2-1068
            '1070', // 1897-09-14 03:50:40+00:00 BAR-LE-REGULIER - Lacomme Maurice
            '1071', // 1887-09-18 09:32:00+00:00 ORAN - Lacronique Gaston
            '1072', // 1901-09-09 02:50:40+00:00 NEUILLY - Laennec Theophile
            '1073', // 1900-07-05 03:50:40+00:00 TULLE - La Farge Georges
            '1074', // 1869-09-11 14:06:40+00:00 NERIS - Lafont Alexandre
            '1075', // 1890-12-23 19:57:36+00:00 BORDEAUX - Lagrange Henri
            '1076', // 1863-02-27 11:57:36+00:00 BAZAS - Lamarque Henri
            '1077', // 1906-05-14 01:50:40+00:00 COMINES - Lamelin Pierre
            '1078', // 1874-07-20 19:44:28+00:00 MIREVAL - Lamouraux Fernand
            '1079', // 1874-08-20 14:04:20+00:00 AVRANCHES - Lance Marcel
            '1081', // 1883-12-25 02:42:32+00:00 REIMS - Langlet Jean
            '1082', // 1891-05-31 21:20:40+00:00 VITTEAUX - Larget Maurice
            '1083', // 1883-11-21 05:50:16+00:00 CEZENS - La Roche Brisson Rene
            '1084', // 1878-06-24 07:42:28+00:00 ST-ETIENNE - Gauquelin-A2-1084
            '1085', // 1882-06-27 08:46:40+00:00 COURCAIS - Lasseur Philippe
            '1086', // 1901-06-13 12:50:40+00:00 CHALONS-S/MARNE - Laurent Roger
            '1088', // 1896-06-16 08:50:40+00:00 BONE - Layani Fernand
            '1089', // 1878-01-02 10:16:24+00:00 ST-POL-DE-LEON - Lazennec Isidore
            '1090', // 1880-09-24 05:59:40+00:00 FLERS-DE-LORNE - Gauquelin-A2-1090
            '1091', // 1901-12-14 11:50:40+00:00 ST-MICHEL-EN-LH - Lebleu Albert
            '1092', // 1891-08-20 08:50:40+00:00 MEDREAC - Le Branchu Rene
            '1093', // 1910-01-19 13:50:40+00:00 BADEN - Le Corre Joseph
            '1094', // 1897-06-15 02:50:40+00:00 CROUY - Lefevre Raymond
            '1095', // 1900-08-18 22:50:40+00:00 LOCHES - Lefort Edmond
            '1096', // 1900-04-23 16:50:40+00:00 BEGARD - Legac Paul
            '1098', // 1881-05-05 10:16:24+00:00 QUIMPER - Le Gorgeu Victor
            '1100', // 1892-01-10 17:50:40+00:00 AUBIGNY - Gauquelin-A2-1100
            '1101', // 1879-10-26 08:41:00+00:00 ST-BRIEUC - Lemoine Francisque
            '1102', // 1890-09-07 18:04:20+00:00 CHERBOURG - Lemperiere Jean
            '1103', // 1863-06-19 21:16:24+00:00 ERGUE-GABERIC - Le Naour Pierre
            '1104', // 1883-12-13 06:01:28+00:00 BAYONNE - Leon-Kindberg Michel
            '1105', // 1892-05-17 19:50:40+00:00 BAIN-DE-BRETAGNE - Le Pennetier Francois
            '1107', // 1882-08-19 00:47:40+00:00 ROUBAIX - Gauquelin-A2-1107
            '1108', // 1894-03-22 07:50:40+00:00 NEVERS - Le Rasle Henri
            '1109', // 1901-05-16 12:50:40+00:00 ROMAGNY - Leroy Denis
            '1110', // 1861-04-22 01:55:40+00:00 LE HAVRE - Leroy Georges
            '1111', // 1883-03-23 20:59:12+00:00 LE MANS - Lesieur Emile
            '1112', // 1885-04-14 05:44:28+00:00 BEZIERS - Levere Raymond
            '1113', // 1893-10-31 09:50:40+00:00 ORAN - Levy Maurice
            '1114', // 1898-07-09 12:50:40+00:00 ST-ANDRE-DE-SAN - L'Heritier Georges
            '1115', // 1877-12-13 11:12:28+00:00 ST-CHAMOND - Locard Edmond
            '1116', // 1894-04-04 20:50:40+00:00 MONTSAUCHE - Gauquelin-A2-1116
            '1117', // 1883-10-12 10:36:00+00:00 EMBRUN - Loppe Etienne
            '1118', // 1890-02-01 05:47:40+00:00 CUNLHAT - Lossedat Maurice
            '1120', // 1861-06-03 02:39:52+00:00 LAIGUES - Lucas Arthur
            '1121', // 1895-09-10 23:20:40+00:00 DIJON - Gauquelin-A2-1121
            '1122', // 1887-03-26 16:47:24+00:00 LUZY - Luquet Gabriel
            '1123', // 1884-09-21 14:51:28+00:00 JOUY-EN-JOSAS - Lutembacher Rene
            '1124', // 1890-11-18 06:48:56+00:00 BERCK-S-MER - Macquet Pierre
            '1125', // 1880-01-18 09:44:28+00:00 LANGEAC - Malien Campsavy Georges
            '1126', // 1865-09-01 16:41:04+00:00 ROCROY - Gauquelin-A2-1126
            '1127', // 1883-02-28 02:42:36+00:00 UCHAUD - Margarot Jean
            '1128', // 1899-05-03 10:50:40+00:00 PONT-AUDEMER - Marie Julien
            '1129', // 1908-03-19 06:50:40+00:00 TOULOUSE - Marques Pierre
            '1130', // 1879-05-27 03:06:40+00:00 BECHEREL - Marquis Eugene
            '1131', // 1900-03-31 01:50:40+00:00 NANCY - Marsal Pierre
            '1132', // 1875-03-07 07:35:12+00:00 MAXEVILLE - Martel Janville Thierry
            '1133', // 1888-05-01 07:42:36+00:00 BESSEGUES - Martin Arthur
            '1134', // 1888-03-07 06:44:28+00:00 BESSAN - Martin Henry
            '1135', // 1900-09-10 10:20:40+00:00 ANGOULEME - Martinaud Georges
            '1136', // 1897-11-11 03:20:40+00:00 NICE - Martiny Marcel
            '1137', // 1889-06-22 03:31:28+00:00 HONFLEUR - Massart Raphael
            '1138', // 1889-06-17 14:45:32+00:00 LAON - Masselin Guy
            '1139', // 1900-02-27 05:00:00-01:00 SCHWEIGHAUSEN - Matter Willy
            '1140', // 1887-07-26 19:01:28+00:00 BAYONNE - Maurel Gerard
            '1141', // 1885-07-24 22:49:20+00:00 ELBEUF - Maurer Andre
            '1142', // 1894-05-23 19:20:40+00:00 NANTES - Meeus Emile
            '1143', // 1897-05-26 08:20:40+00:00 CHALONNES-S/LOI - Meignant Paul
            '1144', // 1870-09-25 03:04:40+00:00 ST-GENIS-DE-SAI - Menciere Louis
            '1145', // 1892-07-14 05:50:40+00:00 TOURS - Mercier-Cosse Armand
            '1146', // 1886-02-07 17:51:28+00:00 CASTRES - Meurisse Henri
            '1147', // 1886-03-23 04:05:52+00:00 DESANDANES - Meyer Henri
            '1148', // 1891-12-28 03:50:40+00:00 ST-ETIENNE - Michel Andre
            '1149', // 1873-05-18 10:02:08+00:00 SAUMUR - Mignon Maurice
            '1150', // 1886-09-12 17:35:00+00:00 RIEZ - Gauquelin-A2-1150
            '1151', // 1885-10-22 02:32:00+00:00 ORAN - Monbrun Albert
            '1152', // 1874-08-22 20:02:08+00:00 ROCHEFORT-S/LOI - Mondain Charles
            '1153', // 1906-07-25 19:50:40+00:00 AVIGNON - Montagard Georges
            '1154', // 1872-12-04 02:40:40+00:00 CAUMONT - Montagard Leon
            '1155', // 1886-11-13 10:45:40+00:00 BRIENON - Gauquelin-A2-1155
            '1156', // 1897-02-04 15:50:40+00:00 ALBERVILLE - Gauquelin-A2-1156
            '1157', // 1897-05-22 11:50:40+00:00 BOURBONNE-LES-B - Morel Max
            '1158', // 1882-05-15 23:57:40+00:00 ST-PRIEST-BRAME - Gauquelin-A2-1158
            '1159', // 1877-05-02 03:11:00+00:00 ST-BRIEUC - Morvan Jules
            '1160', // 1895-08-29 07:50:40+00:00 NANTEUIL-EN-VAL - Mothe Bernard
            '1162', // 1878-08-02 18:10:24+00:00 VALENCE - Mouriquand Emile
            '1163', // 1871-03-30 18:17:40+00:00 PASLIERES - Mouton-Chapat Barthelemy
            '1164', // 1887-03-07 01:35:32+00:00 SALLANCHES - Muraz Gaston
            '1165', // 1868-10-27 22:47:40+00:00 ROUBAIX - Musin Edmond
            '1166', // 1870-08-11 10:30:36+00:00 MULHOUSE - Mutterer Maurice
            '1167', // 1886-12-31 18:29:12+00:00 VIBRAYE - Neau Henry
            '1168', // 1879-06-15 11:44:28+00:00 MONTPELLIER - Negre Leopold
            '1169', // 1908-10-16 13:50:40+00:00 MONTOIR-DE-BRET - Nicolas Henri
            '1170', // 1885-04-19 05:37:24+00:00 ORLEANS - Gauquelin-A2-1170
            '1171', // 1878-06-30 18:21:16+00:00 ST-CLOUD - Oppert Edouard
            '1172', // 1876-01-12 07:02:08+00:00 ANGERS - Papin Edmond
            '1173', // 1890-05-30 21:59:20+00:00 SEGONZAC - Papin Edouard
            '1174', // 1894-07-05 17:50:40+00:00 PONS - Papin Marc
            '1176', // 1883-04-27 13:55:40+00:00 DIEPPE - Parrel Gerard
            '1177', // 1873-10-01 08:30:56+00:00 NICE - Gauquelin-A2-1177
            '1178', // 1876-09-23 02:35:52+00:00 VUILLAFANS - Pasteur Felix
            '1179', // 1889-12-26 18:46:40+00:00 ST-GERMAIN-DE-S - Paturet Georges
            '1181', // 1871-06-21 17:39:20+00:00 DOMPIERRE-LES-B - Pechin Charles
            '1182', // 1898-01-19 21:50:40+00:00 MEILLAC - Pelle Abel
            '1183', // 1882-11-12 08:28:40+00:00 POITIERS - Perdoux Joseph
            '1185', // 1879-07-10 21:29:00+00:00 BONE - Perret Albert
            '1186', // 1875-05-21 03:34:08+00:00 RAMBERVILLIERS - Perrin Maurice
            '1187', // 1895-09-19 17:50:40+00:00 MALO-LES-BAINS - Perrin Paul
            '1188', // 1889-10-18 04:37:08+00:00 GRENOBLE - Perrin Robert
            '1189', // 1864-10-27 08:52:24+00:00 ORLEANS - Petit Georges
            '1190', // 1906-03-10 06:50:40+00:00 MONTCORNET - Petit Max
            '1191', // 1899-01-12 17:50:40+00:00 CORTE - Petrignani Roger
            '1192', // 1883-07-09 03:47:40+00:00 CHATELDON - Phelip J
            '1194', // 1909-08-21 17:50:40+00:00 DIGOIN - Pierre Maurice
            '1195', // 1900-08-17 12:20:40+00:00 VEZELISE - Pierson Antoine
            '1196', // 1898-07-21 12:20:40+00:00 DANVOU - Pigache Andre
            '1197', // 1886-04-29 15:16:24+00:00 LANDIVISIAU - Pilven Joseph
            '1198', // 1873-09-22 09:37:08+00:00 LA TRONCHE - Gauquelin-A2-1198
            '1199', // 1882-08-01 15:42:36+00:00 NIMES - Pironneau Pierre
            '1200', // 1891-08-07 08:50:40+00:00 AMIENS - Playoust Yves
            '1202', // 1881-12-02 17:51:40+00:00 COMPIEGNE - Poisonnier Max
            '1203', // 1904-07-18 23:30:00-01:00 VIGY - Polu Raymond
            '1204', // 1877-03-21 21:01:28+00:00 PAU - Pons Henri
            '1205', // 1873-03-14 01:38:32+00:00 PENNES-MIRABEAU - Porcheron Louis
            '1206', // 1883-10-01 07:36:40+00:00 CANCALE - Poree Francis
            '1207', // 1908-03-04 05:50:40+00:00 CHATEAUBOURG - Poree Prudent
            '1208', // 1895-09-22 08:30:00-01:00 MOOSCH - Prevot Maurice
            '1209', // 1885-08-07 01:01:48+00:00 SAUZE-VAUSSAIS - Prieur Maurice
            '1210', // 1877-08-06 14:51:40+00:00 GRANDVILLIERS - Queuille Adrien
            '1211', // 1886-06-10 06:47:40+00:00 VERNET-LA-VAREN - Quiquandon Joseph
            '1212', // 1883-06-12 08:21:28+00:00 RAMBOUILLET - Rabourdin Andre
            '1213', // 1880-02-15 08:49:20+00:00 ST-GERMAIN-LAVA - Rajat Henri
            '1214', // 1895-12-15 00:50:40+00:00 DIGNE - Raybaud Jean
            '1215', // 1899-06-24 14:50:40+00:00 ST-CIRQ-LA-POPI - Redon Henri
            '1217', // 1901-12-07 17:10:40+00:00 VALENCE - Regard Jean
            '1218', // 1873-06-01 09:34:20+00:00 HAMBYE - Regnault Jules
            '1219', // 1884-01-02 04:51:28+00:00 VERSAILLES - Rehm Pierre
            '1220', // 1888-06-03 15:01:28+00:00 TAILLEVILLE - Gauquelin-A2-1220
            '1221', // 1885-08-03 10:50:16+00:00 VELZIC - Revelliac Edouard
            '1222', // 1901-10-23 10:22:40+00:00 PAVILLY - Reverse Bernard
            '1223', // 1882-06-29 07:37:08+00:00 BOURGOIN - Rhenter Jean
            '1224', // 1893-02-02 03:50:40+00:00 NIEPPE - Richard Armand
            '1225', // 1876-09-09 01:44:28+00:00 CESSENON - Riche Vincent
            '1226', // 1901-05-13 05:50:40+00:00 VILLIERS-S/MARN - Richier Jacques
            '1228', // 1872-09-07 05:25:00+00:00 SARTENE - Roccaserra Vincent
            '1229', // 1876-11-01 10:29:00-01:00 HUTTENHEIM - Rohmer Paul
            '1232', // 1888-07-15 15:37:08+00:00 VIENNE - Rosset Marc
            '1233', // 1888-04-27 16:49:20+00:00 MORET-S/LOING - Rouffiac Pierre
            '1234', // 1890-08-25 02:52:56+00:00 RILHAC-EN-XAINT - Rouffiat Paul
            '1235', // 1890-05-24 20:40:40+00:00 OULLINS - Rougy Mathieu
            '1236', // 1875-05-02 21:35:40+00:00 LA ROCHE-S/YON - Rousseau-Decelle
            '1237', // 1867-11-03 07:39:28+00:00 VILLARS-ST-MARC - Roussel Georges
            '1238', // 1866-01-14 00:49:44+00:00 ST-AFFRIQUE - Roussy Alfred
            '1239', // 1884-03-16 12:55:00+00:00 CUSSAC - Ruaud Ludovic
            '1240', // 1880-04-04 07:50:48+00:00 VARENNES-EN-AMI - Ruin Georges
            '1241', // 1886-06-16 20:59:00-01:00 OTTWILLER - Sackenreiter G
            '1242', // 1887-01-30 16:35:52+00:00 BESANCON - Saintin Henri
            '1243', // 1876-10-28 05:40:24+00:00 CHABEUIL - Sambuc Edouard
            '1246', // 1881-04-26 21:03:00+00:00 MAYENNE - Sauve Louis
            '1247', // 1869-07-06 09:54:40+00:00 MARCHENOIR - Gauquelin-A2-1247
            '1251', // 1873-01-24 22:35:16-01:00 SARREGUEMINES - Schmitt Camille
            '1252', // 1872-09-18 05:29:00-01:00 STRASBOURG - Schmitt Charles
            '1253', // 1889-02-21 18:35:52+00:00 MONTBELIARD - Schwab Roger
            '1254', // 1886-05-04 10:45:36-01:00 MULHOUSE - Schwartz-Wacker Alfred
            '1257', // 1882-03-22 19:39:52+00:00 CHATILLON S-SEIN - Serbource Marcel
            '1258', // 1902-03-15 10:50:40+00:00 ALGER - Sesini Marcel
            '1261', // 1874-08-23 23:52:56+00:00 CHAMBOULIVE - Gauquelin-A2-1261
            '1262', // 1878-07-09 07:51:28+00:00 LABRUGUIERE - Gauquelin-A2-1262
            '1263', // 1895-05-13 04:50:40+00:00 ST-HILAIRE-DU-H - Simon Francis
            '1264', // 1881-08-17 04:52:48+00:00 DOLE - Simonin Louis
            '1265', // 1876-07-06 10:55:16+00:00 PONT-DE-L ARCHE - Sorel Francois
            '1266', // 1871-05-25 07:51:40+00:00 ST-MALO - Sorre Auguste
            '1267', // 1882-02-23 03:46:40+00:00 MOULINS-S-ALLIER - Sorrel Etienne
            '1268', // 1883-09-21 20:44:28+00:00 NEFFIES - Soulayrol Georges
            '1269', // 1890-04-29 21:15:36-01:00 MULHOUSE - Specklin Paul
            '1270', // 1894-07-18 01:50:40+00:00 NICE - Sureau Maurice
            '1271', // 1896-07-05 01:50:40+00:00 MAICHE - Gauquelin-A2-1271
            '1274', // 1874-01-14 08:42:36+00:00 ST-HIPPOLYTE-DU - Teissonniere Maurice
            '1276', // 1897-03-03 04:50:40+00:00 VILLENAUX-LA-GR - Thiolat Pierre
            '1277', // 1905-03-20 18:50:40+00:00 LANHELIN - Tiollais Rene
            '1278', // 1872-08-31 02:02:40+00:00 CLERMONT FERRAND - Tixier Felix
            '1279', // 1878-12-06 08:54:40+00:00 DOLLUET - Tournay Auguste
            '1280', // 1890-01-17 02:57:36+00:00 BEGLES - Tourvieille Labroue Maurice
            '1281', // 1892-01-24 11:50:40+00:00 EPERNAY - Tramuset Rene
            '1282', // 1906-01-22 03:50:40+00:00 ROUEN - Trumel Rene
            '1283', // 1895-11-05 07:50:40+00:00 PONTOISE - Turpin Raymond
            '1284', // 1880-10-09 11:34:08+00:00 TRANS-EN-PROVEN - Vadon Alfred
            '1285', // 1896-11-18 10:50:40+00:00 NARBONNE - Vaisse Jean
            '1286', // 1891-12-18 15:50:40+00:00 BAR-LE-DUC - Valade Roger
            '1287', // 1872-10-17 09:47:40+00:00 PECQUENCOURT - Vallee Cyrille
            '1288', // 1884-04-28 15:01:40+00:00 VICHY - Vallerix Andre
            '1289', // 1903-06-28 11:50:40+00:00 ARMENTIERES - Vandevelde Gaston
            '1290', // 1903-05-22 08:10:40+00:00 LE HAVRE - Vanier Jean
            '1294', // 1884-03-28 19:53:16+00:00 BLANC - Veluet Maurice
            '1295', // 1877-04-11 23:06:20+00:00 NANTES - Gauquelin-A2-1295
            '1296', // 1891-07-31 02:20:40+00:00 BEAUCOURT - Vermelin Henri
            '1297', // 1873-01-05 19:52:56+00:00 BRIVE - Vialle Fernand
            '1298', // 1903-12-16 20:50:40+00:00 TOURS - Vialle Pierre
            '1299', // 1887-01-11 04:34:08+00:00 FREJUS - Vian Louis
            '1300', // 1884-07-09 09:35:24+00:00 SAULX - Gauquelin-A2-1300
            '1301', // 1876-08-06 12:52:56+00:00 NAVES - Vidalin Gustave
            '1302', // 1886-01-19 18:30:56+00:00 GOLFE-JUAN - Vidal-Revel Jean
            '1303', // 1899-03-30 20:20:40+00:00 MONTBAZON - Viette Roger
            '1304', // 1891-09-27 08:50:40+00:00 ALES - Vigne Paul
            '1305', // 1884-07-15 09:45:40+00:00 GUILLON - Vignes Henri
            '1306', // 1887-12-28 03:01:28+00:00 CAEN - Vigot Marcel
            '1307', // 1896-07-31 20:20:40+00:00 AVIGNON - Vincenti Charles
            '1309', // 1891-08-16 04:50:40+00:00 ORLEANS - Viollet Pierre
            '1312', // 1905-12-19 17:50:40+00:00 LAMBALLE - Vulpian Paul
            '1313', // 1903-12-15 09:50:40+00:00 CHALONS-S/MARNE - Waltrigny Hubert
            '1315', // 1888-04-17 12:05:16-01:00 PETITE-ROSSELLE - Wilhelm Theodore
            '1317', // 1905-09-01 12:15:00-01:00 ALGRANGE - Wiltzer Hubert
            '1319', // 1903-11-29 08:00:00-01:00 SARRALBE - Wolgensinger Lucien
            '1320', // 1903-07-27 12:00:00-01:00 STRASBOURG - Woringer Frederic
        ],
        '570SPO' => [
            '6', // 1923-04-17 01:00:00+00:00 VENNISSIEUX - Bally Etienne
            '9', // 1913-12-11 11:00:00+00:00 COSNE - Bazennerye Rene
            '10', // 1900-07-24 01:50:40+00:00 BOULOGNE-BILLANC - Gauquelin-A1-10
            '14', // 1916-03-14 05:00:00+00:00 CREST - Boeckel Paul
            '17', // 1888-12-21 00:38:32+00:00 MARSEILLE - Bouin Jean
            '18', // 1916-02-15 07:00:00-01:00 REDING - Bour Armand
            '19', // 1918-07-29 00:00:00+00:00 AGEN - Brisson Jean
            '28', // 1924-05-20 05:00:00+00:00 TOULOUSE - Damitio Georges
            '36', // 1928-10-27 09:45:00+00:00 ALGER - El Mabrouk Mohamed
            '38', // 1917-04-27 14:30:00+00:00 CHATTELLERAULT - Gaillot Georges
            '41', // 1899-10-01 01:50:40+00:00 LEDORAT - Guillemont Joseph
            '44', // 1921-09-07 00:00:00+00:00 ST-EMILION - Herice Daniel
            '47', // 1917-02-21 15:00:00+00:00 ALDUDES - Joanblanc Robert
            '48', // 1913-12-15 21:45:00+00:00 ROUBAIX - Joye Prudent
            '49', // 1906-12-10 04:50:40+00:00 BORDEAUX - Ladoumegue Jules
            '51', // 1914-08-28 10:00:00+00:00 GERDE - Lalanne Jean
            '52', // 1920-02-18 09:00:00+00:00 THUMERIES - Legrain Pierre
            '54', // 1901-02-21 22:50:40+00:00 LIBOURNE - Lewden Pierre
            '57', // 1920-03-31 01:00:00+00:00 LABRIGUE - Manaire Emile
            '60', // 1925-10-14 06:30:00+00:00 CAPDAIL - Marie Andre
            '64', // 1903-01-27 10:50:40+00:00 NORRENTFONTES - Noel Jules
            '65', // 1910-04-22 09:50:40+00:00 FACTUREBIGAN - Paul Robert
            '68', // 1902-05-22 22:50:40+00:00 LUNEL - Ramadier Pierre
            '69', // 1913-04-20 12:00:00+00:00 EVREUX - Rochard Roger
            '72', // 1912-12-23 12:00:00+00:00 BORDEAUX - Skawinski Pierre
            '74', // 1927-06-22 16:30:00+00:00 VIERZON - Thureau Jean
            '75', // 1919-08-02 12:00:00+00:00 OYONNAX - Tissot Raymond
            '76', // 1920-12-24 13:00:00+00:00 TARBES - Valmy Rene
            '78', // 1923-07-21 06:30:00+00:00 GRANDCHAMONT - Gauquelin-A1-78
            '79', // 1923-07-21 06:00:00+00:00 GRANDCHAMONT - Gauquelin-A1-79
            '80', // 1928-02-24 20:00:00+00:00 LESPESSES - Veste Paulette
            '81', // 1906-02-06 14:00:00-01:00 RIBEAUVILLE - Winter Paul
            '87', // 1921-02-16 01:00:00+00:00 NICE - Behra Jean
            '88', // 1899-08-03 17:50:40+00:00 MONTECARLO - Gauquelin-A1-88
            '90', // 1913-07-05 04:00:00+00:00 HERBEVILLE - Houel Georges
            '91', // 1908-04-02 20:50:40+00:00 PUTEAUX - Lesurque Marcel
            '92', // 1908-10-04 13:10:40+00:00 LILLE - Monneret Georges
            '125', // 1895-09-07 21:50:40+00:00 MONTROUGE - Arnoux Maurice
            '127', // 1905-09-26 22:35:40+00:00 VERSAILLES - Assollant Jean
            '134', // 1896-10-25 06:50:40+00:00 MERU - Bellonte Maurice
            '148', // 1898-07-20 05:50:40+00:00 REIMS - Challe Leon
            '150', // 1896-05-01 17:50:40+00:00 IVIERS - Codos Paul
            '174', // 1902-05-29 11:50:40+00:00 BONY - Guillaumet Henri
            '199', // 1901-12-09 01:50:40+00:00 AUBENTEN - Mermoz Jean
            '208', // 1898-05-20 21:20:40+00:00 LUNEVILLE - Nessler Eric
            '213', // 1889-06-13 05:37:08+00:00 MONTFERRAT - Pegoud Adolphe
            '515', // 1901-01-08 16:20:40+00:00 BOULOGNE-S/MER - Constant Eugene
            '517', // 1922-07-22 21:45:00+00:00 NEUILLY S-SEINE - Lebranchu Roger
            '518', // 1926-09-07 08:00:00+00:00 ASNIERES - Maillet Jacques
            '520', // 1930-05-10 01:30:00+00:00 CORBEIL-ESSONNE - Gauquelin-A1-520
            '521', // 1902-07-12 20:20:40+00:00 TILLIERS - Vandernotte Fernand
            '522', // 1909-07-29 18:50:40+00:00 NANTES - Vandernotte Marcel
            '525', // 1920-02-22 10:30:00+00:00 LEVALLOIS-PERRET - Barrais Andre
            '527', // 1911-07-04 18:00:00+00:00 CROIX - Boel Pierre
            '528', // 1922-12-04 12:30:00+00:00 BREST - Boutin-Desvignes Abel
            '532', // 1920-11-28 08:00:00+00:00 SANTRANGES - Chocat Rene
            '535', // 1925-05-27 16:00:00+00:00 SOCHAUX - Derency Rene
            '536', // 1925-09-16 07:00:00+00:00 BELLEGARDE - Dessemme Jacques
            '537', // 1921-09-04 01:00:00+00:00 GRENOBLE - Duperay Jean
            '538', // 1912-08-31 14:00:00+00:00 RUE - Etienne Roland
            '539', // 1920-06-18 03:00:00+00:00 MONTPELLIER - Gauquelin-A1-539
            '540', // 1907-09-08 01:20:40+00:00 ST-MAUR-DES-FOS - Gauquelin-A1-540
            '541', // 1929-10-31 00:30:00+00:00 TROUVILLE - Freimuler Jacques
            '542', // 1916-11-11 05:00:00+00:00 ARGENT S/SAULDR - Frezot Emile
            '544', // 1926-01-06 04:00:00+00:00 MONTOIRE - Gauquelin-A1-544
            '545', // 1911-04-26 16:00:00+00:00 BIARRITZ - Hell Henri
            '546', // 1927-02-21 04:00:00+00:00 ST-GEORGES D'OR - Merle Genevieve
            '548', // 1927-11-17 13:00:00+00:00 SARCELLES - Offner Raymond
            '549', // 1924-10-12 07:00:00+00:00 BAGNOLET - Perrier Jacques
            '550', // 1920-03-01 20:00:00+00:00 MONACO - Quenin Yvan
            '552', // 1905-05-21 03:00:00-01:00 VIEUX-THANN - Rudler Antoine
            '553', // 1928-01-01 11:30:00+00:00 ORAN - Salignon Jean
            '554', // 1925-03-14 04:00:00+00:00 ST-PIERRE-DES-C - Szwidzinski Jean
            '555', // 1927-10-12 19:00:00+00:00 ROANNE - Vacheresse Andre
            '556', // 1901-08-17 10:50:40+00:00 STE-FOIX-DE-PEY - Chasserau Louis
            '557', // 1901-02-08 01:50:40+00:00 PARDS - Conti Roger
            '559', // 1869-01-21 02:43:40+00:00 CLAIRVAUX - Fouquet Leon
            '561', // 1893-10-20 15:50:40+00:00 ST-MARTIN-DE-LE - Gauquelin-A1-561
            '562', // 1890-08-10 16:50:00+00:00 PANTIN - Lagache Alfred
            '563', // 1925-12-01 04:00:00+00:00 PERPIGNAN - Marty Jean
            '565', // 1910-03-07 19:45:00-01:00 COLMAR - Angelmann Valentin
            '567', // 1926-09-20 15:00:00+00:00 SANCERGUES - Archambault Jean
            '568', // 1929-10-05 10:00:00+00:00 ST-GEORGES-DU V - Assire Marcel
            '569', // 1927-10-14 07:30:00+00:00 ROANNE - Auclair Marcel
            '570', // 1929-04-11 22:00:00+00:00 MACON - Authier Henri
            '571', // 1926-04-07 03:00:00+00:00 PANTIN - Baour Roger
            '572', // 1929-09-20 18:00:00+00:00 AMIENS - Bataille Jacques
            '573', // 1929-01-20 08:00:00+00:00 EQUEMANVILLE - Beaumais Marcel
            '574', // 1926-11-26 16:00:00+00:00 AIX-EN-PROVENCE - Gauquelin-A1-574
            '575', // 1927-07-23 09:00:00+00:00 BEZONS - Gauquelin-A1-575
            '576', // 1928-01-24 16:00:00+00:00 BAGNOLET - Bonnardel Francis
            '577', // 1929-03-05 19:00:00+00:00 HENIN-LIETARD - Gauquelin-A1-577
            '578', // 1930-05-02 00:30:00+00:00 VANNES - Briend Andre
            '579', // 1926-11-13 06:00:00+00:00 LIMOGES - Bro Jacques
            '580', // 1930-11-22 11:00:00+00:00 CHERBOURG - Busata Roger
            '581', // 1894-01-12 17:50:40+00:00 LIEVIN - Carpentier Georges
            '582', // 1926-10-15 03:00:00+00:00 MONTPELLIER - Caulet Auguste
            '583', // 1925-02-22 20:00:00+00:00 GAP - Celestin Bernard
            '584', // 1916-07-22 21:00:00+00:00 SIDI-BEL-ABBES - Cerdan Marcel
            '585', // 1928-01-17 05:30:00+00:00 ST-GERMAIN-VILL - Gauquelin-A1-585
            '586', // 1918-06-23 11:00:00+00:00 BUXEROLLES - Charron Robert
            '587', // 1926-03-04 16:10:00+00:00 BONE - Chemama Emile
            '588', // 1932-05-27 19:00:00+00:00 AUDUN-LE-TICHE - Ciccarelli Mario
            '589', // 1931-03-13 00:00:00+00:00 AUBERVILLIERS - Gauquelin-A1-589
            '590', // 1931-02-12 17:15:00+00:00 PLOUGUENAST - Coeuret Francis
            '591', // 1930-11-15 21:00:00+00:00 BONE - Cohen Robert
            '592', // 1929-01-07 23:00:00+00:00 ST-JOACHIM - Gauquelin-A1-592
            '593', // 1931-01-18 09:30:00+00:00 LE HAVRE - Gauquelin-A1-593
            '595', // 1929-07-22 16:00:00+00:00 LAMBESC - Cuillieres Rene
            '596', // 1923-12-08 05:00:00+00:00 VISCOVATO - Cuneo Marc
            '597', // 1924-02-20 12:00:00+00:00 CHAUNY - Dauthuille Laurent
            '598', // 1927-02-08 15:00:00+00:00 CHARLEVILLE - Dehaye Jacques
            '600', // 1913-09-11 22:00:00+00:00 VELIZY - Dogniaux Paul
            '602', // 1930-09-16 11:00:00+00:00 ANTONY - Dupre Marcel
            '603', // 1929-01-23 10:00:00+00:00 BAGNOLET - Entringer Henri
            '604', // 1927-05-28 22:00:00+00:00 BEZIERS - Escudie Aime
            '605', // 1922-02-24 02:00:00+00:00 MAUBEUGE - Gauquelin-A1-605
            '606', // 1920-01-11 01:00:00+00:00 MAUBEUGE - Famechon Emile
            '607', // 1924-11-08 18:30:00+00:00 MAUBEUGE - Famechon Raymond
            '608', // 1927-03-02 13:00:00+00:00 ALGER - Gauquelin-A1-608
            '609', // 1929-01-13 19:30:00+00:00 TIARET - Garcia Robert
            '610', // 1931-03-05 02:30:00+00:00 ROSENDAEL - Gheerardyn Jean
            '611', // 1929-04-27 04:00:00+00:00 ORAN - Godih Lahouari
            '612', // 1929-04-10 08:00:00+00:00 TARBES - Gracia Guy
            '613', // 1930-11-29 13:00:00+00:00 CHALAGNAC - Gauquelin-A1-613
            '614', // 1930-05-04 04:00:00+00:00 MARSEILLE - Grassi Raymond
            '615', // 1928-12-26 01:00:00+00:00 ROUBAIX - Gress Pierre
            '616', // 1930-01-05 22:30:00+00:00 LISIEUX - Guernalec Jean
            '617', // 1928-03-07 18:20:00+00:00 EVREUX - Guivarch Robert
            '618', // 1907-09-05 10:50:40+00:00 ROUBAIX - Gauquelin-A1-618
            '619', // 1926-11-07 20:30:00+00:00 FUMEL - Haira-Bedian Agop
            '620', // 1928-06-18 09:30:00+00:00 ROMAIN - Herbillon Jacques
            '621', // 1923-08-05 07:00:00+00:00 FRESNES-S/ESCAU - Hermal Franck
            '622', // 1906-01-21 17:20:40+00:00 TROYES - Holzer Maurice
            '623', // 1907-02-08 20:50:40+00:00 REIMS - Huat Eugene
            '624', // 1908-12-17 16:50:40+00:00 VALENCIENNES - Humery Gustave
            '625', // 1927-05-18 01:00:00+00:00 MERICOURT - Gauquelin-A1-625
            '626', // 1927-08-27 19:30:00+00:00 SEDAN - Jacques Robert
            '627', // 1928-01-07 21:00:00+00:00 ORAN - Khalfi Hocine
            '628', // 1927-05-08 00:00:00+00:00 AVELUY - Labalette Jean
            '629', // 1926-03-16 14:30:00+00:00 PAU - Lalounis Jean
            '630', // 1930-06-23 15:00:00+00:00 FOURMIES - Lamotte Roland
            '632', // 1925-03-31 01:00:00+00:00 PONT-AUDEMER - Langlois Pierre
            '633', // 1931-06-13 00:00:00+00:00 VILLE-D'AY - Gauquelin-A1-633
            '634', // 1927-08-26 11:00:00+00:00 EPERNAY - Gauquelin-A1-634
            '635', // 1924-02-17 05:00:00+00:00 MONTLUCON - Gauquelin-A1-635
            '636', // 1921-02-03 07:00:00+00:00 LAON - Lavoine Gilbert
            '637', // 1928-05-27 06:30:00+00:00 ST-VALLIER - Lawniczak Cesar
            '638', // 1929-01-08 21:00:00+00:00 GUINGAMP - Leborgne Yves
            '639', // 1892-10-27 02:50:40+00:00 POUGUES-LES-EAUX - Ledoux Charles
            '640', // 1927-03-28 08:00:00+00:00 HIRSON - Lefin Andre
            '641', // 1929-12-29 20:00:00+00:00 LISIEUX - Gauquelin-A1-641
            '642', // 1911-01-26 10:50:40+00:00 ST-ETIENNE - Louis Pierre
            '643', // 1927-10-23 17:45:00+00:00 LAIGLE - Louni Jacques
            '644', // 1928-11-07 16:00:00+00:00 ALGER - Maddi Yayia
            '645', // 1926-08-03 16:00:00+00:00 BESANCON - Gauquelin-A1-645
            '646', // 1902-04-08 07:50:40+00:00 VALENCIENNES - Mascart Edouard
            '647', // 1930-06-15 00:40:00+00:00 BOULOGNE-S/MER - Masson Michel
            '648', // 1926-04-03 09:00:00+00:00 PETITE RAON - Mathieu Marcel
            '649', // 1928-03-15 01:00:00+00:00 MONT - Mauguin Andre
            '650', // 1926-04-18 04:00:00+00:00 PERIGUEUX - Maury Maurice
            '651', // 1918-06-24 03:00:00+00:00 ST-PARIZET-LE-C - Medina Theo
            '652', // 1928-05-08 06:00:00+00:00 REIMS - Meraint Lucien
            '653', // 1927-11-19 01:00:00+00:00 SALLAUMINES - Meulenbroucq Gaston
            '654', // 1929-06-08 09:30:00+00:00 EPERNAY - Meunier Robert
            '655', // 1929-12-22 21:00:00+00:00 NANCY - Michel Bernard
            '656', // 1929-05-27 17:00:00+00:00 HARAMONT - Milcent Robert
            '657', // 1919-12-01 10:00:00+00:00 TOULOUSE - Montane Pierre
            '658', // 1930-05-10 01:00:00+00:00 LE HAVRE - Gauquelin-A1-658
            '659', // 1931-03-04 19:00:00+00:00 ALGER - Omari Mohamed
            '660', // 1924-06-20 15:00:00+00:00 DURY - Pasek Stanislas
            '661', // 1930-12-05 03:00:00+00:00 LA CLUSAZ - Perillat Simon
            '662', // 1928-08-25 09:00:00+00:00 SAINT-BRIEUC - Perrigault Louis
            '663', // 1929-04-04 04:30:00+00:00 PAU - Petre Gabriel
            '664', // 1926-10-16 15:40:00+00:00 PONTARLIER - Pierluigi Celestin
            '666', // 1906-09-02 05:50:40+00:00 CLERMONT-FERRAND - Pladner Emile
            '667', // 1931-03-28 03:00:00+00:00 MARSEILLE - Pratesi Hilaire
            '669', // 1929-05-31 00:25:00+00:00 GENNEVILLIERS - Prigent Guy
            '670', // 1927-09-26 05:25:00+00:00 GENNEVILLIERS - Gauquelin-A1-670
            '671', // 1930-11-07 09:35:00+00:00 BARLIN - Gauquelin-A1-671
            '673', // 1929-12-24 01:30:00+00:00 ROSENDAEL - Ranvial Marcel
            '674', // 1927-12-09 10:00:00+00:00 ORAN - Richaud Pierre
            '675', // 1928-06-11 07:00:00+00:00 VALENCE - Roustan Rene
            '676', // 1900-07-16 11:50:40+00:00 BORDEAUX - Routis Andre
            '677', // 1929-12-12 05:00:00+00:00 EPERNAY - Santabien Jean
            '678', // 1924-09-10 19:00:00+00:00 NICE - Skena Louis
            '679', // 1927-08-11 02:15:00+00:00 MULHOUSE - Gauquelin-A1-679
            '680', // 1929-08-08 17:00:00+00:00 ST-DENIS - Sobolack Stanislas
            '681', // 1925-07-27 09:00:00+00:00 ST-MARTIN-DU-TE - Stock Gilbert
            '682', // 1923-02-12 11:00:00+00:00 ST-MARTIN-DU-TE - Stock Jean
            '683', // 1929-06-15 15:00:00+00:00 NICE - Gauquelin-A1-683
            '684', // 1929-12-08 22:00:00+00:00 CHAMBON-FEUGERO - Szyjka Joseph
            '685', // 1930-07-13 04:00:00+00:00 TIZI-OUZOU - Gauquelin-A1-685
            '686', // 1932-06-02 12:30:00+00:00 ORAN - Tedijini Mohamed
            '687', // 1927-02-13 12:30:00+00:00 ELBEUF - Thieulin William
            '688', // 1904-05-29 21:50:40+00:00 SAINT-DIZIER - Thil Marcel
            '689', // 1929-03-23 05:45:00+00:00 SCHILLINGHEIM - Thomann Eugene
            '690', // 1927-01-13 23:30:00+00:00 DIJON - Vangi Salvator
            '691', // 1923-06-29 01:00:00+00:00 ROSENDAEL - Vercoutter Andre
            '692', // 1924-10-21 15:00:00+00:00 NOEUX-LES-MINES - Walzack Jean
            '693', // 1931-04-03 00:00:00+00:00 MONCHECOURT - Warusfel Ildephonse
            '694', // 1930-01-02 19:25:00+00:00 STRASBOURG - Gauquelin-A1-694
            '695', // 1927-02-27 20:00:00+00:00 ALGER - Yvel Albert
            '696', // 1930-12-12 17:00:00+00:00 TOURNUS - Gauquelin-A1-696
            '769', // 1927-07-24 09:00:00+00:00 VILLENEUVE-LE-R - Boutigny Robert
            '775', // 1925-12-18 23:30:00+00:00 RUEIL - Baldassari Jean
            '776', // 1920-09-07 19:15:00+00:00 ST-ETIENNE-LA-V - Baratin Pierre
            '777', // 1927-12-25 07:30:00+00:00 AMIENS - Bellanger Jacques
            '779', // 1887-03-04 04:51:16+00:00 NEUILLY-S-SEINE - Berthet Marcel
            '780', // 1924-10-14 13:00:00+00:00 MERIGNAC - Berton Rene
            '781', // 1925-10-01 22:00:00+00:00 LENS - Beyaert Jose
            '783', // 1918-03-22 17:00:00+00:00 PAULMY - Blanchet Andre
            '784', // 1903-12-23 11:50:40+00:00 GRIPEY - Blanchonnet Armand
            '785', // 1925-03-12 23:00:00+00:00 ST-MEEN-LE-GRAN - Bobet Louison
            '786', // 1920-08-08 15:45:00+00:00 ARC-LES-GRAY - Bonnaventure Robert
            '787', // 1904-02-10 16:50:40+00:00 MEUNG-S/LOIRE - Boucheron Onesime
            '788', // 1877-01-14 03:57:28+00:00 MARMANDE - Bourillon Paul
            '789', // 1883-01-19 03:42:32+00:00 REIMS - Brocco Maurice
            '790', // 1921-01-23 22:00:00+00:00 SAINT-MAUR - Caput Louis
            '791', // 1925-01-11 02:00:00+00:00 ARGENTEUIL - Carrara Emile
            '793', // 1914-03-02 10:00:00+00:00 CHAUMONT-S/MARN - Chaillot Louis
            '794', // 1916-04-04 10:00:00+00:00 MAULE - Charpentier Robert
            '795', // 1910-07-14 22:20:40+00:00 MEUDON - Gauquelin-A1-795
            '797', // 1915-09-27 05:00:00+00:00 LABOUHEYRE - Claverie Gabriel
            '798', // 1909-03-14 04:50:40+00:00 PLEYBEN - Gauquelin-A1-798
            '799', // 1915-10-11 14:00:00+00:00 LORGES - Cosson Victor
            '800', // 1924-02-08 05:00:00+00:00 OLLIOULES - Coste Charles
            '801', // 1919-06-04 10:00:00+00:00 CHATEAULIN - Danguillaume Camille
            '804', // 1921-10-06 09:15:00+00:00 POLIGNY - De Muer Maurice
            '805', // 1921-01-06 23:00:00+00:00 LILLIERS - Deprez Louis
            '807', // 1912-07-03 15:00:00+00:00 OSMERY - Diot Emile
            '808', // 1915-10-16 09:00:00+00:00 NESLES-LA-VALLEE - Dorgebray Robert
            '811', // 1928-06-19 09:00:00+00:00 LEZAT - Dupont Jacques
            '812', // 1926-05-14 02:30:00+00:00 LA CHATRE - Dussault Marcel
            '813', // 1899-08-26 05:50:40+00:00 KREMLIN-BICETRE - Faucheux Lucien
            '814', // 1922-04-03 00:00:00+00:00 ST-OUEN - Ferrand Jean
            '815', // 1892-06-18 22:20:40+00:00 PAU - Fontan Victor
            '816', // 1912-02-03 21:00:00+00:00 ARMENTIERES - Fournier Amedee
            '817', // 1913-07-23 13:00:00+00:00 NANTEUIL-LA-FOS - Gauquelin-A1-817
            '818', // 1892-03-28 00:50:40+00:00 MARSEILLE - Ganay Gustave
            '819', // 1924-09-22 17:00:00+00:00 BEAUMONT-MONTEU - Gauquelin-A1-819
            '820', // 1916-04-12 13:00:00+00:00 BLANZY - Gauthier Louis
            '821', // 1925-06-12 01:30:00+00:00 CLERMONT-FERRAND - Geminiani Raphael
            '822', // 1881-11-21 05:57:12+00:00 BOSSAY-S/CLAISE - Georget Emile
            '823', // 1912-08-12 22:00:00+00:00 BOULOGNE-BILLANC - Gerardin Louis
            '824', // 1913-03-28 19:00:00+00:00 KERDONIO - Goasmat Jean
            '825', // 1922-03-31 05:00:00+00:00 CLAMART - Goussot Raymond
            '828', // 1921-12-07 02:30:00+00:00 LAON - Guegan Raymond
            '830', // 1909-12-04 07:50:40+00:00 SABLES D'OLONNE - Guimbretiere Marcel
            '831', // 1885-09-16 17:42:32+00:00 REIMS - Hourlier Leon
            '832', // 1920-07-19 01:00:00+00:00 NOUVION-LE-COMT - Idee Emile
            '833', // 1924-07-08 08:45:00+00:00 RUEIL - Gauquelin-A1-833
            '834', // 1875-03-31 09:39:52+00:00 SANTENAY - Jacquelin Edmond
            '837', // 1920-09-10 22:00:00+00:00 HERICOURT - Lamboley Jean
            '838', // 1916-11-28 09:00:00+00:00 ST-GEOURS-EN-MA - Lapebie Guy
            '839', // 1911-01-16 20:50:40+00:00 BAYONNE - Lapebie Roger
            '840', // 1913-06-06 15:45:00-01:00 LAPOUTROIE - Laurent Marcel
            '841', // 1925-10-16 03:30:00+00:00 MARLES-LES-MINE - Lazarides Apo
            '842', // 1909-03-14 09:50:40+00:00 MOELAN-S/MER - Gauquelin-A1-842
            '843', // 1903-10-10 06:50:40+00:00 PONTIVY - Le Drogo Ferdinand
            '845', // 1909-06-17 20:50:40+00:00 MASSY-PALAISEAU - Lemoine Henri
            '846', // 1918-02-18 04:00:00+00:00 INGUINEL - Le Strat Ange
            '847', // 1912-04-29 11:00:00+00:00 LE HAVRE - Lesueur Raoul
            '849', // 1907-07-25 12:50:40+00:00 AMIENS - Letourneur Alfred
            '850', // 1910-07-12 05:50:40+00:00 NESLES-LA-VALLEE - Level Leon
            '851', // 1904-02-15 21:50:40+00:00 LE BEX - Magne Antonin
            '852', // 1913-05-03 16:30:00+00:00 THIERGEVILLE - Mallet Auguste
            '853', // 1911-02-08 00:50:40+00:00 TOULOUSE - Marcaillou Sylvain
            '854', // 1910-02-27 23:20:40+00:00 ORLEANS - Marechal Jean
            '855', // 1913-08-20 09:00:00+00:00 BAYONNE - Maye Paul
            '856', // 1906-09-29 20:50:40+00:00 ST-BEAUZELY - Merviel Jules
            '857', // 1903-11-17 14:50:40+00:00 EPINAY-S-SEINE - Michard Lucien
            '858', // 1908-06-19 14:50:40+00:00 NICE - Minardi Louis
            '859', // 1909-05-22 22:20:40+00:00 ST-REMY-LES-CHE - Gauquelin-A1-859
            '860', // 1903-11-27 09:50:40+00:00 CLICHY - Moineau Julien
            '861', // 1909-11-18 00:50:40+00:00 SIVRY-COURTRY - Noret Jean
            '862', // 1913-04-18 12:00:00+00:00 GOUSSAINVILLE - Oubron Robert
            '863', // 1904-02-12 22:50:40+00:00 STE-GEMMES-D'AN - Paillard Georges
            '865', // 1882-10-18 01:06:20+00:00 PLESSE - Petit-Breton Lucien
            '866', // 1921-06-28 09:00:00+00:00 PARIS 20E - Piel Roger
            '868', // 1879-06-05 23:19:20+00:00 MONT-S/LOING - Pottier Rene
            '870', // 1904-11-10 00:50:40+00:00 CIEUX - Gauquelin-A1-870
            '871', // 1919-10-25 19:00:00+00:00 MARSEILLE - Gauquelin-A1-871
            '872', // 1925-05-29 17:00:00+00:00 TOULOUSE - Rey Jean
            '874', // 1924-10-20 08:30:00+00:00 VITRY-S-SEINE - Rioland Roger
            '876', // 1862-06-03 06:51:16+00:00 ASNIERES - Rivierre Gaston
            '877', // 1921-06-10 01:00:00+00:00 CONDE-S/VOUZIER - Robic Jean
            '878', // 1914-01-22 04:00:00+00:00 NICE - Rolland Amedee
            '879', // 1920-04-15 13:00:00+00:00 MAREUIL-LE-PORT - Rondeaux Roger
            '881', // 1922-12-19 22:30:00+00:00 CLAMART - Senfftleben Georges
            '882', // 1913-12-28 19:00:00+00:00 LEVALLOIS-PERRET - Seres Arthur
            '883', // 1884-04-07 21:02:16+00:00 CONDOM - Seres Georges
            '885', // 1912-06-06 13:00:00+00:00 VAY - Tassin Eloi
            '886', // 1919-12-11 16:00:00+00:00 ST-LAURENT-DU-V - Teisseire Lucien
            '887', // 1908-05-31 19:50:40+00:00 AUXY - Terreau Ernest
            '888', // 1910-05-30 09:50:40+00:00 ANZIN - Thietard Louis
            '889', // 1907-05-20 03:50:40+00:00 AVION - Vaast Charles
            '890', // 1929-05-01 22:00:00+00:00 CURZON - Varnajo Robert
            '893', // 1914-02-17 06:00:00+00:00 ROCHEVILLE - Gauquelin-A1-893
            '895', // 1902-07-21 21:50:40+00:00 LUNEVILLE - Wambst Georges
            '1228', // 1911-01-07 15:50:40+00:00 MONTPELLIER - Bougnol Rene
            '1229', // 1893-12-21 05:50:40+00:00 HONFLEUR - Buchard Georges
            '1230', // 1912-04-05 04:00:00+00:00 BORDEAUX - Buhan Jean
            '1231', // 1892-07-28 10:20:40+00:00 ST-MALO - Cattiau Philippe
            '1234', // 1913-05-08 15:00:00+00:00 GERARDMER - Gardere Andre
            '1235', // 1909-02-25 08:20:40+00:00 GERARDMER - Gardere Edward
            '1237', // 1891-07-18 07:50:40+00:00 BORDEAUX - Labattut Andre
            '1238', // 1922-06-07 10:00:00+00:00 LA GRAND' COMBE - Lataste Jacques
            '1240', // 1928-10-03 20:00:00+00:00 PERPIGNAN - Oriola Christian
            '1241', // 1911-05-24 16:30:00+00:00 SAINT-BRIEUC - Pecheux Michel
            '1242', // 1904-03-21 21:50:40+00:00 ORLEANS - Schmetz Bernard
            '1244', // 1920-11-26 02:00:00+00:00 MOSTAGANEM - Abderrhamane Bonnedienne
            '1245', // 1897-11-26 21:50:40+00:00 LISIEUX - Accard Robert
            '1246', // 1920-12-03 10:00:00+00:00 BONNEVILLE - Alpsteg Rene
            '1248', // 1922-04-23 09:00:00+00:00 BORDEAUX - Arnaudeau Henri
            '1249', // 1912-05-16 01:10:00+00:00 CHANTILLY - Aston Alfred
            '1250', // 1915-11-23 04:00:00+00:00 SIDI-BEL-ABBES - Aznar Emmanuel
            '1251', // 1923-06-07 22:00:00+00:00 LAMBERSART - Gauquelin-A1-1251
            '1252', // 1904-04-07 13:50:40+00:00 CLAUZEL - Bardot Charles
            '1253', // 1915-06-21 10:00:00+00:00 ORAN - Bastien Jean
            '1254', // 1919-07-02 22:00:00+00:00 REIMS - Batteux Albert
            '1256', // 1912-04-15 02:10:00+00:00 ROUBAIX - Beaucourt Georges
            '1259', // 1918-06-01 02:00:00+00:00 ARCUEIL - Bersouille Paul
            '1261', // 1915-10-22 07:00:00+00:00 BULLY - Gauquelin-A1-1261
            '1262', // 1916-09-02 22:30:00+00:00 MONTIVILLIERS - Bihel Rene
            '1264', // 1921-03-19 03:00:00+00:00 BOULOGNE-BILLANC - Bongiorni Emile
            '1268', // 1913-02-24 02:00:00+00:00 LOISON-S/S-LENS - Bourbotte Francois
            '1270', // 1901-02-13 10:50:40+00:00 VITRY-S-SEINE - Boyer Jean
            '1271', // 1921-03-26 07:00:00+00:00 MONTIGNY-LES-ME - Braun Gaby
            '1272', // 1913-03-10 15:00:00+00:00 SIDI-BEL-ABBES - Brusseaux Michel
            '1275', // 1921-01-14 18:30:00+00:00 ROUBAIX - Carre Roger
            '1277', // 1908-05-30 03:50:40+00:00 HELLEMMES-LILLE - Cheuva Andre
            '1280', // 1905-12-31 13:50:40+00:00 ST-RAPHAEL - Cler Louis
            '1284', // 1924-07-19 03:00:00+00:00 SAINT-ETIENNE - Cuissard Antoine
            '1287', // 1919-11-02 03:00:00+00:00 SETE - Danzelle Pierre
            '1288', // 1918-06-28 01:00:00+00:00 MARSEILLE - Dard Georges
            '1289', // 1898-08-27 20:50:40+00:00 SETE - Dedieu Rene
            '1290', // 1909-06-19 22:50:40+00:00 LIEVIN - Gauquelin-A1-1290
            '1292', // 1907-11-01 20:50:40+00:00 RIS-ORANGIS - Delfour Edmond
            '1293', // 1907-02-15 12:20:40+00:00 VILLEJUIF - Delmer Henri
            '1296', // 1909-01-01 09:50:40+00:00 MARTIGUES - Di Lorto Laurent
            '1298', // 1924-01-15 16:00:00+00:00 ARLES - Domingo Marcel
            '1301', // 1893-11-05 18:50:40+00:00 ROUBAIX - Dubly Raymond
            '1303', // 1914-02-04 09:00:00+00:00 FRANCONVILLE-LA - Dupuis Maurice
            '1305', // 1909-07-08 07:50:40+00:00 ST-MAUR-DES-FOS - Finot Louis
            '1306', // 1923-03-13 21:00:00+00:00 MOHON - Flamion Pierre
            '1308', // 1914-06-16 15:00:00+00:00 MONTPELLIER - Gabrillargues Louis
            '1309', // 1905-09-02 23:20:40+00:00 CLICHY - Galey Marcel
            '1311', // 1890-07-22 01:50:04+00:00 IVRY-S/SEINE - Gamblin Lucien
            '1315', // 1892-08-26 03:50:40+00:00 MONTBEUGNY - Gravier Ernest
            '1316', // 1922-07-20 15:30:00+00:00 VALENCE - Gregoire Jean
            '1318', // 1923-06-01 17:30:00+00:00 ST-SERVAN-S/MER - Grumellon Jean
            '1322', // 1914-07-18 20:00:00-01:00 SCHIRRHEIM - Gauquelin-A1-1322
            '1325', // 1923-08-03 05:00:00+00:00 GANNAT - Huguet Guy
            '1326', // 1924-01-09 09:15:00+00:00 SOCHAUX - Gauquelin-A1-1326
            '1330', // 1906-04-21 05:50:40+00:00 CHOISY-LE-ROI - Kenner Rene
            '1332', // 1908-04-20 07:30:00-01:00 MULHOUSE - Korb Pierre
            '1333', // 1926-01-13 21:00:00+00:00 PARIS 8E - Lamy Roger
            '1334', // 1906-12-30 09:50:40+00:00 MAISONS-ALFORT - Laurent Jean
            '1335', // 1907-12-10 00:20:40+00:00 ST-MAUR-DES-FOS - Laurent Lucien
            '1338', // 1921-01-22 15:30:00+00:00 ROUBAIX - Leenaert Jacques
            '1340', // 1908-03-22 05:20:40+00:00 ORAN - Liberati Ernest
            '1341', // 1913-07-14 13:00:00+00:00 COLLIOURE - Llense Rene
            '1342', // 1925-02-12 09:00:00+00:00 ETERNON - Lorius Pierre
            '1347', // 1924-03-05 13:00:00+00:00 VILLERS-SEMEUSE - Marche Roger
            '1348', // 1905-12-24 11:20:40+00:00 BELFORT - Mattler Eugene
            '1349', // 1924-07-22 10:00:00+00:00 GENNEVILLIERS - Moreel Georges
            '1354', // 1913-12-18 15:00:00+00:00 ISBERGUES - Ourdoullie Marcel
            '1355', // 1905-08-15 01:50:40+00:00 HERICOURT - Pavillard Henri
            '1357', // 1899-10-08 09:20:40+00:00 DAX - Petit Rene
            '1358', // 1920-02-10 21:40:00+00:00 ORLEANS - Petitfils Andre
            '1360', // 1921-04-05 04:00:00+00:00 MARSEILLE - Pironti Felix
            '1362', // 1921-02-01 12:00:00+00:00 TOURCOING - Poblome Marcel
            '1363', // 1918-09-30 22:30:00+00:00 LE TEILLEUL - Prevost Jean
            '1364', // 1919-09-12 15:00:00+00:00 LE PEILLAC - Prouff Jean
            '1368', // 1890-12-12 04:51:16+00:00 BOULOGNE-BILLANC - Gauquelin-A1-1368
            '1370', // 1913-02-13 09:00:00+00:00 DUNKERQUE - Rio Roger
            '1371', // 1921-07-25 01:00:00+00:00 MARSEILLE - Robin Jean
            '1372', // 1920-10-17 01:00:00+00:00 SIDI-BEL-ABBES - Rodriguez Sauveur
            '1373', // 1910-04-30 15:50:40+00:00 MAISONS-ALFORT - Rose Georges
            '1376', // 1889-11-07 11:21:16+00:00 LEVALLOIS-PERRET - Schalbar Auguste
            '1380', // 1924-07-08 02:00:00+00:00 VILLERUPT - Gauquelin-A1-1380
            '1383', // 1922-01-04 00:45:00+00:00 LILLE - Somerlinck Marceau
            '1387', // 1920-01-01 08:00:00+00:00 LHOMME - Stricanne Marceau
            '1391', // 1918-09-29 21:00:00+00:00 ASNIERES - Tessier Henri
            '1392', // 1906-07-30 14:20:40+00:00 BREST - Thepot Alex
            '1394', // 1908-12-30 21:05:40+00:00 ARMENTIERES - Vandooren Jules
            '1396', // 1907-06-12 02:30:00-01:00 SABLON - Veinante Emile
            '1397', // 1909-07-15 05:35:40+00:00 ROUBAIX - Verriest Georges
            '1404', // 1912-12-21 13:00:00+00:00 SETIF - Zatelli Mario
            '1691', // 1903-01-16 15:50:40+00:00 PARIGNE - Bagneux-Faudoas Francois
            '1692', // 1918-08-29 19:00:00+00:00 BORDEAUX - Boulart Philippe
            '1693', // 1903-12-21 01:50:40+00:00 TOURS - Bourin Jean
            '1694', // 1898-12-11 04:50:40+00:00 PORT-MARLY - Dallemagne Marcel
            '1695', // 1918-08-02 04:00:00+00:00 TRELISSAC - Lamaze Henri
            '1696', // 1902-05-03 00:50:40+00:00 CAUDERAN - Le Quellec Yan
            '1697', // 1877-07-06 12:01:28+00:00 BIARRITZ - Massy Arnaud
            '1698', // 1905-07-12 06:50:40+00:00 ANGLET - Mourguiart Henri
            '1700', // 1926-12-20 08:00:00+00:00 PUTEAUX - Dot Raymond
            '1701', // 1894-05-09 17:20:40+00:00 NEVERS - Gounot Jean
            '1702', // 1908-02-02 15:00:00-01:00 OSTHEIM - Krauss Alfred
            '1703', // 1882-03-24 17:58:40+00:00 LIMOGES - Lalu Marcel
            '1704', // 1906-09-28 19:50:40+00:00 LA MACHINE - Rousseau Maurice
            '1705', // 1872-02-24 02:47:40+00:00 CROIX - Sandras Gustave
            '1706', // 1911-08-10 01:00:00-01:00 SARREGUEMINES - Schlindwein Antoine
            '1707', // 1904-05-10 20:50:40+00:00 NEUILLY - Solbach Armand
            '1710', // 1922-02-02 03:30:00+00:00 BELFORT - Vogelbacher Jeanette
            '1712', // 1905-11-13 12:50:40+00:00 LAVAL - Baril Marcel
            '1713', // 1893-07-12 05:50:40+00:00 ST-DENIS - Cadine Ernest
            '1715', // 1912-09-23 07:00:00+00:00 FRONTIGNAN - Ferrari Henri
            '1716', // 1924-03-15 10:00:00+00:00 MONTPELLIER - Firmin Georges
            '1717', // 1900-10-07 20:20:40+00:00 ROMANS-S/ISERE - Francois Roger
            '1719', // 1926-09-22 20:00:00+00:00 MONTPELLIER - Heral Max
            '1720', // 1908-04-21 09:50:40+00:00 ST-ETIENNE - Hostin Louis
            '1722', // 1923-06-04 05:00:00+00:00 SETE - Moulins Henri
            '1724', // 1903-11-03 16:50:40+00:00 LE VESINET - Rigoulot Charles
            '1725', // 1915-07-09 02:00:00+00:00 POITIERS - Thevenet Marcel
            '1729', // 1919-05-19 04:30:00+00:00 BELLE -ISLE - Fleury Joel
            '1730', // 1919-08-18 05:00:00+00:00 FERRETTE - Specker Justy
            '1731', // 1904-03-01 06:40:40+00:00 NEUILLY-S-SEINE - Brassart Yvonne
            '1732', // 1925-09-20 07:00:00+00:00 PONTOISE - Butin Jean
            '1733', // 1910-12-05 10:50:40+00:00 SENS - Chevalier Guy
            '1734', // 1905-08-29 14:50:40+00:00 DAMMARIE-LES-LY - Faure-Beaulieu Suzanne
            '1735', // 1910-06-30 02:50:40+00:00 LILLE - Grimonprez Felix
            '1736', // 1921-11-10 04:30:00+00:00 ORGEVAL - Lacroix Michel
            '1738', // 1918-06-15 04:00:00+00:00 BOULOGNE-S/MER - Peron Jean
            '1739', // 1890-07-20 01:20:04+00:00 ST-MANDE - Salarnier Robert
            '1740', // 1924-05-10 19:00:00+00:00 LILLE - Thieffry Jacques
            '1741', // 1913-06-17 11:00:00+00:00 LILLE - Vandame Pierre
            '1742', // 1916-01-20 04:00:00+00:00 MOLOMPIZE - Aurine Remy
            '1743', // 1915-02-09 08:00:00+00:00 ST-MARCEL - Brunaud Andre
            '1744', // 1919-09-17 21:00:00+00:00 NEUILLY-S-SEINE - Chesnau Rene
            '1746', // 1927-06-05 04:00:00+00:00 CLERMONT-FERRAND - Faure Edmond
            '1747', // 1909-11-03 21:50:40+00:00 FACHES-THUMENIL - Herland Robert
            '1748', // 1909-06-25 17:05:40+00:00 TARARE - Gauquelin-A1-1748
            '1749', // 1920-03-10 19:00:00+00:00 ELOYES - Leclere Jean
            '1750', // 1902-11-05 03:20:40+00:00 BERGUES - Pacome Charles
            '1751', // 1903-09-22 21:50:40+00:00 MEGRIT - Poilve Emile
            '1753', // 1912-07-21 09:30:00+00:00 DESVRES - Cornet Florimond
            '1754', // 1914-04-09 21:00:00+00:00 VANDENESSE - Courron Louis
            '1756', // 1914-03-16 12:45:00+00:00 CHARTRES - Hubert Claude
            '1761', // 1927-11-11 22:53:00+00:00 TOURCOING - Casteur Odette
            '1763', // 1883-07-03 21:19:20+00:00 MELUN - Drigny Emile
            '1770', // 1928-03-29 22:00:00+00:00 NEUILLY-PLAISAN - Gauquelin-A1-1770
            '1772', // 1915-11-18 07:00:00+00:00 CONSTANTINE - Nakache Alfred
            '1778', // 1930-01-22 08:00:00+00:00 AMIENS - Vallerey Gisele
            '1783', // 1899-10-21 22:50:40+00:00 BORDEAUX - Legendre Rene
            '1784', // 1912-01-06 03:00:00+00:00 PESSAC - Gauquelin-A1-1784
            '1785', // 1922-03-17 01:00:00+00:00 ST-ETIENNE-DE-B - Arce Emile
            '1786', // 1922-09-27 13:00:00+00:00 GUETHARY - Bichendaritz Pierre
            '1787', // 1913-09-25 07:00:00+00:00 CAMBO - Boudon Felix
            '1788', // 1918-10-27 15:00:00+00:00 NAY - Chatelain Andre
            '1789', // 1881-05-20 06:01:28+00:00 CAMBO - Chiquito Cambo Joseph
            '1790', // 1900-12-06 07:50:40+00:00 ESPELETTE - Darraidou Auguste
            '1791', // 1913-09-09 09:00:00+00:00 URRUGNE - Dongaitz Frederic
            '1792', // 1907-11-16 14:50:40+00:00 HASPARREN - Durruty Etienne
            '1793', // 1924-11-27 07:00:00+00:00 ST-JEAN-DE-LUZ - Etcheverry Pierre
            '1794', // 1917-01-07 06:00:00+00:00 ESPELETTE - Harambillet Jean
            '1795', // 1898-04-10 12:50:40+00:00 GUETHARY - Harispe Albert
            '1796', // 1909-06-19 17:50:40+00:00 ST-PALAIS - Gauquelin-A1-1796
            '1797', // 1913-07-23 18:00:00+00:00 SARE - Gauquelin-A1-1797
            '1798', // 1894-01-31 21:50:40+00:00 AINHOA - Leonis Auguste
            '1800', // 1905-12-18 08:35:40+00:00 BIARRITZ - Saleza Jose
            '1801', // 1912-10-22 02:30:00+00:00 ST-PALAIS - Urruty Jean
            '1804', // 1908-12-21 10:50:40+00:00 BAYONNE - Ainciart Edouard
            '1810', // 1904-10-12 09:50:40+00:00 PERPIGNAN - Bailette Marcel
            '1811', // 1917-05-20 06:30:00+00:00 VILLE D'AVRAY - Baladie Georges
            '1818', // 1894-12-07 08:50:40+00:00 NEUF-MESNIL - Beguet Louis
            '1824', // 1922-11-11 05:00:00+00:00 BIDART - Beraud Andre
            '1825', // 1914-01-11 10:00:00+00:00 BOUCAU - Bergese Felix
            '1826', // 1924-05-08 12:00:00+00:00 TOULOUSE - Bergougnan Yves
            '1827', // 1924-02-15 04:00:00+00:00 GRAULHET - Gauquelin-A1-1827
            '1831', // 1901-03-17 10:50:40+00:00 TOULOUSE - Bioussa Alexandre
            '1837', // 1922-05-11 17:00:00+00:00 ODOS - Bornenave Leon
            '1846', // 1912-10-12 01:00:00+00:00 VANVES - Gauquelin-A1-1846
            '1848', // 1885-09-18 21:44:28+00:00 BEZIERS - Cadenat Jules
            '1849', // 1918-04-29 21:00:00+00:00 BAYONNE - Caillou Robert
            '1850', // 1923-06-07 20:00:00+00:00 NISSAN-LES-ENSE - Gauquelin-A1-1850
            '1853', // 1905-02-09 03:50:40+00:00 TOULOUSE - Camel Andre
            '1855', // 1927-03-10 23:00:00+00:00 MONTPEY - Cantoni Vincent
            '1866', // 1920-02-29 15:00:00+00:00 TOULOUSE - Combes Gaston
            '1867', // 1885-09-11 07:21:40+00:00 BEAUVAIS - Communeau Marcel
            '1870', // 1899-02-07 14:50:40+00:00 ST-SEVER - Crabos Rene
            '1873', // 1925-01-01 07:00:00+00:00 ELNE - Crespo Joseph
            '1881', // 1926-02-09 07:00:00+00:00 PERPIGNAN - Desclaux Francis
            '1883', // 1929-10-04 00:20:00+00:00 ST-VINCENT-DE-T - Dizabo Pierre
            '1886', // 1924-05-01 11:00:00+00:00 TOULOUSE - Dop Jean
            '1889', // 1924-08-27 00:00:00+00:00 DAX - Dufau Gerard
            '1892', // 1899-04-11 05:50:40+00:00 ARGELES-GAZOST - Dupont Clement
            '1895', // 1922-07-03 22:00:00+00:00 TOULOUSE - Dutrain Henri
            '1904', // 1884-11-30 04:01:28+00:00 PAU - Forgues Fernand
            '1905', // 1889-08-15 08:04:40+00:00 ROCHEFORT-S-M - Franquenelle Andre
            '1907', // 1905-03-20 07:50:40+00:00 ILLE-S/TET - Galia Jean
            '1910', // 1900-10-11 05:50:40+00:00 PERPIGNAN - Got Raoul
            '1912', // 1904-01-22 09:50:40+00:00 PERPIGNAN - Graule Vincent
            '1916', // 1923-07-08 06:00:00+00:00 ASCAIN - Hatchondo Andre
            '1917', // 1898-02-18 14:50:40+00:00 OSTABAT - Jaureguy Adolphe
            '1921', // 1920-09-11 03:00:00+00:00 ST-VINCENT-DE-T - Junquas Louis
            '1930', // 1889-03-02 02:01:28+00:00 ANGLET - Larribau Leon
            '1931', // 1924-02-15 05:00:00+00:00 RIEUMES - Gauquelin-A1-1931
            '1932', // 1895-10-09 20:50:40+00:00 BAYONNE - Lasserre Rene
            '1934', // 1890-04-22 22:57:36+00:00 ARCACHON - Lerou Roger
            '1935', // 1924-03-11 02:00:00+00:00 AGEN - Lespes Ode
            '1941', // 1904-08-11 03:05:40+00:00 VAUCRESSON - Manoir Yves
            '1946', // 1920-09-28 12:30:00+00:00 PAU - Martin Lucien
            '1949', // 1920-06-23 04:00:00+00:00 GELES - Matheu Jean
            '1950', // 1887-05-27 23:10:40+00:00 NEUVILLE-S/SAON - Mauriat Paul
            '1951', // 1921-07-07 06:15:00+00:00 MONTLUEL - Mazon Louis
            '1957', // 1923-05-01 09:30:00+00:00 BORDEAUX - Moga Alban
            '1971', // 1923-08-01 08:00:00+00:00 LOURDES - Prat Jean
            '1974', // 1894-12-04 20:50:40+00:00 GORSES - Puech Louis
            '1976', // 1924-03-24 01:30:00+00:00 ANDERNACH - Gauquelin-A1-1976
            '1978', // 1902-03-29 16:50:40+00:00 PERPIGNAN - Ramis Roger
            '1995', // 1922-11-28 16:00:00+00:00 ODOS - Soro Robert
            '1996', // 1891-03-11 03:54:16+00:00 TOULOUSE - Struxiano Philippe
            '1997', // 1920-08-07 04:00:00+00:00 BIZANOS - Gauquelin-A1-1997
            '1999', // 1923-01-03 02:00:00+00:00 BOURG-EN-BRESSE - Gauquelin-A1-1999
            '2000', // 1914-04-16 23:00:00+00:00 THIERS - Thiers Pierre
            '2002', // 1921-11-20 06:00:00+00:00 ARGELES - Trescases Frederic
            '2004', // 1897-02-14 02:30:40+00:00 CHAMBERY - Vellat Edmond
            '2014', // 1920-05-04 04:00:00+00:00 CHAMONIX - Charlest Regis
            '2015', // 1921-07-06 22:30:00+00:00 CHAMONIX - Couttet James
            '2022', // 1920-08-07 09:00:00+00:00 GUILLAUMES - Gauquelin-A1-2022
            '2023', // 1924-07-23 20:30:00+00:00 GAILLARD - Penz Claude
            '2027', // 1899-01-01 20:20:40+00:00 LILLE - Bizard Xavier
            '2028', // 1909-05-21 15:50:40+00:00 SEVRES - Buret Maurice
            '2029', // 1904-12-19 16:20:40+00:00 SAUMUR - Busnel Amador
            '2031', // 1887-07-12 06:02:00+00:00 STE-COLOMBE - Clave Pierre
            '2032', // 1897-03-15 15:20:40+00:00 MONTHERLANT - Gudin De Vallerin Maurice
            '2034', // 1894-07-27 08:00:40+00:00 YVRE-L'EVEQUE - Jousseaume Andre
            '2035', // 1885-10-25 08:19:20+00:00 MORET - Lesage Xavier
            '2036', // 1910-06-07 19:35:40+00:00 GRAY - Maupeou D'Ableiges Pierre
            '2037', // 1876-09-09 15:24:00+00:00 CHARTRES - Royer-Dupre Henry
            '2042', // 1902-08-25 08:50:40+00:00 VAL-ANDRE - Bernard Alain
            '2043', // 1914-05-18 20:00:00+00:00 LA MADELEINE - Bernard Marcel
            '2044', // 1886-12-12 08:57:36+00:00 BORDEAUX - Blanchy Francois
            '2045', // 1912-02-27 18:30:00+00:00 TALENCE - Bollelli Henri
            '2046', // 1898-08-13 16:50:40+00:00 BIARRITZ - Borotra Jean
            '2047', // 1908-03-05 04:50:40+00:00 HYERES - Boussus Christian
            '2049', // 1928-02-02 10:15:00+00:00 CRETEIL - Chatrier Philippe
            '2050', // 1901-12-14 10:50:40+00:00 VILLEURBANNE - Gauquelin-A1-2050
            '2053', // 1926-11-29 20:10:00+00:00 NANCY - Ducos Haille Jean
            '2054', // 1902-07-06 10:50:40+00:00 TOULOUSE - Geraud Louis
            '2056', // 1921-05-02 17:30:00+00:00 STE-FOY - Gremillet Georges
            '2060', // 1928-04-02 23:50:00+00:00 ALGER - Gauquelin-A1-2060
            '2061', // 1906-08-06 01:20:40+00:00 BORDEAUX - Journu Roland
            '2065', // 1910-06-24 05:30:40+00:00 DIEPPE - Lesueur Jean
            '2069', // 1917-07-10 19:00:00+00:00 LOURDES - Pelizza Pierre
            '2071', // 1901-03-12 10:50:40+00:00 BIDART - Plaa Martin
            '2072', // 1909-02-24 15:50:40+00:00 CANNES - Ramillon Robert
            '2073', // 1895-05-05 05:50:40+00:00 BORDEAUX - Rodel Raymond
            '2074', // 1922-04-13 05:00:00+00:00 ANTIBES - Thomas Jacques
            '2076', // 1904-11-10 17:50:40+00:00 TOURS - Gauquelin-A1-2076
            '2077', // 1900-07-19 22:50:40+00:00 LILLE - Gauquelin-A1-2077
            '2078', // 1894-01-28 12:50:40+00:00 LANGRES - Gauquelin-A1-2078
            '2079', // 1906-01-23 03:20:40+00:00 CLAYE-SOU - Felbaco Pierre
            '2080', // 1914-05-04 16:00:00+00:00 ST-GENIES-D-M - Fournier Jean
            '2081', // 1901-04-20 16:50:40+00:00 MAIDIERES-LES-PT - Genot Lucien
            '2082', // 1899-12-12 07:20:40+00:00 HAUX - Martinez Hoz Gaston
            '2083', // 1876-05-29 03:57:36+00:00 SAUVETERRE-D-G - Parmentier Andre
            '2084', // 1903-04-27 16:50:40+00:00 ORRY-LA-V - Rouland Edouard
            '2085', // 1909-03-21 15:20:40+00:00 ST-CYR-D-S - Touchard Roger
            '2086', // 1893-11-17 10:50:40+00:00 LAVAL - Gerbault Alain
            '2088', // 1928-10-22 09:30:00+00:00 NANTES - Tiriau Roger
        ],
        '676MIL' => [
        ],
        '906PEI' => [
        ],
        '361PEI' => [
        ],
        '500ACT' => [
        ],
        '494DEP' => [
        ],
        '349SCI' => [
            '2555', // 1855-09-27 06:29:00+00:00 STRASBOURG - Appeil Paul
            '2556', // 1869-12-22 08:42:28+00:00 ROANNE - Auclair Noel
            '2557', // 1848-02-14 21:40:40+00:00 CHALON S-SAONE - Baillaud Edouard
            '2558', // 1802-09-30 00:44:28+00:00 MONTPELLIER - Balard Antoine
            '2559', // 1848-03-02 06:47:24+00:00 LUZY - Barbier Francois
            '2560', // 1879-04-01 12:41:04+00:00 MEZIERES - Barillon Emile
            '2561', // 1851-04-21 06:47:40+00:00 LILLE - Barrois Charles
            '2563', // 1841-04-06 20:39:52+00:00 RENEVE - Bassot Jean
            '2564', // 1864-10-22 10:37:48+00:00 ANNOIRE - Bataillon Jean
            '2565', // 1848-01-08 22:41:36+00:00 ANNONAY - Battandier Jules
            '2567', // 1829-10-20 20:35:12+00:00 NANCY - Bazin Henri
            '2571', // 1810-04-23 15:43:40+00:00 ERVY - Belgrand Marie
            '2572', // 1844-11-29 07:44:28+00:00 MONTPELLIER - Benoit Justin
            '2574', // 1840-03-23 01:35:12+00:00 NANCY - Bertin Louis
            '2576', // 1845-09-17 18:35:12+00:00 LUNEVILLE - Bichat Ernest
            '2578', // 1851-04-06 21:54:40+00:00 SISTELS - Bigourdin Guillaume
            '2579', // 1808-09-15 22:42:32+00:00 FISMES - Billet Felix
            '2580', // 1856-10-14 00:29:00+00:00 STRASBOURG - Binger Louis
            '2581', // 1878-02-01 00:48:56+00:00 LOCON - Blaringhem Louis
            '2582', // 1863-08-28 07:39:28+00:00 CHAUMONT - Blondel Andre
            '2583', // 1849-07-03 18:35:12+00:00 NANCY - Blondlot Rene
            '2584', // 1811-02-19 11:35:16+00:00 METZ - Boileau Pierre
            '2585', // 1819-12-22 12:44:28+00:00 MONTPELLIER - Gauquelin-A2-2585
            '2587', // 1871-01-07 03:49:44+00:00 ST AFFRIQUE - Gauquelin-A2-2587
            '2588', // 1828-09-02 16:47:24+00:00 GUERIGNY - Gauquelin-A2-2588
            '2589', // 1878-03-24 23:32:08+00:00 ANGERS - Bosler Jean
            '2590', // 1889-10-13 11:40:56+00:00 LORIENT - Gauquelin-A2-2590
            '2592', // 1827-05-29 04:47:40+00:00 THIERS - Bouquet Grye Anatole
            '2593', // 1857-02-21 22:30:36+00:00 STE MARIE AUX M - Bourgeois Joseph
            '2595', // 1846-01-12 16:49:44+00:00 NANT - Bouty Edmond
            '2596', // 1856-04-09 05:37:48+00:00 ST LAURENT GRAN - Bouvier Louis
            '2597', // 1844-10-23 09:50:48+00:00 AMIENS - Branly Edouard
            '2598', // 1811-08-23 12:41:36+00:00 ANNONAY - Bravais Auguste
            '2600', // 1822-10-09 21:37:08+00:00 VIENNE - Bresse Jacques
            '2601', // 1872-04-01 00:48:56+00:00 COURRIERES - Breton Jules
            '2602', // 1854-12-19 03:01:48+00:00 ST MARTIN LES M - Brillouin Marcel
            '2604', // 1892-08-15 00:50:40+00:00 DIEPPE - Gauquelin-A2-2604
            '2606', // 1822-03-22 22:06:20+00:00 NANTES - Bussy Louis
            '2607', // 1885-08-12 13:38:32+00:00 MARSEILLE - Cabannes Jean
            '2608', // 1832-09-21 10:39:52+00:00 CHATILLON S-SEIN - Cailletet Louis
            '2609', // 1811-05-31 06:04:20+00:00 VALOGNES - Caligny Anatole
            '2610', // 1852-09-20 14:59:20+00:00 ANGOULEME - Callandreau Pierre
            '2611', // 1871-09-15 12:44:28+00:00 MONTAGNAC - Camichel Charles
            '2612', // 1881-07-01 13:41:04+00:00 VOUZIERS - Caquot Albert
            '2616', // 1868-09-05 09:47:40+00:00 BERGUES - Caullery Maurice
            '2617', // 1822-01-18 05:40:24+00:00 LORIOL - Chancel Gustave
            '2618', // 1839-05-01 09:35:52+00:00 BESANCON - Chardonnet Hilaire
            '2619', // 1865-09-01 17:40:40+00:00 OULLINS - Gauquelin-A2-2619
            '2620', // 1802-01-07 00:41:36+00:00 DESAIGNES - Chazallon Antoine
            '2621', // 1882-08-15 19:40:40+00:00 VILLEFRANCHE - Gauquelin-A2-2621
            '2622', // 1873-06-23 12:59:40+00:00 DOMFRONT - Chevalier Auguste
            '2623', // 1810-08-15 22:35:16+00:00 ST QUIRIN - Chevandier Valdrome Eugene
            '2626', // 1821-05-25 17:51:28+00:00 SOREZE - Clos Dominique
            '2627', // 1852-11-28 03:51:28+00:00 GRAULHET - Colin Edouard
            '2628', // 1880-11-01 06:34:08+00:00 BAINS LES BAINS - Gauquelin-A2-2628
            '2629', // 1802-12-26 21:54:16+00:00 CAHORS - Combes Charles
            '2630', // 1841-06-08 21:35:24+00:00 PORT S/SAONE - Considere Armand
            '2631', // 1841-03-07 08:52:24+00:00 ORLEANS - Cornu Marie
            '2632', // 1866-03-04 07:20:48+00:00 AMIENS - Cosserat Eugene
            '2634', // 1869-10-09 08:56:08+00:00 BOURG-EN-BRESSE - Cotton Aime
            '2635', // 1872-02-05 03:39:08+00:00 BOURG-EN-BRESSE - Cotton Emile
            '2636', // 1833-12-03 15:48:20+00:00 PERPIGNAN - Crova Andre
            '2639', // 1856-11-01 04:03:00+00:00 LA DOREE - Daniel Lucien
            '2641', // 1842-08-15 00:42:36+00:00 NIMES - Gauquelin-A2-2641
            '2646', // 1814-06-25 08:35:16+00:00 METZ - Daubree Gabriel
            '2647', // 1801-01-08 15:37:08+00:00 GRENOBLE - Dausse Marie
            '2648', // 1826-09-07 03:01:28+00:00 ESPELETTE - David Armand
            '2649', // 1827-07-26 14:50:48+00:00 AMIENS - Debray Henri
            '2651', // 1854-05-13 03:40:40+00:00 AVIGNON - Delage Yves
            '2653', // 1817-02-03 16:35:16+00:00 METZ - Delesse Achille
            '2655', // 1854-06-25 16:18:20+00:00 PERPIGNAN - Deperet Charles
            '2656', // 1843-12-29 12:52:24+00:00 AILLANT S/MOUIL - Deprez Marcel
            '2659', // 1800-12-30 14:54:40+00:00 VENDOME - Dessaignes Victor
            '2660', // 1862-07-06 18:04:40+00:00 ETAULES - Deveaux Henri
            '2661', // 1843-10-20 07:51:40+00:00 RENNES - Ditte Alfred
            '2662', // 1804-08-25 08:42:36+00:00 VIGNAN - Dortet Tessan Urbain
            '2663', // 1846-06-15 02:54:16+00:00 TOULOUSE - Douville Henri
            '2664', // 1871-03-13 07:30:36-01:00 STE MARIE AUX M - Drach Jules
            '2665', // 1811-10-27 21:14:28+00:00 PORTIRAGNES - Duchartre Pierre
            '2668', // 1801-04-05 02:57:12+00:00 TOURS - Dujardin Felix
            '2671', // 1816-10-15 05:10:56+00:00 PLOEMEUR - Gauquelin-A2-2671
            '2672', // 1875-03-11 20:25:40+00:00 LE HAVRE - Durand-Vieil Georges
            '2673', // 1810-08-07 11:55:16+00:00 BOISSY LAMBERVI - Duval-Jouve Joseph
            '2674', // 1876-03-17 19:35:00+00:00 MISON - Esclangon Ernest
            '2676', // 1823-12-21 15:49:44+00:00 ST LEONS - Fabre Jean
            '2677', // 1867-06-11 00:38:32+00:00 MARSEILLE - Gauquelin-A2-2677
            '2678', // 1856-10-16 20:38:32+00:00 MARSEILLE - Fabry Eugene
            '2679', // 1862-04-20 21:38:32+00:00 MARSEILLE - Fabry Louis
            '2681', // 1812-02-28 11:54:00+00:00 DREUX - Fave Ildephonse
            '2684', // 1814-10-01 01:53:16+00:00 ST BENOIT DU SA - Faye Herve
            '2685', // 1868-11-19 07:36:20+00:00 ST MICHEL DE MA - Ferrie Gustave
            '2687', // 1852-10-03 01:47:40+00:00 BAILLEUIL - Flahault Charles
            '2688', // 1836-06-08 00:21:28+00:00 RAMBOUILLET - Fliche Henri
            '2689', // 1851-10-02 21:59:40+00:00 TARBES - Gauquelin-A2-2689
            '2690', // 1870-07-16 16:51:28+00:00 CASTRES - Fosse Richard
            '2691', // 1828-06-21 15:04:20+00:00 MORTAIN - Fouque Ferdinand
            '2692', // 1801-05-15 16:29:00+00:00 STRASBOURG - Fournet Joseph
            '2693', // 1842-05-23 03:54:16+00:00 TOULOUSE - Fournier Ernest
            '2694', // 1828-11-14 00:53:40+00:00 FOIX - Freycinet Charles
            '2695', // 1832-03-12 01:29:00+00:00 STRASBOURG - Friedel Charles
            '2696', // 1865-07-19 10:30:36+00:00 MULHOUSE - Gauquelin-A2-2696
            '2697', // 1849-04-24 04:54:16+00:00 ST BEAT - Gallieni Joseph
            '2698', // 1800-05-15 00:44:28+00:00 SETE - Gambart Adolphe
            '2700', // 1812-02-13 12:40:40+00:00 ORANGE - Gauquelin-A2-2700
            '2702', // 1845-05-08 06:59:20+00:00 BOUEX - Gayon Ulysse
            '2703', // 1816-08-21 06:29:00+00:00 STRASBOURG - Gerhardt Charles
            '2704', // 1834-04-24 21:47:40+00:00 VALENCIENNES - Gernez Desire
            '2707', // 1889-07-22 06:42:28+00:00 ST ETIENNE - Giraud Georges
            '2708', // 1866-10-08 08:52:32+00:00 ST DIZIERS - Glangeaud Philippe
            '2709', // 1879-10-11 21:35:12+00:00 BACCARAT - Godchot Marcel
            '2710', // 1807-03-25 04:35:16+00:00 HAYANGE - Godron Alexandre
            '2711', // 1856-05-23 17:39:08+00:00 MORNAY - Gonnesiat Francois
            '2712', // 1858-05-21 18:54:16+00:00 LANZAC - Goursat Edouard
            '2713', // 1854-02-19 12:41:36+00:00 VALS LES BAINS - Gouy Georges
            '2714', // 1839-03-08 23:35:12+00:00 HOUDREVILLE - Gauquelin-A2-2714
            '2717', // 1865-03-04 09:52:24+00:00 ORLEANS - Gauquelin-A2-2717
            '2718', // 1871-05-06 23:04:20+00:00 CHERBOURG - Grignard Victor
            '2719', // 1835-07-03 13:04:40+00:00 ROCHEFORT S-MER - Grimaux Edouard
            '2720', // 1849-08-23 11:50:28+00:00 BOURGES - Grossouvre A
            '2721', // 1861-12-27 07:40:40+00:00 AZE - Guichard Claude
            '2722', // 1873-07-11 19:06:20+00:00 ST NAZAIRE - Guillet Leon
            '2723', // 1885-06-09 15:37:48+00:00 LONS LE SAUNIER - Guyenot Emile
            '2724', // 1843-12-25 18:49:20+00:00 FONTAINEBLEAU - Guyou Emile
            '2725', // 1882-08-19 14:35:12+00:00 FLIREY - Gauquelin-A2-2725
            '2726', // 1865-12-08 21:51:28+00:00 VERSAILLES - Hadamard Jacques
            '2727', // 1844-10-30 05:25:40+00:00 ROUEN - Halphen Georges
            '2728', // 1861-10-31 01:03:56+00:00 BOULOGNE S-MER - Hamy Maurice
            '2729', // 1833-07-28 06:50:28+00:00 BOURGES - Haton Goupilliere Julien
            '2730', // 1840-07-17 18:29:00+00:00 STRASBOURG - Hatt Philippe
            '2731', // 1861-06-19 11:29:00+00:00 DRUSENHEIM - Haug Emile
            '2732', // 1836-12-02 17:50:40+00:00 ETAMPES - Hautefeuille Paul
            '2733', // 1812-06-12 12:45:40+00:00 VILLEFARGEAU - Hebert Edmond
            '2735', // 1871-04-24 21:31:40+00:00 COMPIEGNE - Helbronner Paul
            '2737', // 1815-08-21 04:30:36+00:00 LOGELBACH - Hirn Gustave
            '2738', // 1873-01-22 10:45:40+00:00 STE COLUMBES S/ - Houard Clodomir
            '2739', // 1829-03-23 03:55:40+00:00 ELBEUF - Houzeau Auguste
            '2741', // 1861-12-01 06:35:12+00:00 BREMONCOURT - Imbeaux Edouard
            '2742', // 1878-02-19 12:35:32+00:00 ANNEMASSE - Jacob Charles
            '2744', // 1875-02-05 16:17:24+00:00 NEVERS - Javillier Maurice
            '2746', // 1812-07-11 20:35:12+00:00 TOUL - Joly Nicolas
            '2747', // 1820-07-03 02:40:40+00:00 CARPENTRAS - Jonquieres Ernest
            '2748', // 1838-01-05 07:10:40+00:00 LYON - Jordan Camille
            '2749', // 1861-02-27 04:34:08+00:00 EPINAL - Joubin Louis
            '2750', // 1893-02-03 02:50:40+00:00 SIDI BEL ABBES - Julia Gaston
            '2751', // 1866-11-26 00:09:00+00:00 DREUX - Gauquelin-A2-2751
            '2754', // 1858-01-17 00:54:16+00:00 TOULOUSE - Koenigs Gabriel
            '2755', // 1803-05-22 03:30:36+00:00 COLMAR - Kuhlmann Frederic
            '2756', // 1863-02-04 02:40:40+00:00 MACON - Lacroix Alfred
            '2757', // 1862-06-11 14:16:24+00:00 PONTHOU - Gauquelin-A2-2757
            '2758', // 1814-12-20 17:36:20+00:00 NANTES - La Gournerie Jules
            '2759', // 1834-04-09 00:39:20+00:00 BAR-LE-DUC - Laguerre Edmond
            '2760', // 1816-12-25 05:54:16+00:00 TOULOUSE - Lallemand Alexandre
            '2761', // 1857-03-07 18:39:20+00:00 ST AUBIN S/AIRE - Lallemand Charles
            '2763', // 1839-12-30 05:50:28+00:00 BOURGES - Lapparent Albert
            '2765', // 1864-11-23 18:51:28+00:00 POISSY - Laubeuf Maxime
            '2766', // 1819-04-15 07:46:40+00:00 MOULINS - Laussedat Aime
            '2768', // 1868-12-19 08:07:24+00:00 BOISCOMMUN - Lebeau Paul
            '2769', // 1847-01-21 22:29:00+00:00 PECHELBRONN - Le Bel Achille
            '2770', // 1875-06-28 06:51:40+00:00 BEAUVAIS - Lebesque Henri
            '2771', // 1859-01-14 17:39:28+00:00 BLAISY - Lebeuf Auguste
            '2775', // 1861-08-29 05:43:40+00:00 PINEY - Leclainche Emmanuel
            '2776', // 1859-03-25 02:12:36+00:00 BAGNOLS S/CEZE - Leclerc Sablon Mathieu
            '2777', // 1856-01-07 15:34:08+00:00 ST NABORD - Lecomte Henri
            '2778', // 1802-04-28 18:47:40+00:00 AVESNES - Lecoq Henri
            '2779', // 1838-04-18 20:59:20+00:00 COGNAC - Lecoq Bois Baudran Francois
            '2780', // 1830-03-02 09:50:48+00:00 ABBEVILLE - Le Dieu Alfred
            '2781', // 1866-09-07 01:57:12+00:00 LOCHES - Leger Louis
            '2782', // 1898-06-11 10:50:40+00:00 LA SEYNE - Lejay Pierre
            '2783', // 1841-01-16 18:45:40+00:00 TONNERRE - Lemoine Georges
            '2786', // 1875-09-21 00:47:40+00:00 MARETZ - Leriche Maurice
            '2787', // 1805-11-21 15:21:28+00:00 VERSAILLES - Gauquelin-A2-2787
            '2788', // 1838-02-28 00:30:36+00:00 RIBEAUVILLE - Levy Maurice
            '2790', // 1809-03-24 07:48:56+00:00 ST OMER - Liouville Joseph
            '2791', // 1822-03-04 17:51:28+00:00 VERSAILLES - Lissajous Jules
            '2792', // 1823-07-30 02:06:20+00:00 NANTES - Lory Charles
            '2794', // 1870-07-10 13:51:28+00:00 POISSY - Lugeon Maurice
            '2795', // 1864-10-05 00:35:52+00:00 BESANCON - Lumiere Louis
            '2798', // 1872-11-26 20:39:52+00:00 AUXONNE - Maige Albert
            '2799', // 1878-05-29 16:52:48+00:00 LONS LE SAUNIER - Gauquelin-A2-2799
            '2800', // 1833-02-04 00:50:28+00:00 CHATEAUNEUF S/C - Mallard Ernest
            '2801', // 1829-07-30 14:54:16+00:00 TOULOUSE - Manen Leopold
            '2806', // 1820-01-18 20:40:40+00:00 CHALON S-SAONE - Mares Henri
            '2807', // 1874-06-12 17:18:00+00:00 ALGER - Marguet Frederic
            '2808', // 1846-10-10 09:38:32+00:00 AIX EN FROVENCE - Marion Fortune
            '2811', // 1807-10-18 16:38:32+00:00 MARSEILLE - Matheron Philippe
            '2812', // 1867-01-03 05:45:40+00:00 ST MAURICE AUX - Matignon Camille
            '2813', // 1878-09-19 03:49:20+00:00 PROVINS - Mauguin Charles
            '2814', // 1842-07-02 07:01:28+00:00 VAUDRY - Maupas Emile
            '2815', // 1871-02-27 00:52:24+00:00 ORLEANS - Gauquelin-A2-2815
            '2816', // 1809-03-07 01:35:52+00:00 MAICHE - Mauvais Victor
            '2817', // 1809-09-04 03:36:20+00:00 CHAMBERY - Menabrea Louis
            '2818', // 1835-11-12 04:40:40+00:00 CHALON S-SAONE - Meray Charles
            '2820', // 1866-06-08 14:52:24+00:00 CHATILLON S/LOI - Molliard Marin
            '2822', // 1876-04-28 23:30:56+00:00 NICE - Gauquelin-A2-2822
            '2823', // 1864-01-01 08:57:12+00:00 ST LAURENT EN G - Moussu Gustave
            '2824', // 1843-04-07 13:40:40+00:00 TOURNUS - Munier-Chalmas Ernest
            '2825', // 1846-08-10 13:29:00+00:00 SOULTZ S/S FORE - Müntz Achille
            '2826', // 1815-08-14 05:40:40+00:00 AUTUN - Naudin Charles
            '2827', // 1839-10-04 06:55:40+00:00 LE HAVRE - Normand Augustin
            '2828', // 1849-11-01 23:03:00+00:00 LAVAL - Oehlert Daniel
            '2829', // 1822-05-19 02:48:56+00:00 ESTREE COUCHY - Pagnoul Aime
            '2831', // 1802-02-11 22:30:36+00:00 RIBEAUVILLE - Parade Adolphe
            '2832', // 1851-04-08 16:48:56+00:00 ARRAS - Parenty Henry
            '2834', // 1880-07-04 18:03:56+00:00 ST POL - Gauquelin-A2-2834
            '2835', // 1807-02-26 05:04:20+00:00 VALOGNES - Pelouze Jules
            '2838', // 1873-05-19 00:57:36+00:00 BORDEAUX - Perez Charles
            '2839', // 1833-12-06 12:59:40+00:00 TARBES - Perez Jean
            '2840', // 1834-11-29 06:45:40+00:00 ST FARGEAU - Peron Alphonse
            '2841', // 1833-04-18 09:42:36+00:00 VALLARAUGUE - Perrier Francois
            '2842', // 1873-08-11 01:36:20+00:00 CHAMBERY - Perrier Bathie Henri
            '2843', // 1872-10-28 04:44:28+00:00 MONTPELLIER - Perrier-Delon Georges
            '2844', // 1870-09-30 19:17:40+00:00 LILLE - Perrin Jean
            '2845', // 1845-12-19 06:54:40+00:00 ST LOUP - Perrotin Henri
            '2847', // 1873-10-05 05:30:36-01:00 COLMAR - Peyerimhoff Paul
            '2849', // 1844-12-21 13:29:00+00:00 STRASBOURG - Picard Alfred
            '2850', // 1867-07-04 16:41:04+00:00 LA HARDOYE - Picart Luc
            '2852', // 1812-05-17 13:44:28+00:00 BRIOUDE - Pissis Aime
            '2853', // 1854-04-29 00:35:12+00:00 NANCY - Poincare Henri
            '2854', // 1882-10-07 12:02:00+00:00 RIOM DES LANDES - Poisson Charles
            '2856', // 1821-09-20 12:47:40+00:00 ISSOIRE - Pomel Auguste
            '2858', // 1872-01-20 07:06:20+00:00 NANTES - Porcher Charles
            '2860', // 1800-08-29 22:55:40+00:00 ROUEN - Pouchet Felix
            '2863', // 1820-04-16 10:52:00+00:00 ARGENTEUIL - Puiseux Victor
            '2865', // 1830-05-10 08:47:40+00:00 FOURNES - Raoult Francois
            '2866', // 1863-05-10 09:37:08+00:00 ST ROMAIN DE JA - Gauquelin-A2-2866
            '2867', // 1839-12-12 07:57:36+00:00 BORDEAUX - Rayet Georges
            '2868', // 1829-02-13 15:44:28+00:00 MONTPELLIER - Reboul Edmond
            '2869', // 1862-01-30 22:37:08+00:00 GRENOBLE - Recoura Albert
            '2870', // 1828-01-27 16:34:08+00:00 PLOMBIERES - Resal Henri
            '2871', // 1863-11-18 01:44:28+00:00 YSSINGEAUX - Richard Jules
            '2872', // 1817-05-21 17:00:36+00:00 GUEBWILLER - Riggenbach Nicolas
            '2873', // 1853-11-19 07:20:48+00:00 AMIENS - Riquier Charles
            '2874', // 1820-10-17 14:44:28+00:00 MONTPELLIER - Roche Edouard
            '2875', // 1812-08-10 20:35:16+00:00 METZ - Rolland Eugene
            '2876', // 1832-08-18 00:42:36+00:00 SOMMIERES - Rouche Eugene
            '2877', // 1899-11-07 18:05:40+00:00 BOURGES - Roy Maurice
            '2878', // 1834-01-14 12:44:28+00:00 GANGES - Sabatier Armand
            '2879', // 1854-11-05 16:50:36+00:00 CARCASSONNE - Sabatier Paul
            '2880', // 1823-07-28 02:34:08+00:00 ST ZACHARIE - Saporta Gaston
            '2881', // 1837-06-24 19:18:20+00:00 PERPIGNAN - Sarrau Emile
            '2882', // 1861-05-12 18:02:08+00:00 ANGERS - Sauvageau Camille
            '2885', // 1824-07-09 01:38:32+00:00 MARSEILLE - Schloesing Theophile
            '2886', // 1839-01-31 11:50:48+00:00 VERBERIE - Sebert Hippolyte
            '2887', // 1803-07-03 04:44:28+00:00 MONTPELLIER - Seguier Armand
            '2888', // 1851-12-21 07:52:24+00:00 ORLEANS - Sejourne Paul
            '2889', // 1808-09-06 02:54:00+00:00 BROUE - Senarmont Henri
            '2890', // 1856-01-27 05:59:40+00:00 BARBACHEN - Senderens Jean
            '2893', // 1826-06-04 17:35:52+00:00 BESANCON - Sire Georges
            '2894', // 1825-01-10 07:39:28+00:00 LONGEAU - Sirodot Simon
            '2895', // 1828-01-20 16:48:56+00:00 BRUAY - Souillart Cyrille
            '2896', // 1837-08-31 22:31:48+00:00 ST PEZENNE - Gauquelin-A2-2896
            '2897', // 1848-03-24 09:51:28+00:00 MANTES S/SEINE - Tannery Jules
            '2899', // 1859-07-03 16:40:40+00:00 LYON - Termier Pierre
            '2900', // 1831-01-30 08:35:16+00:00 METZ - Terquem Alfred
            '2902', // 1886-06-26 13:39:28+00:00 LANGRES - Thiry Rene
            '2904', // 1875-05-01 01:57:04+00:00 DOMME - Tilho Jean
            '2905', // 1830-05-26 13:35:12+00:00 FLAVIGNY S/MOSE - Tisserand Eugene
            '2906', // 1845-01-13 04:39:52+00:00 NUITS - Tisserand Felix
            '2907', // 1853-07-12 00:36:20+00:00 CHAMBERY - Trabut Louis
            '2908', // 1818-01-08 03:54:40+00:00 MONDOUBLEAU - Trecul Auguste
            '2910', // 1814-10-12 15:47:40+00:00 DUNKERQUE - Tresca Henri
            '2912', // 1815-09-12 19:57:12+00:00 AZAY LE RIDEAU - Tulasne Louis
            '2915', // 1849-12-25 17:21:28+00:00 VERSAILLES - Vallier Emmanuel
            '2916', // 1839-04-19 07:47:40+00:00 BAILLEUL - Van Thieghem Philippe
            '2918', // 1854-07-08 06:40:40+00:00 AVIGNON - Vayssiere Albert
            '2919', // 1806-07-05 08:39:52+00:00 BEAUNE - Verguette-Lamotte Alfred
            '2921', // 1859-09-24 08:44:28+00:00 LAVERUNE - Viala Pierre
            '2922', // 1850-03-16 08:37:08+00:00 VIENNE - Viguier Camille
            '2925', // 1841-11-16 00:39:28+00:00 LANGRES - Violle Jules
            '2926', // 1861-02-13 00:34:08+00:00 DOCELLES - Vuillemin Paul
            '2927', // 1858-07-25 16:47:40+00:00 TRITH ST LEGER - Wallerant Frederic
            '2928', // 1865-03-25 14:30:36+00:00 MULHOUSE - Weiss Pierre
            '2929', // 1867-07-28 06:47:40+00:00 LILLE - Wintrebert Paul
            '2930', // 1848-04-23 10:30:36+00:00 CERNAY - Witz Aime
            '2931', // 1827-11-09 14:15:32+00:00 VORGES - Wolf Charles
            '2932', // 1813-01-16 02:54:40+00:00 VENDOME - Yvon-Villarceau Antoine
            '2933', // 1847-01-14 02:05:12+00:00 NANCY - Gauquelin-A2-2933
        ],
        '884PRE' => [
        ],

    ];
}
