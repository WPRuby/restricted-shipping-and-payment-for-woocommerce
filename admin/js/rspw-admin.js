(function( $ ) {
	'use strict';

    // shipping methods
	$(function() {
        get_operators_based_on_rule_type();
        $(document).on('cmb2_add_row', function (e, x, row) {
            get_operators_based_on_rule_type();
            $('#shipping_condition_rules_'+ row.idNumber +'_rule_type').trigger('change');

        });
        $('#shipping_conditions_metabox .rule_type .cmb2_select').trigger('change');
    });

	function get_operators_based_on_rule_type(){

        $('#shipping_conditions_metabox .rule_type .cmb2_select').change(function(){
            let id = $(this).attr('id');
            let iterator = $(this).attr('data-iterator');
            let index = 0;
            if(typeof iterator === 'undefined'){
                 index = id.replace('shipping_condition_rules_', '').replace('_rule_type', '');
            }else{
                 index = iterator;
            }
            let group_row_selector = $('.cmb-repeatable-grouping[data-iterator='+index+']');
            let operator_select = group_row_selector.find('#shipping_condition_rules_'+ index +'_operator');
            let rule_type = $(this).val();
            let postID = $('#post_ID').val();
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {
                    rule_type: rule_type,
                    index: index,
                    postID: postID,
                    action: 'get_shipping_rule_type_operators'
                },
                success: function (response) {
                    $(operator_select).html(response);
                }
            });
            group_row_selector.find('.value_field').hide();
            group_row_selector.find('.' + rule_type).show();




        });
	}
    // for payment gateways
    $(function() {
        get_payment_operators_based_on_rule_type();
        $(document).on('cmb2_add_row', function (e, x, row) {
            get_payment_operators_based_on_rule_type();
            $('#payment_condition_rules_'+ row.idNumber +'_rule_type').trigger('change');
        });
        $('#payment_conditions_metabox .rule_type .cmb2_select').trigger('change');
    });

    function get_payment_operators_based_on_rule_type(){

        $('#payment_conditions_metabox .rule_type .cmb2_select').change(function(){
            let id = $(this).attr('id');
            let iterator = $(this).attr('data-iterator');
            let index = 0;
            if(typeof iterator === 'undefined'){
                index = id.replace('payment_condition_rules_', '').replace('_rule_type', '');
            }else{
                index = iterator;
            }
            let group_row_selector = $('.cmb-repeatable-grouping[data-iterator='+index+']');
            let operator_select = group_row_selector.find('#payment_condition_rules_'+ index +'_operator');
            let rule_type = $(this).val();
            let postID = $('#post_ID').val();
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {
                    rule_type: rule_type,
                    index: index,
                    postID: postID,
                    action: 'get_payment_rule_type_operators'
                },
                success: function (response) {
                    $(operator_select).html(response);
                }
            });
            group_row_selector.find('.value_field').hide();
            group_row_selector.find('.' + rule_type).show();

        });
    }


})( jQuery );
