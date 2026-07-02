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
                                    <a href="<?php echo esc_url(get_permalink($usage_post->ID) . '#glossar-term-' . get_the_ID()); ?>" class="usage-link">
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


<?php
get_footer();
?>
