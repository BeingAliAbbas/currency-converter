<?php 
$currency = $_SESSION['currency'];

if ($currency == 1) {
  $sign_convert = "$";
} else {
  $sign_convert = "₹";
}
?>

<label><?=lang("order_service")?></label>
<select name="service_id" class="form-control square ajaxChangeService" data-url="<?=cn($module."/order/get_service/")?>">
  <option> <?=lang("choose_a_service")?></option>
  <?php
    if (!empty($services)) {
      $service_item_default = $services[0];
      $currency_symbol      = get_option('currency_symbol', "$");
      $decimal_places       = get_option('currency_decimal', 2);
      foreach ($services as $key => $service) {
        $price_per_1k = $service->price;
        
        if ($currency == 1) {
          $price_show = $price_per_1k / 75;
        } else {
          $price_show = $price_per_1k;
        }
    ?>

        <option value="<?= $service->id ?>" data-type="<?= $service->type ?>" data-dripfeed="<?= $service->dripfeed ?>"><?= $service->name ?> &ndash; <?php echo $sign_convert; ?><?= (!empty($price_show)) ? currency_format($price_show, get_option('currency_decimal', 2), $decimalpoint, $separator) : 0 ?> </option>
  <?php }}?>
</select>
