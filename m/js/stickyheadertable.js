(function($){
    $.fn.stickyheader=function(options,callback){
        var elm=$(this);
        var opts=jQuery.extend({
            tblid:$(this).attr('id'),
            fixedHeader:true,
            tblheight:500,
            cellspacing:0
        },options);
        $(elm).wrap("<div id='"+opts.tblid+"_table_wrapper'>");
        $('#'+opts.tblid+'_table_wrapper').before("<div id='"+opts.tblid+"_div_header'></div>");
        
        var tblhead="<table cellpadding='0' style='box-shadow: 0 4px 4px #333;' cellspacing='"+opts.cellspacing+"' id='"+opts.tblid+"_fixedheader_tbl'>";
        tblhead+="<thead><tr>";
        $(elm).find('th').each(function(){
            tblhead+="<th>"+$(this).html()+"</th>";
        });
        tblhead+="</tr></thead><tbody></tbody></table>";
        $('#'+opts.tblid+'_table_wrapper').css({
            height:$(elm).height(),
            'overflow':'auto',
            'position':'relative',
            'z-index':3,
            'height':opts.tblheight
        });
        //adjusting for scrollbar
        var scrTp=$('#'+opts.tblid+'_table_wrapper').scrollTop(2);
        var scrTp=$('#'+opts.tblid+'_table_wrapper').scrollTop();
        $('#'+opts.tblid+'_div_header').css({
            'overflow-y':(scrTp==0)?'none':'scroll',
            'position':'absolute',
            'z-index':'100',
            'box-shadow':'0 4px 4px #333'
        });
        $('#'+opts.tblid+'_div_header').html(tblhead);
        //fixing chrome overlay bugs
        var dupHead=$('#'+opts.tblid+'_fixedheader_tbl');
        //Applying head CSS
        $(dupHead).css({
            'z-index':4,
            'position':'relative'
        });
        //Extracting widths
        var basewidths=[];
        $('#'+opts.tblid).find('th').each(function(){
            basewidths.push($(this).width());
        });
            
        //Applying widths to duplicate head and simulating click events
        $(dupHead).find('th').each(function(z){
            $(this).css('width',(basewidths[z]));
            $(this).click(function(){
                $('#'+opts.tblid).find('th').eq(z).trigger('click');
            });
        });
        setTimeout(function(){
            if (typeof callback == 'function') { // make sure the callback is a function
                callback.call(this); // brings the scope to the callback
            }
        },100);
    }
})(jQuery);