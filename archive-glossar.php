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

<style>
/* Glossar Archive Styles */
.glossar-archive {
    margin-top: 2rem;
}

.page-header {
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #0073aa;
}

.page-title {
    margin: 0 0 0.5rem 0;
    color: #0073aa;
}

.archive-description {
    margin: 0;
    color: #666;
    font-size: 1.1rem;
}

/* Alphabet Navigation */
.glossar-alphabet {
    margin: 2rem 0;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
    position: sticky;
    top: 80px;
    z-index: 10;
}

.alphabet-letters {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    justify-content: center;
}

.alphabet-link {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2.5rem;
    height: 2.5rem;
    background: #fff;
    color: #0073aa;
    text-decoration: none;
    font-weight: 600;
    border-radius: 4px;
    transition: all 0.2s ease;
    border: 1px solid #ddd;
}

.alphabet-link:hover {
    background: #0073aa;
    color: #fff;
    border-color: #0073aa;
    transform: translateY(-2px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* Letter Sections */
.glossar-letter-section {
    margin: 3rem 0;
    scroll-margin-top: 150px;
}

.glossar-letter-heading {
    font-size: 2.5rem;
    color: #0073aa;
    margin: 0 0 1.5rem 0;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #e0e0e0;
}

.glossar-terms-list {
    display: grid;
    gap: 1.5rem;
}

/* Term Entries */
.glossar-term-entry {
    padding: 1.5rem;
    background: #fff;
    border: 1px solid #eee;
    border-radius: 8px;
    transition: all 0.2s ease;
}

.glossar-term-entry:hover {
    border-color: #0073aa;
    box-shadow: 0 2px 8px rgba(0, 115, 170, 0.1);
}

.glossar-term-title {
    margin: 0 0 0.75rem 0;
    font-size: 1.5rem;
}

.glossar-term-title a {
    color: #333;
    text-decoration: none;
    transition: color 0.2s ease;
}

.glossar-term-title a:hover {
    color: #0073aa;
}

.glossar-term-excerpt {
    margin: 0 0 1rem 0;
    color: #666;
    line-height: 1.6;
}

.glossar-read-more {
    display: inline-block;
    color: #0073aa;
    text-decoration: none;
    font-weight: 500;
    transition: color 0.2s ease;
}

.glossar-read-more:hover {
    color: #005a87;
    text-decoration: underline;
}

.no-terms-message {
    text-align: center;
    padding: 3rem;
    color: #666;
    font-size: 1.1rem;
}

/* Responsive */
@media (max-width: 768px) {
    .glossar-alphabet {
        position: static;
    }

    .alphabet-letters {
        gap: 0.25rem;
    }

    .alphabet-link {
        width: 2rem;
        height: 2rem;
        font-size: 0.9rem;
    }

    .glossar-letter-heading {
        font-size: 2rem;
    }

    .glossar-term-title {
        font-size: 1.25rem;
    }
}

@media (max-width: 480px) {
    .glossar-letter-heading {
        font-size: 1.75rem;
    }

    .glossar-term-entry {
        padding: 1rem;
    }

    .glossar-term-title {
        font-size: 1.1rem;
    }

    .glossar-term-excerpt {
        font-size: 0.9rem;
    }
}
</style>

<?php
get_footer();
?>
