<!-- Footer Start -->
    <div class="container-fluid bg-light pt-5 px-sm-3 px-md-5">
        <div class="row">
            <div class="col-lg-3 col-md-6 mb-5">
                <a href="index.html" class="navbar-brand">
                    <img src="<?php echo URLBASE; ?><?php echo SITE_LOGO; ?>?<?php echo time()?>" alt="Logo" width="150px">
                </a>
                <p>Volup amet magna clita tempor. Tempor sea eos vero ipsum. Lorem lorem sit sed elitr sed kasd et</p>
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
			
            <div class="col-lg-3 col-md-6 mb-5">
                <h4 class="font-weight-bold mb-4">Tags</h4>
                <div class="d-flex flex-wrap m-n1">
                    <a href="" class="btn btn-sm btn-outline-secondary m-1">Politics</a>
                    <a href="" class="btn btn-sm btn-outline-secondary m-1">Business</a>
                    <a href="" class="btn btn-sm btn-outline-secondary m-1">Corporate</a>
                    <a href="" class="btn btn-sm btn-outline-secondary m-1">Sports</a>
                    <a href="" class="btn btn-sm btn-outline-secondary m-1">Health</a>
                    <a href="" class="btn btn-sm btn-outline-secondary m-1">Education</a>
                    <a href="" class="btn btn-sm btn-outline-secondary m-1">Science</a>
                    <a href="" class="btn btn-sm btn-outline-secondary m-1">Technology</a>
                    <a href="" class="btn btn-sm btn-outline-secondary m-1">Foods</a>
                    <a href="" class="btn btn-sm btn-outline-secondary m-1">Entertainment</a>
                    <a href="" class="btn btn-sm btn-outline-secondary m-1">Travel</a>
                    <a href="" class="btn btn-sm btn-outline-secondary m-1">Lifestyle</a>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-5">
                <h4 class="font-weight-bold mb-4">Quick Links</h4>

                <div class="d-flex flex-column justify-content-start">
                    <a class="text-secondary mb-2" href="#"><i class="fa fa-angle-right text-dark mr-2"></i>About</a>
                    <a class="text-secondary mb-2" href="#"><i class="fa fa-angle-right text-dark mr-2"></i>Advertise</a>
                    <a class="text-secondary mb-2" href="#"><i class="fa fa-angle-right text-dark mr-2"></i>Privacy & policy</a>
                    <a class="text-secondary mb-2" href="#"><i class="fa fa-angle-right text-dark mr-2"></i>Terms & conditions</a>
                    <a class="text-secondary" href="#"><i class="fa fa-angle-right text-dark mr-2"></i>Contact</a>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid py-4 px-sm-3 px-md-5">
        <p class="m-0 text-center">
            &copy; <a class="font-weight-bold" href="#"><?= NOMBRE_SITIO; ?></a>. All Rights Reserved. 
			
			<!--/*** This template is free as long as you keep the footer author’s credit link/attribution link/backlink. If you'd like to use the template without the footer author’s credit link/attribution link/backlink, you can purchase the Credit Removal License from "https://htmlcodex.com/credit-removal". Thank you for your support. ***/-->
			Designed by <a class="font-weight-bold" href="https://htmlcodex.com">HTML Codex</a>
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
</body>

</html>