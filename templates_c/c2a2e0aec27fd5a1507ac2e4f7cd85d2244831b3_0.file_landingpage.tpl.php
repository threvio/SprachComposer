<?php
/* Smarty version 5.8.0, created on 2026-09-11 10:21:28
  from 'file:landingpage.tpl' */

/* @var \Smarty\Template $_smarty_tpl */
if ($_smarty_tpl->getCompiled()->isFresh($_smarty_tpl, array (
  'version' => '5.8.0',
  'unifunc' => 'content_6aa3ba08447f45_38623298',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'c2a2e0aec27fd5a1507ac2e4f7cd85d2244831b3' => 
    array (
      0 => 'landingpage.tpl',
      1 => 1789113256,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
))) {
function content_6aa3ba08447f45_38623298 (\Smarty\Template $_smarty_tpl) {
$_smarty_current_dir = 'C:\\xampp\\htdocs\\SprachComposer\\templates';
?><!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars((string)$_smarty_tpl->getValue('courseTitle'), ENT_QUOTES, 'UTF-8', true);?>
</title>
    <style>
        :root {
            --primary: <?php echo htmlspecialchars((string)$_smarty_tpl->getValue('primaryColor'), ENT_QUOTES, 'UTF-8', true);?>
;
            --bg-tint: color-mix(in srgb, var(--primary) 6%, #f4f7f6);
            --shadow-tint: color-mix(in srgb, var(--primary) 20%, transparent);
        }
        body { font-family: "Segoe UI", Arial, sans-serif; background-color: var(--bg-tint); display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; padding: 20px; box-sizing: border-box; }
        .container { background: white; padding: 50px; border-radius: 16px; box-shadow: 0 15px 40px var(--shadow-tint); max-width: 650px; width: 100%; text-align: center; }
        .container.split-layout { display: flex; flex-direction: row; align-items: center; max-width: 850px; padding: 40px; text-align: left; gap: 40px; }
        .split-layout .logo-wrapper { flex: 0 0 35%; display: flex; justify-content: center; }
        .split-layout .content-wrapper { flex: 0 0 calc(65% - 40px); }
        .split-layout .btn-group { justify-content: flex-start; }
        h1 { color: var(--primary); margin-top: 0; margin-bottom: 8px; font-size: 32px; font-weight: 800; letter-spacing: -0.5px; }
        .subtitle { color: #666666; margin-bottom: 35px; font-size: 16px; font-style: italic; font-weight: 500; letter-spacing: -0.5px; }
        .btn-group { display: flex; justify-content: center; gap: 20px; flex-wrap: wrap; }
        .btn { position: relative; display: flex; justify-content: center; align-items: center; width: 130px; height: 90px; text-decoration: none; color: white; border-radius: 12px; font-weight: 800; font-size: 32px; text-shadow: 0 2px 5px rgba(0,0,0,0.8); transition: transform 0.2s, box-shadow 0.2s; overflow: hidden; box-shadow: 0 5px 15px var(--shadow-tint); }
        .btn:hover { transform: translateY(-4px) scale(1.03); box-shadow: 0 12px 25px var(--shadow-tint); }
        .logo-img { max-width: 100%; max-height: 120px; border-radius: 8px; object-fit: contain; }
        @media (max-width: 650px) { .container.split-layout { flex-direction: column; text-align: center; gap: 20px; } .split-layout .btn-group { justify-content: center; } .split-layout .logo-wrapper, .split-layout .content-wrapper { flex: 1 1 100%; } }
    </style>
</head>
<body>
<div class="<?php echo htmlspecialchars((string)$_smarty_tpl->getValue('containerClass'), ENT_QUOTES, 'UTF-8', true);?>
">
    
    <?php if ($_smarty_tpl->getValue('layoutStyle') === 'layout2') {?>
        <div class="logo-wrapper">
            <?php if ($_smarty_tpl->getValue('logoFilename')) {?>
                <img src="<?php echo htmlspecialchars((string)$_smarty_tpl->getValue('logoFilename'), ENT_QUOTES, 'UTF-8', true);?>
" alt="Kunden Logo" class="logo-img">
            <?php }?>
        </div>
        <div class="content-wrapper">
    <?php } else { ?>
        <?php if ($_smarty_tpl->getValue('logoFilename')) {?>
            <img src="<?php echo htmlspecialchars((string)$_smarty_tpl->getValue('logoFilename'), ENT_QUOTES, 'UTF-8', true);?>
" alt="Kunden Logo" class="logo-img" style="margin-bottom: 25px;">
        <?php }?>
    <?php }?>

    <h1><?php echo htmlspecialchars((string)$_smarty_tpl->getValue('courseTitle'), ENT_QUOTES, 'UTF-8', true);?>
</h1>
    <p class="subtitle">Bitte wählen Sie Ihre Sprache / Please select your language:</p>
    
    <div class="btn-group">
        <!-- Smarty Schleife für die Sprach-Buttons -->
        <?php
$_from = $_smarty_tpl->getSmarty()->getRuntime('Foreach')->init($_smarty_tpl, $_smarty_tpl->getValue('languageLinks'), 'lang');
$foreach0DoElse = true;
foreach ($_from ?? [] as $_smarty_tpl->getVariable('lang')->value) {
$foreach0DoElse = false;
?>
            <a href="<?php echo htmlspecialchars((string)$_smarty_tpl->getValue('lang')['url'], ENT_QUOTES, 'UTF-8', true);?>
" class="btn" style="<?php echo $_smarty_tpl->getValue('lang')['bgStyle'];?>
"><?php echo htmlspecialchars((string)$_smarty_tpl->getValue('lang')['name'], ENT_QUOTES, 'UTF-8', true);?>
</a>
        <?php
}
$_smarty_tpl->getSmarty()->getRuntime('Foreach')->restore($_smarty_tpl, 1);?>
    </div>

    <?php if ($_smarty_tpl->getValue('layoutStyle') === 'layout2') {?>
        </div>
    <?php }?>

</div>
</body>
</html><?php }
}
