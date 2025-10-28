<!-- Contact Us Start -->
<div class="container-fluid py-5">
    <div class="container py-5">
        <div class="bg-light rounded p-5">
            <div class="row g-4">
                <!-- Columna izquierda: info + mapa -->
                <div class="col-lg-5">
                    <div>
                        <h1 class="mb-4">Contáctanos</h1>
                        <p class="mb-4">
                            <?= htmlspecialchars($sys['business_description'] ?? 'Si necesitas publicidad, información sobre nuestros servicios o simplemente quieres comunicarte con nosotros, déjanos tu mensaje y te responderemos lo antes posible.') ?>
                        </p>

                        <?php if (!empty($sys['business_map'])): ?>
                        <div class="rounded overflow-hidden">
                            <?= $sys['business_map'] ?>
                        </div>
                        <?php else: ?>
                        <div class="rounded">
                            <iframe class="rounded w-100"
                                style="height: 425px;"
                                src="https://www.google.com/maps?q=Colombia&output=embed"
                                loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade">
                            </iframe>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Columna derecha: formulario + contactos -->
                <div class="col-lg-7">
                    <form id="contactForm" method="post" action="enviar_contacto.php" class="mb-4">
                        <div class="row g-4">
                            <div class="col-lg-6">
                                <input type="text" name="name" class="w-100 form-control border-0 py-3" placeholder="Tu nombre" required>
                            </div>
                            <div class="col-lg-6">
                                <input type="email" name="email" class="w-100 form-control border-0 py-3" placeholder="Tu correo electrónico" required>
                            </div>
                            <div class="col-lg-6">
                                <input type="text" name="phone" class="w-100 form-control border-0 py-3" placeholder="Tu teléfono">
                            </div>
                            <div class="col-lg-6">
                                <input type="text" name="subject" class="w-100 form-control border-0 py-3" placeholder="Asunto">
                            </div>
                            <div class="col-12">
                                <textarea name="message" class="w-100 form-control border-0" rows="6" placeholder="Tu mensaje"></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="w-100 btn btn-primary form-control py-3">Enviar mensaje</button>
                            </div>
                        </div>
                    </form>

                    <div class="row g-4">
                        <?php if (!empty($sys['business_address'])): ?>
                        <div class="col-xl-6">
                            <div class="d-flex p-4 rounded bg-white">
                                <i class="fas fa-map-marker-alt fa-2x text-primary me-4"></i>
                                <div>
                                    <h4>Dirección</h4>
                                    <p class="mb-0"><?= htmlspecialchars($sys['business_address']) ?></p>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($sys['site_email'])): ?>
                        <div class="col-xl-6">
                            <div class="d-flex p-4 rounded bg-white">
                                <i class="fas fa-envelope fa-2x text-primary me-4"></i>
                                <div>
                                    <h4>Correo</h4>
                                    <p class="mb-0"><?= htmlspecialchars($sys['site_email']) ?></p>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($sys['business_phone'])): ?>
                        <div class="col-xl-6">
                            <div class="d-flex p-4 rounded bg-white">
                                <i class="fa fa-phone-alt fa-2x text-primary me-4"></i>
                                <div>
                                    <h4>Teléfono</h4>
                                    <p class="mb-0"><?= htmlspecialchars($sys['business_phone']) ?></p>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="col-xl-6">
                            <div class="d-flex p-4 rounded bg-white">
                                <i class="fa fa-share-alt fa-2x text-primary me-4"></i>
                                <div>
                                    <h4>Síguenos</h4>
                                    <div class="d-flex">
                                        <?php if (!empty($sys['twitter'])): ?>
                                            <a class="me-3" href="<?= htmlspecialchars($sys['twitter']) ?>" target="_blank"><i class="fab fa-twitter text-dark link-hover"></i></a>
                                        <?php endif; ?>
                                        <?php if (!empty($sys['facebook'])): ?>
                                            <a class="me-3" href="<?= htmlspecialchars($sys['facebook']) ?>" target="_blank"><i class="fab fa-facebook-f text-dark link-hover"></i></a>
                                        <?php endif; ?>
                                        <?php if (!empty($sys['youtube'])): ?>
                                            <a class="me-3" href="<?= htmlspecialchars($sys['youtube']) ?>" target="_blank"><i class="fab fa-youtube text-dark link-hover"></i></a>
                                        <?php endif; ?>
                                        <?php if (!empty($sys['instagram'])): ?>
                                            <a class="me-3" href="<?= htmlspecialchars($sys['instagram']) ?>" target="_blank"><i class="fab fa-instagram text-dark link-hover"></i></a>
                                        <?php endif; ?>
                                        <?php if (!empty($sys['tiktok'])): ?>
                                            <a class="" href="<?= htmlspecialchars($sys['tiktok']) ?>" target="_blank"><i class="fab fa-tiktok text-dark link-hover"></i></a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div> <!-- row g-4 -->
                </div> <!-- col-lg-7 -->
            </div> <!-- row g-4 -->
        </div> <!-- bg-light -->
    </div> <!-- container -->
</div>
<!-- Contact Us End -->
   