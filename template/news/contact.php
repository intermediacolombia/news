<?php
// =======================
// Variables SEO dinámicas
// =======================
$page_title       = "Contactanos |" . NOMBRE_SITIO;
$page_description = "Formulario de contacto"  . NOMBRE_SITIO;
$page_keywords    = NOMBRE_SITIO . ", contacto, " . NOMBRE_SITIO;
$page_author      = NOMBRE_SITIO;

// Imagen SEO → primera del producto o logo por defecto
$page_image = rtrim(URLBASE, '/') .FAVICON;
if (!empty($images)) {
    $path = $images[0]['path'];
    $path = ($path[0] === '/') ? $path : '/' . $path;
    $page_image = rtrim(URLBASE, '/') . $path;
}

// Canonical automático (desde URL actual)
$currentPath    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$page_canonical = rtrim(URLBASE, '/') . '/' . ltrim($currentPath, '/');

// =======================
// Fin SEO
// =======================

?>


    <!-- Breadcrumb Start -->
    <div class="container-fluid">
        <div class="container-bk">
            <nav class="breadcrumb bg-transparent m-0 p-0">
                <a class="breadcrumb-item" href="/">Home</a>
                <span class="breadcrumb-item active">Contacto</span>
            </nav>
        </div>
    </div>
    <!-- Breadcrumb End -->


    <!-- Contact Start -->
    <div class="container-fluid py-3">
        <div class="container-bk">
            <div class="bg-light py-2 px-4 mb-3">
                <h3 class="m-0">Contact Us For Any Queries</h3>
            </div>
            <div class="row">
                <div class="col-md-5">
                    <div class="bg-light mb-3" style="padding: 30px;">
                        <h6 class="font-weight-bold">Contactanos</h6>
                        <p>Si necesitas publicidad, saber más sobre nosotros o simplemente quieres contactarnos, déjanos un mensaje y nos pondremos en contacto contigo lo antes posible.</p>
						
						<?php if (!empty($sys['business_address'])): ?>
                        <div class="d-flex align-items-center mb-3">
                            <i class="fa fa-2x fa-map-marker-alt text-primary mr-3"></i>
                            <div class="d-flex flex-column">
                                <h6 class="font-weight-bold">Nuestras Oficinas</h6>
                                <p class="m-0"><?= htmlspecialchars($sys['business_address']) ?></p>
                            </div>
                        </div>
						<?php endif; ?>
						<?php if (!empty($sys['site_email'])): ?>
                        <div class="d-flex align-items-center mb-3">
                            <i class="fa fa-2x fa-envelope-open text-primary mr-3"></i>
                            <div class="d-flex flex-column">
                                <h6 class="font-weight-bold">Email</h6>
                                <p class="m-0"><?= htmlspecialchars($sys['site_email']) ?></p>
                            </div>
                        </div>
						<?php endif; ?>
						<?php if (!empty($sys['business_phone'])): ?>
                        <div class="d-flex align-items-center">
                            <i class="fas fa-2x fa-phone-alt text-primary mr-3"></i>
                            <div class="d-flex flex-column">
                                <h6 class="font-weight-bold">Llamanos</h6>
                                <p class="m-0"><?= htmlspecialchars($sys['business_phone']) ?></p>
                            </div>
                        </div>
						<?php endif; ?>
						
						<?php if (!empty($sys['business_map'])): ?>
				<div class="col-md-12 contact-map outer-bottom-vs">
					<?= $sys['business_map'] ?>
				</div>
			<?php endif; ?>
						
                    </div>
                </div>
                <div class="col-md-7">
                    <div class="contact-form bg-light mb-3" style="padding: 30px;">
                        <div id="success"></div>
                        <form name="sentMessage" id="contactForm" novalidate="novalidate">
                            <div class="form-row">
                                <div class="col-md-6">
                                    <div class="control-group">
                                        <input type="text" class="form-control p-4" id="name" placeholder="Your Name" required="required" data-validation-required-message="Please enter your name" />
                                        <p class="help-block text-danger"></p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="control-group">
                                        <input type="email" class="form-control p-4" id="email" placeholder="Your Email" required="required" data-validation-required-message="Please enter your email" />
                                        <p class="help-block text-danger"></p>
                                    </div>
                                </div>
                            </div>
                            <div class="control-group">
                                <input type="text" class="form-control p-4" id="subject" placeholder="Subject" required="required" data-validation-required-message="Please enter a subject" />
                                <p class="help-block text-danger"></p>
                            </div>
                            <div class="control-group">
                                <textarea class="form-control" rows="4" id="message" placeholder="Message" required="required" data-validation-required-message="Please enter your message"></textarea>
                                <p class="help-block text-danger"></p>
                            </div>
                            <div>
                                <button class="btn btn-primary font-weight-semi-bold px-4" style="height: 50px;" type="submit" id="sendMessageButton">Send Message</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Contact End -->
