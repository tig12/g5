<?php
/******************************************************************************
    Code to generate class Gauquelin1955Data
    Input csv files are csv generated by gauquelin5 tool, and modified manually to ad a 1955 column
    
    @license    GPL
    @copyright  Thierry Graff
    @history    2017-05-06 18:01:35+02:00, Thierry Graff : Creation
********************************************************************************/


const DS = DIRECTORY_SEPARATOR;

const CSV_SEP = ','; // grrrr, libreoffice transformed ; in ,
/** Generated groups ; format : group code => [name, serie] **/
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

generate(__DIR__ . DS . 'z-gauquelin-1955'); // starts by z- to remain unversioned

// ******************************************************
/**
    @param  $dir    absolute path to the directory containing the modified csv files
**/
function generate($dir){
    $date = date('c');
    $res = <<<HEADER
<?php
/******************************************************************************
    Definition of groups used by Gauquelin in the book of 1955
    Generated on $date
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

HEADER;
    foreach(GROUPS as $groupCode => [$name, $serie]){
        $res .= "        '$groupCode' => [\n";
        $count = substr($name, 0, 3);
        $raw = file_get_contents($dir . DS . $serie . '.csv');
        $raw = str_replace('"', '', $raw); // libreoffice adds " and I don't know how to remove them
        $lines = explode("\n", $raw);
        $nlines = count($lines);
        $fieldnames = explode(CSV_SEP, $lines[0]);
        $flip = array_flip($fieldnames);
        for($i=1; $i < $nlines; $i++){
            if(trim($lines[$i]) == ''){
                continue;
            }
            $fields = explode(CSV_SEP, $lines[$i]);
            $code = $fields[$flip['1955']];
            if($code != $groupCode){
                continue;
            }
            $res .= "            '{$fields[$flip['NUM']]}', // {$fields[$flip['DATE']]} {$fields[$flip['PLACE']]} - {$fields[$flip['NAME']]}\n";
//echo "\n"; print_r($fields); echo "\n";
//break;
        }
        $res .= "        ],\n";
//break;
    }
    $res .= "
    ];
}
";
echo $res;    
}
