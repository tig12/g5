<?php
/********************************************************************************
    Loads files data/tmp/ertel/ertel-4384-athletes.csv and ertel-4384-athletes-raw.csv in database.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2021, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\ertel\sport;

use tiglib\patterns\Command;
use g5\DB5;
use g5\model\Source;
use g5\model\Group;
use g5\model\Person;
use g5\commands\ertel\Ertel;
use g5\commands\gauq\LERRCP;
use g5\commands\cpara\CPara;
use g5\commands\csicop\CSICOP;
use g5\commands\cfepp\CFEPP;

class tmp2db implements Command {
    
    const REPORT_TYPE = [
        'small' => 'Echoes the number of inserted / updated rows',
        'full'  => 'Lists details of other restorations',
    ];
    
    /**
        @param  $params Array containing 1 element : the type of report ; see REPORT_TYPE
    **/
    public static function execute($params=[]): string {
        if(count($params) > 1){
            return "USELESS PARAMETER : " . $params[1] . "\n";
        }
        $msg = '';
        foreach(self::REPORT_TYPE as $k => $v){
            $msg .= "  $k : $v\n";
        }
        if(count($params) != 1){
            return "WRONG USAGE - This command needs a parameter to specify which output it displays. Can be :\n" . $msg;
        }
        $reportType = $params[0];
        if(!in_array($reportType, array_keys(self::REPORT_TYPE))){
            return "INVALID PARAMETER : $reportType - Possible values :\n" . $msg;
        }
        
        $report = "--- ErtelSport tmp2db ---\n";
        
        // source corresponding to 3a_sports - insert if does not already exist
        $source = Source::getBySlug(ErtelSport::SOURCE_SLUG); // DB
        if(is_null($source)){
            $source = new Source(ErtelSport::SOURCE_DEFINITION_FILE);
            $source->insert(); // DB
            $report .= "Inserted source " . $source->data['slug'] . "\n";
        }
        // source corresponding to Ertel (for partial ids) - insert if does not already exist
        $sourceErtel = Source::getBySlug(Ertel::SOURCE_SLUG); // DB
        if(is_null($sourceErtel)){
            $sourceErtel = new Source(Ertel::SOURCE_DEFINITION_FILE);
            $sourceErtel->insert(); // DB
            $report .= "Inserted source " . $sourceErtel->data['slug'] . "\n";
        }
        // source corresponding to Comité Para
        $sourceCPara = Source::getBySlug(CPara::SOURCE_SLUG); // DB
        if(is_null($sourceCPara)){
            $sourceCPara = new Source(CPara::SOURCE_DEFINITION_FILE);
            $sourceCPara->insert(); // DB
            $report .= "Inserted source " . $sourceCPara->data['slug'] . "\n";
        }
        // source corresponding to CSICOP
        $sourceCSICOP = Source::getBySlug(CSICOP::SOURCE_SLUG); // DB
        if(is_null($sourceCSICOP)){
            $sourceCSICOP = new Source(CSICOP::SOURCE_DEFINITION_FILE);
            $sourceCSICOP>insert(); // DB
            $report .= "Inserted source " . $sourceCSICOP->data['slug'] . "\n";
        }
        // source corresponding to CFEPP
        $sourceCFEPP = Source::getBySlug(CFEPP::SOURCE_SLUG); // DB
        if(is_null($sourceCFEPP)){
            $sourceCFEPP = new Source(CFEPP::SOURCE_DEFINITION_FILE);
            $sourceCFEPP->insert(); // DB
            $report .= "Inserted source " . $sourceCFEPP->data['slug'] . "\n";
        }
        
        // main group
        $g = Group::createFromSlug(ErtelSport::GROUP_SLUG); // DB
        if(is_null($g)){
            $g = ErtelSport::getGroup();
        }
        else{
            $g->deleteMembers(); // only deletes asssociations between group and members
        }
        // subgroups
        $subgroups = [];
        foreach(ErtelSport::SUBGROUP_SLUGS as $slug){
            $subgroups[$slug] = Group::createFromSlug($slug); // DB
            if(is_null($subgroups[$slug])){
                $subgroups[$slug] = ErtelSport::getSubgroup($slug);
            }
            else{
                $subgroups[$slug]->deleteMembers(); // DB only deletes asssociations between group and members
            }
        }
        
        // Main loop
        $nInsert = 0;
        $nUpdate = 0;
        $nRestoredSex = 0;
        $nRestoredOccu = 0;
        // both arrays share the same order of elements,
        // so they can be iterated in a single loop
        $lines = ErtelSport::loadTmpFile();
        $linesRaw = ErtelSport::loadTmpRawFile();
        $N = count($lines);
        $t1 = microtime(true);
        for($i=0; $i < $N; $i++){
            $line = $lines[$i];
            $subgroupSlug = self::computeSubgroupSlug($line);
            $erId = Ertel::ertelId('S', $line['NR']);
            $lineRaw = $linesRaw[$i];
            // All persons already in db are coming from Gauquelin data
            // see docs/sport-sportsmen.html#ertel-s-subsamples
            // Except line 4011 thoma-georg-1937-08-20 ; in file Müller 2 (612 famous men)
            if($line['GQID'] == '' && $line['NR'] != 4011){
                //
                // Person not in Gauquelin data
                //
                $p = new Person();
                $new = [];
                $new['trust'] = ErtelSport::TRUST_LEVEL;
                $new['name']['family'] = $line['FNAME'];
                $new['name']['given'] = $line['GNAME'];
                $new['birth'] = [];
                $new['birth']['date'] = $line['DATE'];
                $new['birth']['place']['cy'] = $line['CY'];
                if($line['C1'] != ''){
                    $new['birth']['place']['c1'] = $line['C1']; // useful only for SCT (Scotland)
                }
                $new['sex'] = $line['SEX'];
                //
                $p->addOccu(ErtelSport::computeSport($line));
                $p->addIdInSource($source->data['slug'], $line['NR']);
                $p->addIdPartial(Ertel::SOURCE_SLUG, $erId);
                $p->updateFields($new);
                $p->computeSlug();
                $p->addHistory(
                    command: 'ertel sport tmp2db',
                    sourceSlug: $source->data['slug'],
                    newdata: $new,
                    rawdata: $lineRaw
                );
                $nInsert++;
                $p->data['id'] = $p->insert(); // DB
            }
            else{
                // Person already in A1 or D6 or D10
                // Ertel data are considered of lower quality than Gauquelin
                // So update only missing information in Gauquelin:
                // - birth times (legal, not UTC) in A1
                // - precise sport in D6 and D10
                // - sex
                // Missing names in A1 are not handled here (done by class fixA1)
                $new = [];
                $tmp = LERRCP::explodeGauquelinId($line['GQID']);
                // $tmp != 2 for thoma-georg-1937-08-20 (the only record coming from Müller)
                if(count($tmp) == 2){
                    $NUM = $tmp[1];
                }
                switch($subgroupSlug){
                    //
                    // Already in A1
                    //
                    case 'ertel-1-first-french':
                    case 'ertel-2-first-european':
                    case 'ertel-6-para-champions':
                        $p = Person::getBySourceId('a1', $NUM);
                        $new['birth']['date'] = $line['DATE']; // A1 contains only date UT
                        $new['sex'] = $line['SEX'];
                        $nRestoredSex++;
                	break;
                    //
                	// Already in D6
                    //
                    case 'ertel-9-second-european':
                        $p = Person::getBySourceId('d6', $NUM);
                        $new['sex'] = $line['SEX'];
                        $nRestoredSex++;
                        // replace occu by Ertel value as Gauquelin file contains only 'sportsperson'
                        $new['occus'] = [ErtelSport::computeSport($line)];
                        $nRestoredOccu++;
                        // TODO compare birth dates; add an issue if Ertel != D6
                	break;
                    //
                	// Already in D10
                    //
                    case 'ertel-8-csicop-us':
                    case 'ertel-12-gauq-us':
                        $p = Person::getBySourceId('d10', $NUM);
                        $new['sex'] = $line['SEX'];
                        $nRestoredSex++;
                        // replace occu by Ertel value as Gauquelin file contains only 'sportsperson'
                        $new['occus'] = [ErtelSport::computeSport($line)];
                        $nRestoredOccu++;
                        // TODO compare birth dates; add an issue if Ertel != D10
                	break;
                }
                //
                // Particular cases
                //
                // Beltoise Jean Pierre 1937-04-26 ; in file E3, not mentioned by Ertel
                if($line['GQID'] == 'E3-95'){
                    $p = Person::getBySourceId('e3', 95);
                    $p->addOccu(ErtelSport::computeSport($line));
                    $new['occus'] = [ErtelSport::computeSport($line)];
                }
                // thoma-georg-1937-08-20 ; in file Müller 2 (612 famous men)
                else if($line['NR'] == 4011){
                    $p = Person::getBySourceId('afd2', 558);
                    // no new information added by Ertel
                }
                
                $p->addIdInSource($source->data['slug'], $line['NR']);
                $p->addIdPartial(Ertel::SOURCE_SLUG, $erId);
                $p->updateFields($new);
                // repeat fields to include in $history
                $new['ids-partial'] = [Ertel::SOURCE_SLUG => $erId];
                $p->addHistory(
                    command: 'ertel sport tmp2db',
                    sourceSlug: $source->data['slug'],
                    newdata: $new,
                    rawdata: $lineRaw
                );
                $nUpdate++;
                $p->update(); // DB
            }
            // main group
            $g->addMember($p->data['id']);
            // subgroups
            $subgroups[$subgroupSlug]->addMember($p->data['id']);
        } // end main loop

        $t2 = microtime(true);
        try{
            $g->data['id'] = $g->insert(); // DB
        }
        catch(\Exception $e){
            // group already exists
            $g->insertMembers();
        }
        foreach($subgroups as $slug => $subgroup){
            try{
                $subgroup->data['id'] = $subgroup->insert(); // DB
            }
            catch(\Exception $e){
                // group already exists
                $subgroup->insertMembers();
            }
        }
        $dt = round($t2 - $t1, 5);
        $report .= "Inserted $nInsert, updated $nUpdate ($dt s)\n";
        if($reportType == 'full'){
            $report .= "Nb restored sex = $nRestoredSex\n"
                     . "Nb restored occu = $nRestoredOccu\n";
        }
        return $report;
    }
    
    /** Returns the slug of the subgroup of a record **/
    private static function computeSubgroupSlug(&$line) {
        if($line['G55'] != ''){
            return 'ertel-1-first-french';
        }
        if($line['QUEL'] == 'G:A01' && $line['G55'] == '' && $line['PARA_NR'] == ''){
            return 'ertel-2-first-european';
        }
        if($line['QUEL'] == 'GMINI'){
            return 'ertel-3-italian-football';
        }
        if($line['QUEL'] == 'GMING'){
            return 'ertel-4-german-various';
        }
        if($line['QUEL'] == 'G_ADD'){
            return 'ertel-5-french-occasionals';
        }
        if($line['QUEL'] == 'G:A01' && $line['PARA_NR'] != ''){
            // only new persons coming from cpara
            // part of cpara data are in 'ertel-1-first-french'
            return 'ertel-6-para-champions';
        }
        if($line['QUEL'] == 'GCPAR'){
            return 'ertel-7-para-lowers';
        }
        if($line['QUEL'] == 'G:D10' && $line['CSINR'] != ''){
            return 'ertel-8-csicop-us';
        }
        if($line['QUEL'] == 'G:D06'){
            return 'ertel-9-second-european';
        }
        if($line['QUEL'] == 'GMINV'){
            return 'ertel-10-italian-cyclists';
        }
        if($line['QUEL'] == 'GMIND'){
            return 'ertel-11-lower-french';
        }
        if($line['QUEL'] == 'G:D10' && $line['CSINR'] == ''){
            return 'ertel-12-gauq-us';
        }
        if($line['QUEL'] == 'G_79F'){
            return 'ertel-13-plus-special';
        }
        throw new \Exception("Line without subgroup " .print_r($line, true));
    }
        
}// end class    

