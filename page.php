<?php get_header(); ?>

<main id="main" class="site-main site-main-with-sidebar">
    <div class="container">
        <div class="content-with-sidebar">
            <?php
            // Only show sidebar if navigation is not hidden
            if (!simple_clean_should_hide_navigation()) {
                get_sidebar();
            }
            ?>

            <div class="content-area">
                <?php while (have_posts()) : the_post(); ?>
                    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                        <header class="entry-header">
                            <h1 class="entry-title"><?php the_title(); ?></h1>
                        </header>

                        <div class="entry-content">
                            <?php the_content(); ?>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</main>

<?php get_footer(); ?>