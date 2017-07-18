/*
 * Simple Wp Sitemap admin js
 */

jQuery(function ($) {
    'use strict';

    var form = $('#simple-wp-sitemap-form'),
        orderList = $('#sitemap-display-order'),
        tab = location.search[location.search.length - 1];


    // Changes an item in order menu
    function changeOrderItem (e) {
        var arrow = $(e.target), li = arrow.parent();

        if (arrow.hasClass('sitemap-up') && li.prev().length) {
            li.prev().before(li);

        } else if (arrow.hasClass('sitemap-down') && li.next().length) {
            li.next().after(li);
        }
    };

    // Sets hidden fields values and submits the form to save changes
    function submitForm (e) {
        e.preventDefault();

        var inputs = orderList.find('input[data-name]');

        orderList.find('input[type=hidden]').each(function (i) {
            $(this).val((i + 1) + '-|-' + inputs.eq(i).val());
        });

        form.attr('action', form.attr('action') + '&tab=' + (parseInt(form.tabs('option', 'active')) + 1));
        form.get(0).submit();
    };

    // Restores default order options
    function restoreDefaultOrder () {
        var sections = ['Home', 'Posts', 'Pages', 'Other', 'Categories', 'Tags', 'Authors'],
            html = '';

        $.each(sections, function (i, section) {
            html += '<li><input type="text" class="swp-name" data-name="' + section.toLowerCase() + '" value="' + section + '">' +
                '<span class="sitemap-down" title="move down"></span><span class="sitemap-up" title="move up"></span>' +
                '<input type="hidden" name="simple_wp_' + section.toLowerCase() + '_n" value="' + (i + 1) + '"></li>';
        });
        orderList.html(html);
    };


    form.tabs({
        active: /\d/.test(tab) ? parseInt(tab) - 1 : 0
    });

    form.on('submit', submitForm);
    orderList.on('click', changeOrderItem);
    $('#sitemap-defaults').on('click', restoreDefaultOrder);
});
