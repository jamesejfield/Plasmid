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
            var innerDiv = $("<div id=" + id + "-InnerDiv" + "></div>");
            div.append(innerDiv.append(map));
            
            
            // Compute boundaries of rendered SVG image
            var Box = map[0].getBBox();
            minX = Box.x;
            minY = Box.y;
            maxX = minX + Box.width;
            maxY = minY + Box.height;

            // Compute top trim
            topTrim = minY;
            
            // Compute bottom trim
            bottomTrim = map.height() - maxY;
            
            // Compute right trim
            rightTrim = map.width(); - maxX;
            
            // Compute left trim
            leftTrim = minX;
           
            // Trim top and left
            map.css('position','relative').css('top','-'+topTrim+'px').css('left','-'+leftTrim+'px');
            
            // set height and width values for the outer div based on the svg bounding box
            var topbuffer = 100;
            var divWidth =  Box.width + "px";
            var OuterDivHeight = topbuffer + Box.height + "px";
            var InnerDivHeight = Box.height + "px";
            div.css('width', divWidth).css('height', OuterDivHeight);    	
            innerDiv.css('width', divWidth).css('height', InnerDivHeight);
            
            // Find position of outer div
			var outerDivTop = div[0].offsetTop;
			var outerDivLeft = div[0].offsetLeft;    
			
	
	}
	
});

}





function ButtonCutter($id , gene_bank , radius , restriction_enzymes , cutters ) {
var target = ($id.id + "-InnerDiv") ;
var target = document.getElementById(target);
target.removeChild( target.firstChild );


   // make an ajax request to fetch the map and replace pmap
    $.ajax({
        url: 'http://www.labgeni.us/api/plasmid_map/?callback=?',
        cache: true,
        data: {
            gb_location: gene_bank , 
            restriction_enzymes: restriction_enzymes,
            radius: radius,
            cut_types: cutters
            
        },
        dataType: 'json',
        success: function(data) {
            data.plasmid_map = data.plasmid_map.replace("<?xml version=\"1.0\" encoding=\"UTF-8\"?>", "");
            var map = $(data.plasmid_map);
            var div = document.getElementById($id.id);
            target.appendChild(map[0]); 
            
          map.css('position','relative').css('top','-'+topTrim+'px').css('left','-'+leftTrim+'px');


             }})

}

