<h1 style="margin: 0 0 10px 0; font-size: 16px;padding: 0;">Добавлен новый комментарий на сайте <?php echo $data['site_name']?></h1>
<p style="padding: 0;margin: 10px 0;font-size: 12px;">
    Пользователь <strong><?php echo $data['name'] ?></strong> оставил комментарий:
</p>
<div style="margin: 0;padding: 10px;background: #FFF5B5; font-weight: bold;">
    <?php echo $data['message'] ?>
</div>
<p style="padding: 0;margin: 10px 0;font-size: 12px;">
  Объект комментирования: <?php echo $data['item'] ?>
<p style="padding: 0;margin: 10px 0;font-size: 12px;">
  Email пользователя: <?php echo $data['email'] ?>
</p>