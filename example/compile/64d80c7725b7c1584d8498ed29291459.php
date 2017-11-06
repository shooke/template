<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $title; ?></title>
</head>
<body>

<div>
布局中的内容
默认内容
</div>
    <hr>
<div>
    自身内容
    <?php echo $content; ?>
</div>
    <hr>
<div>判断
<?php if($title) { ?>1>
    <?php echo $title; ?>
<?php } else { ?>
    没有标题
<?php } ?>
</div>
    <hr>
<div>
    <?php foreach($array as $item) { ?>
        <?php echo $item; ?>
    <?php } ?>
</div>



     <div>
     内容页
    <?php echo $this->renderPartial('footer'); ?>
     </div>

<div>
    布局载入
    <?php echo $this->renderPartial('footer'); ?>
</div>

</body>
</html>