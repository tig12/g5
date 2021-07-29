<?php


require_once __DIR__ . DIRECTORY_SEPARATOR . 'DAGStringNode.php';

$streetDancer = new DAGStringNode('streetDancer');
$operaDancer = new DAGStringNode('operaDancer');
$folkDancer = new DAGStringNode('folkDancer');
$ticketChecker = new DAGStringNode('ticketChecker');
$streetArtist = new DAGStringNode('streetArtist');
$dancer = new DAGStringNode('dancer');
$artist = new DAGStringNode('artist');
$operaEmployee = new DAGStringNode('operaEmployee');

$streetDancer->addEdge($streetArtist);
$streetDancer->addEdge($dancer);
$operaDancer->addEdge($dancer);
$operaDancer->addEdge($operaEmployee);
$folkDancer->addEdge($dancer);
$ticketChecker->addEdge($operaEmployee);
$streetArtist->addEdge($artist);
$dancer->addEdge($artist);

//echo "\n"; print_r($streetDancer->getRelatedNodes()); echo "\n";
echo "streetDancer: \n"; print_r($streetDancer->getReachableAsStrings()); echo "\n";
echo "ticketChecker: \n"; print_r($ticketChecker->getReachableAsStrings()); echo "\n";
echo "operaEmployee: \n"; print_r($operaEmployee->getReachableAsStrings()); echo "\n";
echo "operaDancer: \n"; print_r($operaDancer->getReachableAsStrings()); echo "\n";

