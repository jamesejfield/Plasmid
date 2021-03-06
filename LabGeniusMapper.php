<?php

// Extension credits that will show up on Special:Version
$wgExtensionCredits['specialpage'][] = array(
    'path' => __FILE__,
    'name' => 'LabGenius Mapper',
    'version' => 1.0,
    'url' => 'http://ecoliwiki.net/colipedia/index.php/Extension:LabGeniusMapper',
    'author' => 'Rhys Algar, James Field, Ujjwal Thaakar',
    'descriptionmsg' => 'Magically injects svg plasmid maps',
);

$wgHooks['ParserFirstCallInit'][] = 'lgPlasmidMapInit';

function lgPlasmidMapInit(Parser $parser) {
    // register the plasmid map render hook on tag <pmap/>
    $parser->setHook('pmap', 'lgPlasmidMapRender');
    return true;
}

// Execute the rendering

// The full directory path for this extension
$dir = dirname( __FILE__ ) . DIRECTORY_SEPARATOR;

// Load the HelloWorld special page class
//$wgAutoloadClasses['LabGeniusMapper'] = $dir . 'LabGeniusMapper.body.php';

/*
    pmap is a single tag
    Attributes for pmap tag :-
        * id = an id for the div which encloses the plasmid map
        * gb-location = 'location of the GenBank file'
        * restriction-enzymes = 'comma seperated list of enzymes'

    Optional attributes
        * radius = radius of plasmid map in px. Defaults to 140.
        * cut-type = the cut type which is one of the following permissible values :-
            + single
            + double
            + all
        defaults to double.
*/

function lgPlasmidMapRender($input, array $args, Parser $parser, PPFrame $frame ) {
    if (!isset($args['id']) || $args['id'] == '') {
        return "<p>id attribute of <pmap> is not optional!";
    }
    $id = $args['id'];

    // ignore $input and fill it out with out with code to fetch the map and insert it here
    $invalidGBLocation = !filter_var($args['gb-location'], FILTER_VALIDATE_URL);
    if (!isset($args['gb-location']) || $invalidGBLocation) {
        return "<p>attribute gb-location of <pmap> is not optional and should be a valid url";
    }
    $gene_bank = $args['gb-location']; // the genebank location
    
    // comma seperated list of restriction enzymes with whitespaces stripped
    $restriction_enzymes = '';
    if (isset($args['restriction-enzymes'])) {
        $restriction_enzymes = preg_replace('/\s+/', '', $args['restriction-enzymes']);
        //$restriction_enzymes = preg_replace('/(\w+)/', "'$1'", $restriction_enzymes);
    }
    
    $radius = 140;
    if (isset($args['radius']) && $args['radius'] != '') {
        $radius = intval($args['radius']); // optional radius size for the svg map

    }

    $cutType = 'double';
    if (isset($args['cut-type']) && $args['cut-type'] != '') {
        $cutType = $args['cut-type']; // optional cut type
        // if there is a cut type then check if it is permissible
        switch ($cutType) {
            case 'single':
            case 'double':
            case 'all':
                break;
            
            default:
                // invalid cut type - render out an error
                $input = <<<EOT
<p>Your cut-type attribute value is impermissible! It can only be one of the following :-</p>
<ul>
<li>single</li>
<li>double</li>
<li>all</li>
</ul>
EOT;
                return $input;
        }
    }

    // Insert the script to make an ajax call an replace this <pmap> with the svg returned
    // but first check if wgUseAjax is true or not
    global $wgUseAjax;
    if (!$wgUseAjax) {
        $input = <<<EOT
<p>Can't use ajax since \$wgUseAjax == false</p>
EOT;
        return $input;
    }

// Note, for testing purposes, the location of the javascript should be changed to - '../extensions/Plasmid/plasmidScript.js'
    $input = <<<EOT
<script>
$(function(){
var url = 'https://s3.amazonaws.com/labgeniusstatic/js/mediawikiScript.js';
$.getScript(url, function() {
options = {
gene_bank: '$gene_bank',
restriction_enzymes: '$restriction_enzymes',
radius: $radius,
cutType: '$cutType'
};
id = '$id';
LGRun('$gene_bank');
});
});
</script>

<style type="text/css">
.glow:hover {
box-shadow: 0 0 5px 2px #f00;
-webkit-box-shadow: 0 0 5px 2px #f00;
-moz-box-shadow: 0 0 5px 2px #f00;
}
</style>


<div id="$id" style="float:right; border: 1px solid black; z-index: 0;">
<div id= "Button_$id" style="position:relative; top:0px; left:10px; z-index: 1;">
<div align="center"><b>Powered by</b> <a href="http://beta.labgeni.us/welcome/#coliwiki"><img class="glow" src="https://s3.amazonaws.com/labgeniusstatic/Logo.png" alt="LabGenius" /></a></div>
<b>Cutters</b> 
<select id= "Cutter_$id" onchange="ButtonCutter($id , '$gene_bank' , $radius , '$restriction_enzymes' , value )">
<option value="single">Single</option>
<option value="double" selected="selected">Double</option>
<option value="all">All</option>
<option value="none">None</option>
</select>
</div>
</div>
EOT;
    return $input;
    
}


