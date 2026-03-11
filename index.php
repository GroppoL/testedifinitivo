<?php require_once 'includes/header.php'; ?>


<section class="hero">
    <link rel="stylesheet" href="/assets/css/style.css">
    <div class="hero-overlay"></div>
    <div class="container hero-content">
        <h1 class="hero-title">Studio <span class="text-red">Sombra</span></h1>
        <p class="hero-subtitle">
            Transformando ideias em arte permanente. Mais de 10 anos de experiência.

        </p>
        <a href="cadastro.php" class="btn-hero">📅 Agende sua Sessão</a>
    </div>
</section>

<section class="bio-section">
    <div class="container">
        <div class="bio-grid">
            <div class="bio-image">
                <img src="https://images.unsplash.com/photo-1482328177731-274399da39f0?w=800" alt="Tatuador">
            </div>
            <div class="bio-content">
                <h2 class="section-title">Conheça o <span class="text-red">Artista</span></h2>
                <p class="bio-text">
                    Com mais de uma década de experiência, especializo-me em criar

                    tatuagens únicas que contam histórias. Cada projeto é tratado com

                    atenção aos detalhes, desde o conceito inicial até a execução final.

                </p>
                <p class="bio-text">
                    Meu estúdio oferece um ambiente acolhedor e profissional, onde sua

                    segurança e conforto são prioridades.

                </p>

                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">🏆</div>
                        <div class="stat-number">10+</div>
                        <div class="stat-label">Anos de Experiência</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">👥</div>
                        <div class="stat-number">2000+</div>
                        <div class="stat-label">Clientes Satisfeitos</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="portfolio-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Meu <span class="text-red">Portfólio</span></h2>
            <p class="section-subtitle">Explore alguns dos trabalhos realizados</p>
        </div>

        <div class="gallery-grid">
            <?php

            $imagens = [

                ['url' => 'https://images.unsplash.com/photo-1767121082263-652dbe270e57?w=400', 'titulo' => 'Realismo'],

                ['url' => 'https://images.unsplash.com/photo-1721160223584-b3a19f2e0e6a?w=400', 'titulo' => 'Blackwork'],

                ['url' => 'https://images.unsplash.com/photo-1610942933193-8fafd0973f6d?w=400', 'titulo' => 'Manga'],

                ['url' => 'https://images.unsplash.com/photo-1759247943324-9d84bff28bca?w=400', 'titulo' => 'Grande Porte'],

                ['url' => 'https://images.unsplash.com/photo-1653845508077-3a15db13f807?w=400', 'titulo' => 'Geométrico'],

                ['url' => 'https://images.unsplash.com/photo-1571855618158-f2ea615c339a?w=400', 'titulo' => 'Floral'],

            ];


            foreach ($imagens as $img): ?>
                <div class="gallery-item">
                    <img src="<?php echo $img['url']; ?>" alt="<?php echo $img['titulo']; ?>">
                    <div class="gallery-overlay">
                        <p class="gallery-title"><?php echo $img['titulo']; ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="cta-section">
    <div class="container text-center">
        <div class="cta-icon">❤️</div>
        <h2 class="cta-title">Pronto para sua <span class="text-red">Nova Tattoo</span>?</h2>
        <p class="cta-text">
            Cadastre-se agora e agende uma consulta. Vamos transformar sua ideia em realidade!

        </p>
        <a href="cadastro.php" class="btn-hero">📅 Começar Agora</a>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>