</main>
<footer class="pie text-center py-3">
    <?= e(APP_NAME) ?> · Escuintla, Guatemala
</footer>
<script src="<?= e(asset('vendor/bootstrap/js/bootstrap.bundle.min.js')) ?>"></script>
<script src="<?= e(asset('js/app.js')) ?>"></script>
<?php foreach (($scripts ?? []) as $script): ?>
    <script src="<?= e(asset($script)) ?>" defer></script>
<?php endforeach; ?>
</body>
</html>