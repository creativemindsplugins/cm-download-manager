<?php
$filter_request = $_GET;

$search = esc_attr($filter_request['cmdm_search'] ?? '');
$date_from = esc_attr($filter_request['date_from'] ?? '');
$date_to = esc_attr($filter_request['date_to'] ?? '');
?>

<form action="<?php echo esc_attr($pagination['baseUrl'] ?? ''); ?>" method="GET"
      class="cmdm-dasboard-search-extended-form admin-downloads-filter">
    <div class="fields">
        <div class="field-wrapper">
            <label class="field-name" for="cmdm_search">
                <?php echo CMDM_Labels::getLocalized('filter_search_by_title'); ?>
            </label>
            <input id="cmdm_search"
                   class="field-value" type="text" name="cmdm_search"
                   value="<?php echo $search; ?>"
                   placeholder=""/>
        </div>

        <div class="field-wrapper date-range-wrapper">
            <label class="field-name">
                <?php echo CMDM_Labels::getLocalized('filter_search_by_date_range'); ?>
            </label>
            <div class="field-value">
                <input class="field-value" id="from" name="date_from" value="<?php echo $date_from; ?>"
                       placeholder="<?php echo CMDM_Labels::getLocalized('filter_search_by_date_from'); ?>" autocomplete="off">
                <span>-</span>
                <input class="field-value" id="to" name="date_to" value="<?php echo $date_to; ?>"
                       placeholder="<?php echo CMDM_Labels::getLocalized('filter_search_by_date_to'); ?>" autocomplete="off">
            </div>
        </div>
    </div>
    <div class="submit-wrapper">
        <div></div>
        <button class="cmdm-button" type="submit">
            <?php echo CMDM_Labels::getLocalized('filter_submit_button'); ?>
        </button>
    </div>
</form>

<script>
    (function ($) {
        $(document).ready(function () {
            let dateFormat = "mm/dd/yy",
                from = $("[name='date_from']")
                    .datepicker({
                        defaultDate: "+1w",
                        changeMonth: true,
                        numberOfMonths: 1,
                        dateFormat: dateFormat
                    })
                    ,
                to = $("[name='date_to']").datepicker({
                    defaultDate: "+1w",
                    changeMonth: true,
                    numberOfMonths: 1,
                    dateFormat: dateFormat
                })


            from.on("change", function () {
                to.datepicker("option", "minDate", getDate(this));
            });
            to.on("change", function () {
                from.datepicker("option", "maxDate", getDate(this));
            });

            function getDate(element) {
                let date;
                try {
                    date = $.datepicker.parseDate(dateFormat, element.value);
                } catch (error) {
                    date = null;
                }
console.log(element.value);
                return date;
            }
        });

    })(jQuery);
</script>
