<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
// Dismiss alerts
document.querySelectorAll('.alert').forEach(a => {
    setTimeout(() => { try { bootstrap.Alert.getOrCreateInstance(a)?.close(); } catch(e){} }, 5000);
});
</script>
<?= $extraScripts ?? '' ?>
</body>
</html>
