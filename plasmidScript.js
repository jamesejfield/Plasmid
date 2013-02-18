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
        var div = $('#$id');
        var innerDiv = div.append('<div></div>');
        innerDiv.css('width', maxX - minX).css('height', maxY - minY).css('overflow', 'none');
        innerDiv.append(map);
        div.append(innerDiv);
        var width = map.attr('width'), height = map.attr('height');
        var divWidth = parseInt('$width', 10), divHeight = parseInt('$height', 10);
        div.scrollLeft(width/2 - divWidth/2);
        div.scrollTop(height/2 - divHeight/2);
    }
});