<?php include __DIR__ . '/partials/features.php'; ?>


<?php
    $stmt = $pdo->prepare("
        SELECT * FROM ads 
        WHERE position = 1 AND status = 'active' 
        LIMIT 1
    ");
    $stmt->execute();
    $ad = $stmt->fetch(PDO::FETCH_ASSOC);
    ?>

    <?php if ($ad && !empty($ad['image_url'])): ?>
        <?php if (!empty($ad['target_url'])): ?>
<div class="container-fluid py-5">
	<div class="container py-5">   
        <div class="row">
            <a href="<?= htmlspecialchars($ad['target_url']) ?>" target="_blank" rel="noopener">
                <img class="img-fluid"
                     src="<?= URLBASE . htmlspecialchars($ad['image_url']) ?>"
                     alt="<?= htmlspecialchars($ad['title'] ?? 'Publicidad') ?>">
            </a>
		</div>
	</div>
	</div>
        <?php else: ?>
<div class="container-fluid py-5">
	<div class="container py-5">   
        <div class="row">
            <img class="img-fluid"
                 src="<?= URLBASE . htmlspecialchars($ad['image_url']) ?>"
                 alt="<?= htmlspecialchars($ad['title'] ?? 'Publicidad') ?>">
			</div>
	</div>
	</div>
        <?php endif; ?>
    <?php endif; ?>
		

<?php include __DIR__ . '/partials/main_post.php'; ?>

		
<?php include __DIR__ . '/partials/ads3.php'; ?>
		
       


        <!-- Banner Start -
        <div class="container-fluid py-5 my-5" style="background: linear-gradient(rgba(202, 203, 185, 1), rgba(202, 203, 185, 1));">
            <div class="container">
                <div class="row g-4 align-items-center">
                    <div class="col-lg-7">
                        <h1 class="mb-4 text-primary">Newsers</h1>
                        <h1 class="mb-4">Get Every Weekly Updates</h1>
                        <p class="text-dark mb-4 pb-2">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley
                        </p>
                        <div class="position-relative mx-auto">
                            <input class="form-control w-100 py-3 rounded-pill" type="email" placeholder="Your Busines Email">
                            <button type="submit" class="btn btn-primary py-3 px-5 position-absolute rounded-pill text-white h-100" style="top: 0; right: 0;">Subscribe Now</button>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="rounded">
                            <img src="img/banner-img.jpg" class="img-fluid rounded w-100 rounded" alt="">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Banner End -->


      <?php include __DIR__ . '/partials/home_latest_news.php'; ?> 
		<?php include __DIR__ . '/partials/ads4.php'; ?>
      <?php include __DIR__ . '/partials/home_latest_news_footer.php'; ?> 

	<?php if (!empty($sys['banner_inferior'])): ?>
  <div class="footer-banner text-center my-3">
    <img src="<?= htmlspecialchars($sys['banner_inferior'], ENT_QUOTES, 'UTF-8') ?>" alt="Banner Inferior" class="img-fluid">
  </div>
	<?php endif; ?>
