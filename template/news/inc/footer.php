</main>
<!-- Footer Start -->
    <div class="container-fluid bg-light pt-5 px-sm-3 px-md-5">
        <div class="row">
            <div class="col-lg-3 col-md-6 mb-5">
                <a href="index.html" class="navbar-brand">
                    <img src="<?php echo URLBASE; ?><?php echo SITE_LOGO; ?>?<?php echo time()?>" alt="Logo" width="150px">
                </a>
                <p><?= htmlspecialchars($sys['info_footer']) ?></p>
                <div class="d-flex justify-content-start mt-4">
					
					<?php if (!empty($sys['twitter'])): ?>
                    <a class="btn btn-outline-secondary text-center mr-2 px-0" style="width: 38px; height: 38px;" href="<?= htmlspecialchars($sys['twitter']) ?>" target="_blank"><i class="fa-brands fa-x-twitter"></i></a>
					<?php endif; ?>
					
					<?php if (!empty($sys['facebook'])): ?>
                    <a class="btn btn-outline-secondary text-center mr-2 px-0" style="width: 38px; height: 38px;" href="<?= htmlspecialchars($sys['facebook']) ?>" target="_blank"><i class="fa-brands fa-facebook-f"></i></a>
					<?php endif; ?>
					
					<?php if (!empty($sys['instagram'])): ?>
                    <a class="btn btn-outline-secondary text-center mr-2 px-0" style="width: 38px; height: 38px;" href="<?= htmlspecialchars($sys['instagram']) ?>" target="_blank"><i class="fa-brands fa-instagram"></i></a>
					<?php endif; ?>
					
					<?php if (!empty($sys['tiktok'])): ?>
                    <a class="btn btn-outline-secondary text-center mr-2 px-0" style="width: 38px; height: 38px;" href="<?= htmlspecialchars($sys['tiktok']) ?>" target="_blank"><i class="fa-brands fa-tiktok"></i></a>
					<?php endif; ?>
					
					<?php if (!empty($sys['youtube'])): ?>
                    <a class="btn btn-outline-secondary text-center mr-2 px-0" style="width: 38px; height: 38px;" href="<?= htmlspecialchars($sys['youtube']) ?>" target="_blank"><i class="fa-brands fa-youtube"></i></a>
					<?php endif; ?>
					
					<?php if (!empty($sys['whatsapp'])): ?>
                    <a class="btn btn-outline-secondary text-center mr-2 px-0" style="width: 38px; height: 38px;" href="<?= htmlspecialchars($sys['whatsapp']) ?>" target="_blank"><i class="fa-brands fa-whatsapp"></i></a>
					<?php endif; ?>
					
                </div>
            </div>
            <?php include __DIR__ . '/../partials/footer-categories.php'; ?>
            <?php include __DIR__ . '/../partials/tags.php'; ?>
			
			
			
            
			
			
            <div class="col-lg-3 col-md-6 mb-5">
                <h4 class="font-weight-bold mb-4">Links Rápidos</h4>

                <div class="d-flex flex-column justify-content-start">
                    <a class="text-secondary mb-2" href="<?= URLBASE ?>/about-us"><i class="fa fa-angle-right text-dark mr-2"></i>Nosotros</a>
                    <a class="text-secondary mb-2" href="<?= URLBASE ?>/privacy-policy"><i class="fa fa-angle-right text-dark mr-2"></i>Politica de Privacidad</a>
                   
                    <a class="text-secondary mb-2" href="<?= URLBASE ?>/terms-and-conditions"><i class="fa fa-angle-right text-dark mr-2"></i>Términos y Condiciones</a>
                    <a class="text-secondary" href="<?= URLBASE ?>/contact"><i class="fa fa-angle-right text-dark mr-2"></i>Contacto</a>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid py-4 px-sm-3 px-md-5">
        <p class="m-0 text-center">
            &copy;<?php echo date('Y');?> <a class="font-weight-bold" href="#"><?= NOMBRE_SITIO; ?></a>. Todos los derechos reservados. <br>
			
			<!--/*** This template is free as long as you keep the footer author’s credit link/attribution link/backlink. If you'd like to use the template without the footer author’s credit link/attribution link/backlink, you can purchase the Credit Removal License from "https://htmlcodex.com/credit-removal". Thank you for your support. ***/-->
			Hosting & Diseño <a class="font-weight-bold" href="https://www.intermediahost.co">Intermedia Host</a>
        </p>
    </div>
    <!-- Footer End -->


    <!-- Back to Top -->
    <a href="#" class="btn btn-dark back-to-top"><i class="fa fa-angle-up"></i></a>


    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo URLBASE; ?>/template/news/lib/easing/easing.min.js"></script>
    <script src="<?php echo URLBASE; ?>/template/news/lib/owlcarousel/owl.carousel.min.js"></script>

    <!-- Contact Javascript File -->
    <script src="<?php echo URLBASE; ?>/template/news/mail/jqBootstrapValidation.min.js"></script>
    <script src="<?php echo URLBASE; ?>/template/news/mail/contact.js"></script>

    <!-- Template Javascript -->
    <script src="<?php echo URLBASE; ?>/template/news/js/main.js?<?php echo time();?>"></script>


<?php if (!empty($sys['code_player'])): ?>
<style>
p.m-0.text-center {
    padding-bottom: <?= $sys['player_height'] + 10 ?? 70 ?>px!important;
}
</style>
<script>
const direccionURL1 = `
  <div style="bottom: 0;display: flex;height: <?= $sys['player_height'] ?? 70 ?>px;left: 0;position: fixed;right: 0;width: 100%;z-index: 1500;overflow: hidden;"><iframe src="<?= $sys['code_player'] ?? '' ?>" frameborder="0" scrolling="no" style="width: 100%;"></iframe></div>
`;
</script>
<script src="<?= URLBASE ?>/inc/core/js/navegacion.js?<?= time(); ?>"></script>
<?php endif; ?>

<?= $sys['code_footer'] ?>
</body>

</html>