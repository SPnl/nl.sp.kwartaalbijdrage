<div class="crm-block crm-form-block crm-kwartaalbijdragen-form-block">

    <div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="top"}
    </div>

    <div class="crm-section">
        <div class="label">{$form.basisbedrag.label}</div>
        <div class="content">{$form.basisbedrag.html} <em>Per kwartaal</em></div>
        <div class="clear"></div>
    </div>

    <div class="crm-section">
        <div class="label">{$form.ledenvergoeding.label}</div>
        <div class="content">{$form.ledenvergoeding.html} <em>Per lid</em></div>
        <div class="clear"></div>
    </div>

    <div class="crm-section">
        <div class="label">{$form.tribunevergoeding.label}</div>
        <div class="content">{$form.tribunevergoeding.html} <em>Per verzonden/bezorgde tribune</em></div>
        <div class="clear"></div>
    </div>

    {* FOOTER *}
    <div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
    </div>

</div>