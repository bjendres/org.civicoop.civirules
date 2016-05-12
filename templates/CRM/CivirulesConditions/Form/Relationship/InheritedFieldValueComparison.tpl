<h3>{$ruleConditionHeader}</h3>
<div class="crm-block crm-form-block crm-civirule-rule_condition-block-field-value-comparison">
    <div class="crm-section">
        <div class="label">{$form.mode.label}</div>
        <div class="content">{$form.mode.html}</div>
        <div class="clear"></div>
    </div>
    <div class="crm-section">
        <div class="label">{$form.field.label}</div>
        <div class="content">{$form.field.html}</div>
        <div class="clear"></div>
    </div>
    <div class="crm-section">
        <div class="label">{$form.operator.label}</div>
        <div class="content">{$form.operator.html}</div>
        <div class="clear"></div>
    </div>
    <div class="crm-section" id="value_parent">
        <div class="label">{$form.value.label}</div>
        <div class="content">
            {$form.value.html}
            <select id="value_options" class="hiddenElement">

            </select>
        </div>
        <div class="clear"></div>
    </div>
    <div class="crm-section" id="multi_value_parent">
        <div class="label">{$form.multi_value.label}</div>
        <div class="content textarea">
            {$form.multi_value.html}
            <p class="description">{ts}Seperate each value on a new line{/ts}</p>
        </div>
        <div id="multi_value_options" class="hiddenElement content">

        </div>
        <div class="clear"></div>
    </div>
</div>
<div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>
{include file="CRM/CivirulesConditions/Form/ValueComparisonJs.tpl"}

{literal}
<script type="text/javascript">
    cj(function() {
        cj('#field').change(function() {
            var entity = 'Contact';
            var field = cj('#field').val();
            var field = field.replace(entity+'_', "");
            retrieveOptionsForEntityAndField(entity, field);
            cj('#operator').trigger('change');
        });

    });

    function retrieveOptionsForEntityAndField(entity, field) {
        var options = new Array();
        var multiple = false;
        CRM_civirules_conidtion_form_updateOptionValues(options, multiple);
        if (field.indexOf('custom_') == 0) {
            var custom_field_id = field.replace('custom_', '');
            CRM.api3('CustomField', 'getsingle', {'sequential': 1, 'id': custom_field_id}, true)
            .done(function(data) {
                switch(data.html_type) {
                    {/literal}
                        {foreach from=$custom_field_multi_select_html_types item=custom_field_multi_select_html_type}
                    case '{$custom_field_multi_select_html_type}':
                        {/foreach}
                    {literal}
                        multiple = true;
                        CRM_civirules_conidtion_form_updateOptionValues(options, multiple);
                        break;
                }
            });
        }
        CRM.api3(entity, 'getoptions', {'sequential': 1, 'field': field}, false)
        .done(function (data) {
            if (data.values) {
                options = data.values;
            }
            CRM_civirules_conidtion_form_updateOptionValues(options, multiple);
        });
    }
</script>
{/literal}