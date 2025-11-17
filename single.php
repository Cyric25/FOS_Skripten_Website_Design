<?php get_header(); ?>

<main id="main" class="site-main">
    <div class="container">
        <div class="content-area">
            <?php while (have_posts()) : the_post(); ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                    <header class="entry-header">
                        <h1 class="entry-title"><?php the_title(); ?></h1>
                        <div class="entry-meta">
                            Veröffentlicht am <?php echo get_the_date(); ?> von <?php the_author(); ?>
                        </div>
                    </header>

                    <div class="entry-content">
                        <?php the_content(); ?>
                    </div>

                    <footer class="entry-footer">
                        <?php
                        $categories_list = get_the_category_list(', ');
                        if ($categories_list) {
                            echo '<p>Kategorien: ' . $categories_list . '</p>';
                        }

                        $tags_list = get_the_tag_list('', ', ');
                        if ($tags_list) {
                            echo '<p>Tags: ' . $tags_list . '</p>';
                        }
                        ?>
                    </footer>
                </article>

                <?php
                // Navigation zu vorherigen/nächsten Beiträgen
                the_post_navigation(array(
                    'prev_text' => '&laquo; %title',
                    'next_text' => '%title &raquo;'
                ));
                ?>

            <?php endwhile; ?>
        </div>
    </div>
</main>

<?php get_footer(); ?>