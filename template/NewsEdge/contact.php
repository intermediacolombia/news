<!-- Contact Us Start -->
<section class="bg-body section-space-less30">
    <div class="container">
        <div class="item-box-light-md-less30">
            <div class="row">
                <!-- Columna izquierda: info + mapa -->
                <div class="col-lg-5 col-md-12 mb-30">
                    <div class="contact-info-box">
                        <div class="topic-border color-cinnabar mb-30">
                            <div class="topic-box-lg color-cinnabar">Contáctanos</div>
                        </div>
                        
                        <p class="description-body-dark mb-30">
                            <?= htmlspecialchars($sys['business_description'] ?? 'Si necesitas publicidad, información sobre nuestros servicios o simplemente quieres comunicarte con nosotros, déjanos tu mensaje y te responderemos lo antes posible.') ?>
                        </p>

                        <?php if (!empty($sys['business_map'])): ?>
                        <div class="rounded overflow-hidden mb-30">
                            <?= $sys['business_map'] ?>
                        </div>
                        <?php else: ?>
                        <div class="rounded mb-30">
                            <iframe class="rounded width-100 contact-map"
                                src="https://www.google.com/maps?q=Colombia&output=embed"
                                loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade">
                            </iframe>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Columna derecha: formulario + contactos -->
                <div class="col-lg-7 col-md-12 mb-30">
                    <!-- Formulario de Contacto -->
                    <div class="contact-form-box item-box-light-md item-shadow-1 p-30 mb-30">
                        <h3 class="title-semibold-dark size-xl mb-30">Envíanos un mensaje</h3>
                        
                        <form id="contactForm" method="post" action="enviar_contacto.php">
                            <div class="row">
                                <div class="col-lg-6 col-md-6 col-12 mb-20">
                                    <input type="text" 
                                           name="name" 
                                           class="form-control contact-input" 
                                           placeholder="Tu nombre" 
                                           required>
                                </div>
                                <div class="col-lg-6 col-md-6 col-12 mb-20">
                                    <input type="email" 
                                           name="email" 
                                           class="form-control contact-input" 
                                           placeholder="Tu correo electrónico" 
                                           required>
                                </div>
                                <div class="col-lg-6 col-md-6 col-12 mb-20">
                                    <input type="text" 
                                           name="phone" 
                                           class="form-control contact-input" 
                                           placeholder="Tu teléfono">
                                </div>
                                <div class="col-lg-6 col-md-6 col-12 mb-20">
                                    <input type="text" 
                                           name="subject" 
                                           class="form-control contact-input" 
                                           placeholder="Asunto">
                                </div>
                                <div class="col-12 mb-20">
                                    <textarea name="message" 
                                              class="form-control contact-textarea" 
                                              rows="6" 
                                              placeholder="Tu mensaje"></textarea>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="contact-submit-btn">
                                        Enviar mensaje
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Información de Contacto -->
                    <div class="row">
                        <?php if (!empty($sys['business_address'])): ?>
                        <div class="col-xl-6 col-lg-6 col-md-6 col-12 mb-20">
                            <div class="contact-info-card item-box-light-md item-shadow-1 p-20">
                                <div class="contact-info-content">
                                    <i class="fa fa-map-marker contact-icon" aria-hidden="true"></i>
                                    <div class="contact-info-text">
                                        <h4 class="title-semibold-dark size-md mb-10">Dirección</h4>
                                        <p class="description-body-light mb-0">
                                            <?= htmlspecialchars($sys['business_address']) ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($sys['site_email'])): ?>
                        <div class="col-xl-6 col-lg-6 col-md-6 col-12 mb-20">
                            <div class="contact-info-card item-box-light-md item-shadow-1 p-20">
                                <div class="contact-info-content">
                                    <i class="fa fa-envelope contact-icon" aria-hidden="true"></i>
                                    <div class="contact-info-text">
                                        <h4 class="title-semibold-dark size-md mb-10">Correo</h4>
                                        <p class="description-body-light mb-0">
                                            <?= htmlspecialchars($sys['site_email']) ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($sys['business_phone'])): ?>
                        <div class="col-xl-6 col-lg-6 col-md-6 col-12 mb-20">
                            <div class="contact-info-card item-box-light-md item-shadow-1 p-20">
                                <div class="contact-info-content">
                                    <i class="fa fa-phone contact-icon" aria-hidden="true"></i>
                                    <div class="contact-info-text">
                                        <h4 class="title-semibold-dark size-md mb-10">Teléfono</h4>
                                        <p class="description-body-light mb-0">
                                            <?= htmlspecialchars($sys['business_phone']) ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="col-xl-6 col-lg-6 col-md-6 col-12 mb-20">
                            <div class="contact-info-card item-box-light-md item-shadow-1 p-20">
                                <div class="contact-info-content">
                                    <i class="fa fa-share-alt contact-icon" aria-hidden="true"></i>
                                    <div class="contact-info-text">
                                        <h4 class="title-semibold-dark size-md mb-10">Síguenos</h4>
                                        <ul class="contact-social-list">
                                            <?php if (!empty($sys['facebook'])): ?>
                                            <li>
                                                <a href="<?= htmlspecialchars($sys['facebook']) ?>" 
                                                   target="_blank" 
                                                   class="contact-social-link facebook">
                                                    <i class="fa fa-facebook" aria-hidden="true"></i>
                                                </a>
                                            </li>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($sys['twitter'])): ?>
                                            <li>
                                                <a href="<?= htmlspecialchars($sys['twitter']) ?>" 
                                                   target="_blank" 
                                                   class="contact-social-link twitter">
                                                    <i class="fa fa-twitter" aria-hidden="true"></i>
                                                </a>
                                            </li>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($sys['youtube'])): ?>
                                            <li>
                                                <a href="<?= htmlspecialchars($sys['youtube']) ?>" 
                                                   target="_blank" 
                                                   class="contact-social-link youtube">
                                                    <i class="fa fa-youtube" aria-hidden="true"></i>
                                                </a>
                                            </li>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($sys['instagram'])): ?>
                                            <li>
                                                <a href="<?= htmlspecialchars($sys['instagram']) ?>" 
                                                   target="_blank" 
                                                   class="contact-social-link instagram">
                                                    <i class="fa fa-instagram" aria-hidden="true"></i>
                                                </a>
                                            </li>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($sys['tiktok'])): ?>
                                            <li>
                                                <a href="<?= htmlspecialchars($sys['tiktok']) ?>" 
                                                   target="_blank"
                                                   class="contact-social-link tiktok">
                                                    <i class="fa fa-music" aria-hidden="true"></i>
                                                </a>
                                            </li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Contact Us End -->

<style>
    /* Mapa */
    .contact-map {
        height: 425px;
        border: 0;
    }
    
    /* Formulario */
    .contact-input,
    .contact-textarea {
        height: 50px;
        border: 1px solid #ddd;
        padding: 0 20px;
        border-radius: 4px;
        width: 100%;
        transition: all 0.3s ease;
    }
    
    .contact-textarea {
        height: auto;
        padding: 15px 20px;
    }
    
    .contact-input:focus,
    .contact-textarea:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(196, 30, 58, 0.1);
    }
    
    .contact-submit-btn {
        width: 100%;
        height: 50px;
        background-color: #000;
        color: white;
        border: none;
        border-radius: 4px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .contact-submit-btn:hover {
        background-color: var(--primary);
    }
    
    /* Tarjetas de información */
    .contact-info-card {
        border-radius: 4px;
        min-height: 100px;
        transition: all 0.3s ease;
    }
    
    .contact-info-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.15) !important;
    }
    
    .contact-info-content {
        display: flex;
        align-items: start;
    }
    
    .contact-icon {
        font-size: 32px;
        color: #000;
        margin-right: 20px;
        margin-top: 5px;
        flex-shrink: 0;
    }
    
    .contact-info-text {
        flex: 1;
    }
    
    /* Redes sociales */
    .contact-social-list {
        margin: 0;
        padding: 0;
        list-style: none;
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }
    
    .contact-social-link {
        display: inline-block;
        width: 35px;
        height: 35px;
        line-height: 35px;
        text-align: center;
        color: white;
        border-radius: 50%;
        transition: all 0.3s ease;
    }
    
    .contact-social-link:hover {
        transform: scale(1.1);
        opacity: 0.8;
    }
    
    .contact-social-link.facebook {
        background: #3b5998;
    }
    
    .contact-social-link.twitter {
        background: #1da1f2;
    }
    
    .contact-social-link.youtube {
        background: #ff0000;
    }
    
    .contact-social-link.instagram {
        background: #e4405f;
    }
    
    .contact-social-link.tiktok {
        background: #000000;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .contact-map {
            height: 300px;
        }
        
        .contact-info-content {
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        
        .contact-icon {
            margin-right: 0;
            margin-bottom: 15px;
        }
        
        .contact-social-list {
            justify-content: center;
        }
    }
</style>