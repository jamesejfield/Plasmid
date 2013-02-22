function validOffset(v) {
    return v < 0?0:v;
}

function centreMap(map){
    map.find('text').each(function(i, v) {
        v = $(v);
        var x = parseFloat(v.attr('x'));// || parseFloat(v.attr('x1')) || parseFloat(v.attr('x2'));
        var y = parseFloat(v.attr('y'));
        if (!isNaN(x) && !isNaN(y)) {
            minX = Math.min(minX, x-v.width());
            minY = Math.min(minY, y-v.height());
            maxX = Math.max(maxX, x+v.width());
            maxY = Math.max(maxY, y+v.height());
        }
    });
    // add padding around the map
    var padding = 0;
    minX -= padding;
    maxX += padding;
    minY -= padding;
    maxY += padding;
    var width = map.width(), height = map.height();
    var divWidth = maxX - minX, divHeight = maxY - minY;
    innerDiv.css('width', divWidth).css('height', divHeight);
    innerDiv.css('position', 'relative').offset({
        left: validOffset(options.width - divWidth),
        top: validOffset(options.height - divHeight)
    });
    innerDiv.css('top', validOffset(options.height - divHeight)).css('left', validOffset(options.width - divWidth));
    scrollDiv.css('width', validOffset(2*options.width - divWidth)).css('height', validOffset(2*options.height - divHeight));

    // scrolling
    innerDiv.css('overflow', 'scroll');
    innerDiv.scrollLeft(minX);
    innerDiv.scrollTop(minY);
    innerDiv.css('overflow', 'hidden');

    div.scrollLeft(scrollDiv.width()/2 - options.width/2);
    div.scrollTop(scrollDiv.height()/2 - options.height/2);
}

function LGRun(id, options) { // accept id of the div and a options hash
    // make an ajax request to fetch the map and replace pmap
    $.ajax({
        url: 'http://www.labgeni.us/api/plasmid_map/?callback=?',
        cache: true,
        data: {
            gb_location: options.gene_bank,
            restriction_enzymes: options.restriction_enzymes,
            radius: options.radius,
            cut_types: options.cutType
        },
        dataType: 'json',
        success: function(data) {
            data.plasmid_map = data.plasmid_map.replace("<?xml version=\"1.0\" encoding=\"UTF-8\"?>", "");
            var map = $(data.plasmid_map);
            // determine the bounds and crop out the extra whitespace
            var minX, maxX, minY, maxY;
            minX = minY = 10000.0; // we know the svg is not wider than 10000px to be on the safe side
            maxX = maxY = 0.0;
            var div = $('#'+id);
            var innerDiv = $('<div></div>');
            var scrollDiv = $('<div></div>');
            div.append(scrollDiv.append(innerDiv.append(map)));
            centreMap(map)
        }
    });
}
