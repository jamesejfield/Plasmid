<?php

// Extension credits that will show up on Special:Version
$wgExtensionCredits['specialpage'][] = array(
    'path' => __FILE__,
    'name' => 'LabGenius Mapper',
    'version' => 1.0,
    'url' => 'http://ecoliwiki.net/colipedia/index.php/Extension:LabGeniusMapper',
    'author' => 'Ujjwal Thaakar',
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

    $width = 'auto';
    if (isset($args['width'])) {
        $width = $args['width'];
    }

    $height = 'auto';
    if (isset($args['height'])) {
        $height = $args['height'];
    }

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

    $input = <<<EOT
<script>
// make an ajax request to fetch the map and replace pmap
$.ajax({
url: 'http://www.labgeni.us/api/plasmid_map/?callback=?',
data: {
gb_location: '$gene_bank',
restriction_enzymes: "$restriction_enzymes",
radius: $radius,
cut_types: '$cutType'
},
dataType: 'json',
success: function(data) {
data.plasmid_map = data.plasmid_map.replace("<?xml version=\"1.0\" encoding=\"UTF-8\"?>", "");
var map = $(data.plasmid_map);
// determine the bounds and crop out the extra whitespace
var minX, maxX, minY, maxY;
minX = minY = 10000.0; // we know the svg is not wider than 10000px to be on the safe side
maxX = maxY = 0.0;
map.find('text').each(function(i, v) {
v = $(v);
var x = parseFloat(v.attr('x'));
var y = parseFloat(v.attr('y'));
if (!(isNaN(x) || isNaN(y))) {
minX = Math.min(minX, x);
minY = Math.min(minY, y);
maxX = Math.max(maxX, x);
maxY = Math.max(maxY, y);
}
});
// add padding around the map
var padding = 25;
minX -= padding;
maxX += padding;
minY -= padding;
maxY += padding; 
var width = map.attr('width'), height = map.attr('height');
var div = $('#$id');
div.append(map);
var divWidth = parseInt('$width', 10), divHeight = parseInt('$height', 10);
div.scrollLeft(width/2 - divWidth/2);
div.scrollTop(height/2 - divHeight/2);
// prevent out of bounds scrolling
div.scroll(function(e) {
if (div.scrollTop() < minY) div.scrollTop(minY);
if (div.scrollTop() > maxY - divHeight) div.scrollTop(maxY - divHeight);
if (div.scrollLeft() < minX) div.scrollLeft(minX);
if (div.scrollLeft() > maxX - divWidth) div.scrollLeft(maxX - divWidth);
});
}
});
</script>
<div id="$id" style="width: $width; height: $height; overflow: scroll;">
</div>
EOT;
    return $input;
}
