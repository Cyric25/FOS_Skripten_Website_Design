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

                <?php
                // Display "Where is this term used?" section
                $term_usage = simple_clean_get_term_usage(get_the_ID());
                if (!empty($term_usage)) :
                ?>
                    <div class="glossar-term-usage">
                        <h3 class="usage-title">
                            <span class="dashicons dashicons-admin-links"></span>
                            Dieser Begriff wird verwendet in:
                        </h3>
                        <ul class="usage-list">
                            <?php foreach ($term_usage as $usage_post) : ?>
                                <li class="usage-item">
                                    <a href="<?php echo esc_url(get_permalink($usage_post->ID)); ?>" class="usage-link">
                                        <span class="usage-post-title"><?php echo esc_html($usage_post->post_title); ?></span>
                                        <span class="usage-post-type">
                                            <?php
                                            $type_label = $usage_post->post_type === 'page' ? 'Seite' : 'Beitrag';
                                            echo esc_html($type_label);
                                            ?>
                                        </span>
                                        <span class="usage-post-date"><?php echo date_i18n('d.m.Y', strtotime($usage_post->post_date)); ?></span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <p class="usage-count">
                            <?php
                            $count = count($term_usage);
                            printf(
                                _n('Wird auf %d Seite verwendet', 'Wird auf %d Seiten verwendet', $count, 'simple-clean-theme'),
                                $count
                            );
                            ?>
                        </p>
                    </div>
                <?php endif; ?>

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
    color: var(--color-ui-surface, #e24614);
    text-decoration: none;
    font-size: 0.95rem;
    transition: color 0.2s ease;
}

.breadcrumb a:hover {
    color: var(--color-ui-surface-dark, #c93d12);
    text-decoration: underline;
}

.glossar-single .entry-header {
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid var(--color-ui-surface, #e24614);
}

.glossar-single .glossar-term-title {
    color: var(--color-special-text, #71230a);
    margin: 0;
    font-size: 2.5rem;
}

.glossar-term-content {
    line-height: 1.8;
    font-size: 1.05rem;
    color: var(--color-text-primary, #333);
}

.glossar-term-content p {
    margin-bottom: 1.5rem;
}

.glossar-term-content h2,
.glossar-term-content h3,
.glossar-term-content h4 {
    margin-top: 2rem;
    margin-bottom: 1rem;
    color: var(--color-special-text, #71230a);
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

/* Usage Section */
.glossar-term-usage {
    margin: 3rem 0 2rem;
    padding: 2rem;
    background: var(--color-ui-surface-light, #f5ede9);
    border-left: 4px solid var(--color-ui-surface, #e24614);
    border-radius: 8px;
}

.glossar-term-usage .usage-title {
    margin: 0 0 1.5rem 0;
    color: var(--color-special-text, #71230a);
    font-size: 1.3rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.glossar-term-usage .usage-title .dashicons {
    font-size: 1.5rem;
    width: 1.5rem;
    height: 1.5rem;
}

.usage-list {
    list-style: none;
    margin: 0;
    padding: 0;
}

.usage-item {
    margin-bottom: 0.75rem;
}

.usage-link {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.75rem 1rem;
    background: #fff;
    border-radius: 4px;
    text-decoration: none;
    color: var(--color-text-primary, #333);
    transition: all 0.2s ease;
    border: 1px solid transparent;
}

.usage-link:hover {
    background: #fff;
    border-color: var(--color-ui-surface, #e24614);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transform: translateX(4px);
}

.usage-post-title {
    flex: 1;
    font-weight: 500;
    color: var(--color-ui-surface, #e24614);
}

.usage-post-type {
    padding: 0.25rem 0.75rem;
    background: var(--color-ui-surface, #e24614);
    color: #fff;
    border-radius: 12px;
    font-size: 0.8rem;
    text-transform: uppercase;
    font-weight: 600;
}

.usage-post-date {
    color: #666;
    font-size: 0.9rem;
}

.usage-count {
    margin: 1.5rem 0 0 0;
    padding-top: 1rem;
    border-top: 1px solid rgba(0, 0, 0, 0.1);
    color: #666;
    font-size: 0.9rem;
    font-style: italic;
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
    background: var(--color-ui-surface-light, #f5ede9);
    color: var(--color-ui-surface, #e24614);
    text-decoration: none;
    border-radius: 4px;
    transition: all 0.2s ease;
    font-weight: 500;
}

.term-navigation a:hover {
    background: var(--color-ui-surface, #e24614);
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
