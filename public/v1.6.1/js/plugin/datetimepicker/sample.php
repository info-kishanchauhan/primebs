<!-- plugins for Bootstrap dateTime selection -->
<script src="./asset_1_5_1/datetimepicker/moment.js"></script>
<script src="./asset_1_5_1/datetimepicker/transition.js"></script>
<script src="./asset_1_5_1/datetimepicker/collapse.js"></script>
<script src="./asset_1_5_1/datetimepicker/bootstrap-datetimepicker.js"></script>

<script>

$('#txtFrom').datetimepicker({
format : 'YYYY-MM-DD HH:mm'
});

</script>


<section class="col col-2">
    <label class="label"><strong class="txt-color-blue">From</strong></label>
    <label class="input"> <i class="icon-append fa fa-calendar"></i>
        <input type="text" id="txtFrom" name="txtFrom" class="">
    </label>

</section>