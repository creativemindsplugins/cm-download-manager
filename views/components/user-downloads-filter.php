<?php
$filter_request = $_GET;
$search = esc_attr($filter_request['cmdm_search'] ?? '');
?>

<form action="<?php echo esc_attr($pagination['baseUrl'] ?? ''); ?>" method="GET"
      class="cmdm-dasboard-search-form user-downloads-filter">
    <div class="field-wrapper">
        <label class="field-name" for="cmdm_search"></label>
        <input id="cmdm_search"
               class="field-value" type="text" name="cmdm_search"
               value="<?php echo $search; ?>"
               placeholder="Search..."/>
    </div>
</form>
