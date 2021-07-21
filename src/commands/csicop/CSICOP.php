<?php
/******************************************************************************

    CSICOP = Committee for the Scientific Investigation of Claims of the Paranormal
    Class used by source management
                                   
    @license    GPL
    @history    2021-07-20 07:33:13+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\csicop;

use g5\model\Group;
use g5\commands\csicop\si42\SI42;
use g5\commands\csicop\irving\Irving;

class CSICOP {
    
    /**
        Path to the yaml file containing the characteristics of the source.
        Relative to directory data/model/source
    **/
    const SOURCE_DEFINITION_FILE = 'csicop' . DS . 'csicop.yml';
    
    /** Slug of source  **/
    const SOURCE_SLUG = 'csicop-committee';
    
    /** Slug of the group (all csicop records) **/
    const GROUP_SLUG = 'csicop';

    /** Slug of the group in db (canvas 1) **/
    const GROUP1_SLUG = 'csicop-batch1';

    /** Slug of the group in db (canvas 2) **/
    const GROUP2_SLUG = 'csicop-batch2';

    /** Slug of the group in db (canvas 3) **/
    const GROUP3_SLUG = 'csicop-batch3';
    
    /**
        Returns a Group object for CSICOP 408 sportsmen.
    **/
    public static function getGroup(): Group {
        $g = new Group();
        $g->data['slug'] = self::GROUP_SLUG;
        $g->data['name'] = "CSICOP 408 sportsmen";
        $g->data['description'] = "408 American athletes, gathered by CSICOP";
        $g->data['sources'] = [CSICOP::SOURCE_SLUG, SI42::SOURCE_SLUG, Irving::LIST_SOURCE_SLUG];
        return $g;
    }
    
    /**
        Returns a Group object for CSICOP Batch 1 (128 athletes).
    **/
    public static function getGroup_batch1(): Group {
        $g = new Group();
        $g->data['slug'] = self::GROUP1_SLUG;
        $g->data['name'] = "CSICOP Batch 1 - 128 sportsmen";
        $g->data['description'] = "128 American athletes, first batch of data gathered by CSICOP";
        $g->data['sources'] = [CSICOP::SOURCE_SLUG, SI42::SOURCE_SLUG, Irving::LIST_SOURCE_SLUG];
        $g->data['parents'] = [CSICOP::GROUP_SLUG];
        return $g;
    }
    
    /**
        Returns a Group object for CSICOP Batch 2 (198 athletes).
    **/
    public static function getGroup_batch2(): Group {
        $g = new Group();
        $g->data['slug'] = self::GROUP2_SLUG;
        $g->data['name'] = "CSICOP Batch 2 - 198 sportsmen";
        $g->data['description'] = "198 American athletes, second batch of data gathered by CSICOP";
        $g->data['sources'] = [CSICOP::SOURCE_SLUG, SI42::SOURCE_SLUG, Irving::LIST_SOURCE_SLUG];
        $g->data['parents'] = [CSICOP::GROUP_SLUG];
        return $g;
    }
    
    /**
        Returns a Group object for CSICOP Batch 3 (82 athletes).
    **/
    public static function getGroup_batch3(): Group {
        $g = new Group();
        $g->data['slug'] = self::GROUP3_SLUG;
        $g->data['name'] = "CSICOP Batch 3 - 82 sportsmen";
        $g->data['description'] = "82 American athletes, third batch of data gathered by CSICOP";
        $g->data['sources'] = [CSICOP::SOURCE_SLUG, SI42::SOURCE_SLUG, Irving::LIST_SOURCE_SLUG];
        $g->data['parents'] = [CSICOP::GROUP_SLUG];
        return $g;
    }
    
} // end class
