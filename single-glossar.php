<?php
/**
 * Template for displaying single Glossar term
 */

get_header();
?>

<main id="main" class="site-main">
    <div class="container">
        <?php while (have_posts()) : the_post(); ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class('glossar-single'); ?>>
                <header class="entry-header">
                    <div class="breadcrumb">
                        <a href="<?php echo esc_url(get_post_type_archive_link('glossar')); ?>">
                            &larr; Zurück zum Glossar
                        </a>
                    </div>
                    <h1 class="entry-title glossar-term-title"><?php the_title(); ?></h1>
                </header>

                <div class="entry-content glossar-term-content">
                    <?php
                    the_content();

                    // Display thumbnail if available
                    if (has_post_thumbnail()) {
                        echo '<div class="glossar-term-image">';
                        the_post_thumbnail('large');
                        echo '</div>';
                    }
                    ?>
                </div>

                <footer class="entry-footer">
                    <div class="glossar-navigation">
                        <?php
                        // Get previous and next terms
                        $prev_post = get_previous_post();
                        $next_post = get_next_post();

                        if ($prev_post || $next_post) {
                            echo '<nav class="term-navigation">';

                            if ($prev_post) {
                                echo '<a href="' . esc_url(get_permalink($prev_post)) . '" class="nav-previous">';
                                echo '&larr; ' . esc_html($prev_post->post_title);
                                echo '</a>';
                            }

                            if ($next_post) {
                                echo '<a href="' . esc_url(get_permalink($next_post)) . '" class="nav-next">';
                                echo esc_html($next_post->post_title) . ' &rarr;';
                                echo '</a>';
                            }

                            echo '</nav>';
                        }
                        ?>
                    </div>
                </footer>
            </article>
        <?php endwhile; ?>
    </div>
</main>

<style>
/* Single Glossar Term Styles */
.glossar-single {
    max-width: 800px;
    margin: 0 auto;
}

.breadcrumb {
    margin-bottom: 1rem;
}

.breadcrumb a {
    color: #0073aa;
    text-decoration: none;
    font-size: 0.95rem;
    transition: color 0.2s ease;
}

.breadcrumb a:hover {
    color: #005a87;
    text-decoration: underline;
}

.glossar-single .entry-header {
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #0073aa;
}

.glossar-single .glossar-term-title {
    color: #0073aa;
    margin: 0;
    font-size: 2.5rem;
}

.glossar-term-content {
    line-height: 1.8;
    font-size: 1.05rem;
    color: #333;
}

.glossar-term-content p {
    margin-bottom: 1.5rem;
}

.glossar-term-content h2,
.glossar-term-content h3,
.glossar-term-content h4 {
    margin-top: 2rem;
    margin-bottom: 1rem;
    color: #0073aa;
}

.glossar-term-content ul,
.glossar-term-content ol {
    margin: 1rem 0 1.5rem 1.5rem;
}

.glossar-term-content li {
    margin-bottom: 0.5rem;
}

.glossar-term-image {
    margin: 2rem 0;
    text-align: center;
}

.glossar-term-image img {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

/* Navigation */
.entry-footer {
    margin-top: 3rem;
    padding-top: 2rem;
    border-top: 1px solid #eee;
}

.term-navigation {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
}

.term-navigation a {
    flex: 1;
    padding: 1rem;
    background: #f8f9fa;
    color: #0073aa;
    text-decoration: none;
    border-radius: 4px;
    transition: all 0.2s ease;
    font-weight: 500;
}

.term-navigation a:hover {
    background: #0073aa;
    color: #fff;
}

.nav-previous {
    text-align: left;
}

.nav-next {
    text-align: right;
}

/* Responsive */
@media (max-width: 768px) {
    .glossar-single .glossar-term-title {
        font-size: 2rem;
    }

    .glossar-term-content {
        font-size: 1rem;
    }

    .term-navigation {
        flex-direction: column;
    }

    .nav-next {
        text-align: left;
    }
}

@media (max-width: 480px) {
    .glossar-single .glossar-term-title {
        font-size: 1.75rem;
    }

    .glossar-term-content {
        font-size: 0.95rem;
    }
}
</style>

<?php
get_footer();
?>
