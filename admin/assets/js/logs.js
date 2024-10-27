function AddReadMore() {
    //This limit you can set after how much characters you want to show Read More.
    var charLmt = 0;
    // Text to show when text is collapsed
    var readMoreTxt = "View";
    // Text to show when text is expanded
    var readLessTxt = "Hide";


    //Traverse all selectors with this class and manupulate HTML part to show Read More
    jQuery(".addReadMore").each(function() {
        if (jQuery(this).find(".firstSec").length)
            return;

        var allstr = jQuery(this).text();
        console.log('allstr', allstr)
        if (allstr.length > charLmt) {
            var firstSet = allstr.substring(0, charLmt);
            var secdHalf = allstr.substring(charLmt, allstr.length);
            var strtoadd = firstSet + "<span class='SecSec'>" + secdHalf + "</span><span class='readMore'  title='Click to View Response'>" + readMoreTxt + "</span><span class='readLess' title='Click to Show Less'>" + readLessTxt + "</span>";
            jQuery(this).html(strtoadd);
        }

    });
    //Read More and Read Less Click Event binding
    jQuery(document).on("click", ".readMore, .readLess", function() {
        jQuery(this).closest(".addReadMore").toggleClass("showlesscontent showmorecontent");
    });
}
AddReadMore();