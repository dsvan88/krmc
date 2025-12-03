<? 
if (empty($id))         $id = mt_rand(1000, 10000);
if (!empty($prefix))    $id = $prefix.'_'.$id;
if (empty($checked))    $checked = '';
if (empty($label))      $label = '';
if (empty($title))      $title = '';

if (empty($icon))
    $icon = '';
else {
    if (substr($icon, 0, 3) === 'fa-')
        $class = 'fa '.$icon;
    else
        $label = $icon;
}
    
?>

<span class="cb">
    <input type="checkbox" id="<?= $id ?>" name="<?= $name ?>" value="<?= $value ?>" class="cb__checkbox" <?= $checked ?> />
    <label for="<?= $id ?>" class="cb__label <?= $class ?>" <?= empty($title) ? '' : "title='$title'" ?>> <?= $label ?> </label>
</span>