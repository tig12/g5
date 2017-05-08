<?php
/******************************************************************************
    Definition of groups used by Gauquelin in the book of 1955
    Generated on 2017-05-09T00:07:19+02:00
    @license    GPL
********************************************************************************/

namespace gauquelin5;

class Data1955{

    /** Groups ; format : group code => [name, serie] **/
    const GROUPS = [
        '576MED' => ["576 membres associés et correspondants de l'académie de médecine", 'A2'],
        '508MED' => ['508 autres médecins notables', 'A2'],
        '570SPO' => ['570 sportifs', 'A2'],
        '676MIL' => ['676 militaires', 'A2'],
        '906PEI' => ['906 peintres', 'A2'],
        '361PEI' => ['361 peintres mineurs', 'A2'],
        '500ACT' => ['500 acteurs', 'A2'],
        '494DEP' => ['494 députés', 'A2'],
        '349SCI' => ["349 membres, associés et correspondants de l'académie des sciences", 'A2'],
        '884PRE' => ['884 prêtres', 'A2'],
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
        ],
        '570SPO' => [
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
        ],
        '884PRE' => [
        ],

    ];
}
