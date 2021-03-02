</main>
<footer class="container-fluid">
    <div class="row">
        <div class="col bg-dark text-light text-center py-3 w-100">
            &copy;<?php echo date('Y') ?> - ROOM - Tout droits réservés <a href="<?php echo URL ?>cgv.php">CGV</a> <a href="<?php echo URL ?>mentionsLegale.php">Mentions Légales</a>

        </div>
    </div>
</footer>
<?php if (!isset($_COOKIE['acceptcookies'])) : ?>
    <div id="bandeaucookies" class="bg-primary w-100 text-center py-3 text-light">
        Ce site utilise des cookies afin d'améliorer votre confort de navigation sur notre site. Consultez notre politique de confidentialité. <a href="#" id="confirmcookies" class="btn btn-info"> J'ai compris</a>
    </div>
<?php endif; ?>
<!-- Gestion des dates simplifié en js avec dayjs -->
<script src="<?php echo URL ?>node_modules/dayjs/dayjs.min.js"></script>
<script src="<?php echo URL ?>node_modules/dayjs/plugin/customParseFormat"></script>
<script>
    dayjs.extend(window.dayjs_plugin_customParseFormat);
</script>
<!-- Appel du datepicker js -->
<script type="module" src="<?php echo URL ?>inc/js/datepicker.js"></script>
<!-- Appel du timepicker -->
<script src="<?php echo URL ?>node_modules/pickerjs/dist/picker.js"></script>
<script type="module" src="<?php echo URL ?>inc/js/ajax.js"></script>
<!-- JS pour les lightbox -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/ekko-lightbox/5.3.0/ekko-lightbox.min.js" integrity="sha512-Y2IiVZeaBwXG1wSV7f13plqlmFOx8MdjuHyYFVoYzhyRr3nH/NMDjTBSswijzADdNzMyWNetbLMfOpIPl6Cv9g==" crossorigin="anonymous"></script>
<!-- Js animation -->
<script src="<?php echo URL ?>inc/js/animation.js"></script>

</body>

</html>