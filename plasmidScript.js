function validOffset(v) {
    return v < 0?0:v;
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
            var div = $('#'+id);
            var innerDiv = $('<div></div>');
            div.append(innerDiv.append(map));
			// determine the bounds and crop out the extra whitespace
     		var minX, maxX, minY, maxY;
		    minX = minY = 10000.0; // we know the svg is not wider than 10000px to be on the safe side
		    maxX = maxY = 0.0;
		    //map.load(function(){
		    	map.find('text').each(function(i, v) {
    		    	v = $(v);
	    	    	var x = parseFloat(v.attr('x'));// || parseFloat(v.attr('x1')) || parseFloat(v.attr('x2'));
		    	    var y = parseFloat(v.attr('y'));
    		    	if (!isNaN(x) && !isNaN(y)) {
        		    	minX = Math.min(minX, x-v.outerWidth());
	            		minY = Math.min(minY, y-v.outerHeight());
				        maxX = Math.max(maxX, x+v.outerWidth());
				        maxY = Math.max(maxY, y+v.outerHeight());
        			}
			    });
			    console.log(minX,minY,maxX,maxY);
    			svgHeight = map.height();
	    	    svgWidth = map.width();
	        	topTrim = minY-10;
	    	    bottomTrim = svgHeight - maxY;
    			leftTrim = minX - 50;
	    		rightTrim = svgWidth - maxX;
    			// Trim top and left
			    map.css('position','relative').css('top','-'+topTrim+'px').css('left','-'+leftTrim+'px');
    			// Trim holding Div
    			var divWidth = maxX - minX, divHeight = maxY - minY;
		    	innerDiv.css('width', divWidth).css('height', divHeight);
		    	
		    	// set height and width values for the outer div
		    	var divWidth = divWidth + 150 + "px" ; 
		    	var divHeight = divHeight + 20 + "px" ; 
		    	// get the div element
				d = document.getElementById(id);
				// set the width
				d.style.width=divWidth;
				// set the height
				d.style.height=divHeight;
		    	

		    	
	     	//})
		}
	});
}
