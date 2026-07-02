<?php
/**
 * Template for displaying Glossar archive (all glossary terms)
 */

get_header();
?>

<main id="main" class="site-main">
    <div class="container">
        <header class="page-header">
            <h1 class="page-title">Glossar</h1>
            <p class="archive-description">Hier findest du alle Fachbegriffe mit Erklärungen.</p>
        </header>

        <div class="glossar-archive">
            <?php
            // Get all glossar terms, grouped by first letter
            $glossar_terms = get_posts(array(
                'post_type' => 'glossar',
                'posts_per_page' => -1,
                'post_status' => 'publish',
                'orderby' => 'title',
                'order' => 'ASC',
            ));

            if ($glossar_terms) {
                // Group terms by first letter
                $grouped_terms = array();
                foreach ($glossar_terms as $term) {
                    $first_letter = mb_strtoupper(mb_substr($term->post_title, 0, 1));
                    if (!isset($grouped_terms[$first_letter])) {
                        $grouped_terms[$first_letter] = array();
                    }
                    $grouped_terms[$first_letter][] = $term;
                }

                // Sort by letter
                ksort($grouped_terms);

                // Display alphabet navigation
                echo '<nav class="glossar-alphabet">';
                echo '<div class="alphabet-letters">';
                foreach ($grouped_terms as $letter => $terms) {
                    echo '<a href="#letter-' . esc_attr($letter) . '" class="alphabet-link">' . esc_html($letter) . '</a>';
                }
                echo '</div>';
                echo '</nav>';

                // Display terms grouped by letter
                foreach ($grouped_terms as $letter => $terms) {
                    echo '<section class="glossar-letter-section" id="letter-' . esc_attr($letter) . '">';
                    echo '<h2 class="glossar-letter-heading">' . esc_html($letter) . '</h2>';
                    echo '<div class="glossar-terms-list">';

                    foreach ($terms as $term) {
                        setup_postdata($term);
                        ?>
                        <article class="glossar-term-entry">
                            <h3 class="glossar-term-title">
                                <a href="<?php echo esc_url(get_permalink($term->ID)); ?>">
                                    <?php echo esc_html($term->post_title); ?>
                                </a>
                            </h3>
                            <div class="glossar-term-excerpt">
                                <?php
                                $content = $term->post_content;
                                $excerpt = wp_trim_words(strip_tags($content), 30, '...');
                                echo esc_html($excerpt);
                                ?>
                            </div>
                            <a href="<?php echo esc_url(get_permalink($term->ID)); ?>" class="glossar-read-more">
                                Mehr erfahren &rarr;
                            </a>
                        </article>
                        <?php
                    }

                    echo '</div>'; // .glossar-terms-list
                    echo '</section>'; // .glossar-letter-section
                }

                wp_reset_postdata();
            } else {
                echo '<p class="no-terms-message">Es wurden noch keine Glossar-Begriffe erstellt.</p>';
            }
            ?>
        </div>
    </div>
</main>


<?php
get_footer();
?>
