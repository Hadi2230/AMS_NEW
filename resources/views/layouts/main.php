<!DOCTYPE html>
<html dir="rtl" lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
    <title><?php echo $title ?? 'سامانه مدیریت اعلا نیرو'; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Vazirmatn Font -->
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/css/app.css">
    
    <!-- Page specific CSS -->
    <?php if (isset($pageCss)): ?>
        <?php foreach ($pageCss as $css): ?>
            <link rel="stylesheet" href="<?php echo $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body class="<?php echo isset($_COOKIE['theme']) && $_COOKIE['theme']==='dark' ? 'dark-mode' : ''; ?>">
    
    <!-- Navigation -->
    <?php include __DIR__ . '/partials/navbar.php'; ?>
    
    <!-- Main Content -->
    <main class="main-content">
        <?php if (isset($content)): ?>
            <?php echo $content; ?>
        <?php endif; ?>
    </main>
    
    <!-- Footer -->
    <?php include __DIR__ . '/partials/footer.php'; ?>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="/js/app.js"></script>
    
    <!-- Page specific JS -->
    <?php if (isset($pageJs)): ?>
        <?php foreach ($pageJs as $js): ?>
            <script src="<?php echo $js; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Flash Messages -->
    <?php if (isset($_SESSION['flash_messages'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                <?php foreach ($_SESSION['flash_messages'] as $type => $message): ?>
                    showNotification('<?php echo $message; ?>', '<?php echo $type; ?>');
                <?php endforeach; ?>
            });
        </script>
        <?php unset($_SESSION['flash_messages']); ?>
    <?php endif; ?>
</body>
</html>