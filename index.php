<?php get_header(); ?>

<main id="main" class="site-main">
    <div class="container">
        <div class="content-area">
            <?php if (have_posts()) : ?>
                <?php while (have_posts()) : the_post(); ?>
                    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                        <header class="entry-header">
                            <h2 class="entry-title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h2>
                            <div class="entry-meta">
                                Veröffentlicht am <?php echo get_the_date(); ?> von <?php the_author(); ?>
                            </div>
                        </header>

                        <div class="entry-content">
                            <?php the_excerpt(); ?>
                        </div>
                    </article>
                <?php endwhile; ?>

                <?php
                // Pagination
                the_posts_navigation(array(
                    'prev_text' => '&laquo; Ältere Beiträge',
                    'next_text' => 'Neuere Beiträge &raquo;'
                ));
                ?>

            <?php else : ?>
                <p>Keine Beiträge gefunden.</p>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php get_footer(); ?>