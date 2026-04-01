<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(($pageTitle ?? 'System') . ' - ' . config('app.name')) ?></title>
    <link rel="stylesheet" href="<?= e(asset('css/app.css?v=1.0.2')) ?>">
</head>
<body class="guest-body">
    <?= $content ?>
</body>
</html>
