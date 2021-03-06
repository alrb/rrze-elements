/**
 * RRZE Accordion 1.0.0
 * RRZE Webteam
 */

jQuery(document).ready(function($) {
    // Close Accordions on start, except first
    $('.accordion-body').not(".accordion-body.open").not('.accordion-body.stayopen').hide();
    $('.accordion-body.open').parent().find('button.accordion-toggle').addClass('active');
    $('.accordion').each(function() {
        if ($(this).find('button.expand-all').length > 0) {
            var items = $(this).find(".accordion-group");
            var open = $(this).find(".accordion-body.open");
            if (items.length == open.length) {
                $(this).find('button.expand-all').attr("data-status", 'open').data('status', 'open').html(accordionToggle.collapse_all);
                //console.log("items = " + items.length + " open = " + open.length);
            }
        }

    });

    $('.accordion-toggle').bind('click', function(event) {
        event.preventDefault();
        var $accordion = $(this).attr('href');
        var $name = $(this).data('name');
        toggleAccordion($accordion);
        // Put name attribute in URL path if available, else href
        if (typeof($name) !== 'undefined') {
            window.history.replaceState(null, null, '#' + $name);
        } else {
            window.history.replaceState(null, null, $accordion);
        }
    });

    // Keyboard navigation for accordions
    $('.accordion-toggle').keydown(function(event) {
        if (event.keyCode == 32) {
            var $accordion = $(this).attr('href');
            var $name = $(this).data('name');
            toggleAccordion($accordion);
            if (typeof($name) !== 'undefined') {
                window.history.replaceState(null, null, '#' + $name);
            } else {
                window.history.replaceState(null, null, $accordion);
            }
        }
    });

    function toggleAccordion($accordion) {
        var $thisgroup = $($accordion).closest('.accordion-group');
        var $othergroups = $($accordion).closest('.accordion').find('.accordion-group').not($thisgroup);
        $($othergroups).children('.accordion-heading').children(' .accordion-toggle').removeClass('active');
        $($othergroups).children('.accordion-body').not('.accordion-body.stayopen').not('.accordion-body.open').slideUp();
        $($thisgroup).children('.accordion-heading').children('.accordion-toggle').toggleClass('active');
        $($thisgroup).children('.accordion-body').slideToggle();
    }

    function openAnchorAccordion($target) {
        if ($target.closest('.accordion').parent().closest('.accordion-group')) {
            var $thisgroup = $($target).closest('.accordion-group');
            var $othergroups = $($target).closest('.accordion').find('.accordion-group').not($thisgroup);
            $($othergroups).find('.accordion-toggle').removeClass('active');
            $($othergroups).find('.accordion-body').not('.accordion-body.stayopen').slideUp();
            $($thisgroup).find('.accordion-toggle:first').not('.active').addClass('active');
            $($thisgroup).find('.accordion-body:first').slideDown();
            // open parent accordion bodies if target = nested accordion
            $($thisgroup).parents('.accordion-group').find('.accordion-toggle:first').not('.active').addClass('active');
            $($thisgroup).parents('.accordion-body').slideDown();
        }
        var offset = $target.offset();
        var $scrolloffset = offset.top - 300;
        $('html,body').animate({
            scrollTop: $scrolloffset
        }, 'slow');
    }

    if (window.location.hash) {
        var identifier = window.location.hash.split('_')[0];
        var inpagenum = window.location.hash.split('_')[1];
        if (identifier == '#collapse') {
            if ($.isNumeric(inpagenum)) {
                var $findid = 'collapse_' + inpagenum;
                var $target = $('body').find('#' + $findid);
            }
        } else {
            var $findname = window.location.hash.replace('\#', '');
            var $target = $('body').find('div[name=' + $findname + ']');
        }
        if ($target.length > 0) {
            openAnchorAccordion($target);
        }
    }

    $('a:not(.prev, .next)').click(function(e) { // prev und next wegen Konflikt mit Timeline ausgeschlossen
        // nur auf Seiten, auf denen ein Accordion existiert,
        // und nur, wenn der geklickte Link nicht der Accordion-Toggle-Link oder der Expand-All-Link ist
        if (($('#accordion-0').length) &&
            (!$(this).hasClass("accordion-toggle"))) {
            var $hash = $(this).prop("hash");
            var identifier = $hash.split('_')[0];
            var inpagenum = $hash.split('_')[1];
            if (identifier == '#collapse') {
                if ($.isNumeric(inpagenum)) {
                    var $findid = 'collapse_' + inpagenum;
                    var $target = $('body').find('#' + $findid);
                }
            } else {
                var $findname = identifier.replace('\#', '');
                var $target = $('body').find('div[name=' + $findname + ']');
            }
            if ($target) {
                openAnchorAccordion($target);
            }
        }
    });

    $('.expand-all').click(function(e) {
        var $thisgroup = $(this).closest('.accordion');
        if ($(this).data('status') === 'open') {
            $($thisgroup).find('.accordion-body').slideUp();
            $($thisgroup).find('.accordion-toggle').removeClass('active');
            $(this).attr("data-status", 'closed').data('status', 'closed').html(accordionToggle.expand_all);
        } else {
            $($thisgroup).find('.accordion-body').slideDown();
            $($thisgroup).find('.accordion-toggle').addClass('active');
            $(this).attr("data-status", 'open').data('status', 'open').html(accordionToggle.collapse_all);
        }
    });

});
