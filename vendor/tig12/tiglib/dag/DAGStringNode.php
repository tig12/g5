<?php
/******************************************************************************
    Represents a node of a DAG (Directed acyclic graph)
    The value of a node is a string
    
    @license    GPL
    @history    2021-07-29 17:10:25+02:00, Thierry Graff : Creation
    @history    2021-09-16 12:18:30+02:00, Thierry Graff : Integrate to tiglib
********************************************************************************/
namespace tiglib\dag;

class DAGStringNode{
    
    private string $value;
    
    private array $edges; // array of DAGStringNode
    
    public function __construct(string $value, array $edges=[]){
        $this->value = $value;
        $this->edges = $edges;
    }
    
    public function getValue(): string {
        return $this->value;
    }
    
    public function addEdge(DAGStringNode $edge) {
        $this->edges[] = $edge;
    }
    
    /** 
        Returns an array of reachable edges.
        No transitive reduction.
        WARNING: if 2 nodes have the same value, both are returned.
    **/
    public function getReachable(): array {
        $res = $this->edges;
        foreach($this->edges as $edge){
            // here could check for uniqueness
            $res = array_merge($res, $edge->getReachable());
        }
        return $res;
    }
    
    /** 
        Returns an array containing the values (strings) of reachable edges
        No transitive reduction.
        Duplicate entries are removed.
    **/
    public function getReachableAsStrings(): array {
        $tmp = $this->getReachable();
        $res = [];
        foreach($tmp as $node){
            $res[] = $node->getValue();
        }
        return array_unique($res);
    }
    
} // end class
