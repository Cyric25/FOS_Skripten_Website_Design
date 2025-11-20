/**
 * Glossar Frontend JavaScript
 * Handles automatic term detection, linking, and modal display
 */

class GlossarSystem {
    constructor() {
        this.settings = window.glossarData || {};
        this.terms = this.settings.terms || [];
        this.modalType = this.settings.modalType || 'tooltip';
        this.autoLink = this.settings.autoLink === '1';
        this.firstOnly = this.settings.firstOnly === '1';
        this.caseSensitive = this.settings.caseSensitive === '1';

        this.currentModal = null;
        this.linkedTerms = new Set(); // Track already linked terms

        this.init();
    }

    init() {
        if (this.terms.length === 0) {
            return;
        }

        // Wait for DOM to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.setupGlossar());
        } else {
            this.setupGlossar();
        }
    }

    setupGlossar() {
        // Enable debug mode via URL parameter ?glossarDebug=1
        if (window.location.search.includes('glossarDebug=1')) {
            window.glossarDebug = true;
            console.log('Glossar: Debug-Modus aktiviert');
            console.log('Glossar-Einstellungen:', {
                modalType: this.modalType,
                autoLink: this.autoLink,
                firstOnly: this.firstOnly,
                caseSensitive: this.caseSensitive,
                totalTerms: this.terms.length
            });
        }

        // Process manually marked terms
        this.processMarkedTerms();

        // Process automatic linking if enabled
        if (this.autoLink) {
            this.processAutoLinking();
        }

        // Setup event listeners
        this.setupEventListeners();

        // Create modal container
        this.createModalContainer();
    }

    processMarkedTerms() {
        const markedTerms = document.querySelectorAll('.glossar-term');
        markedTerms.forEach(element => {
            const termText = element.textContent.trim();
            const termData = this.findTermData(termText);

            if (termData) {
                this.makeTermClickable(element, termData);
            }
        });
    }

    processAutoLinking() {
        // Get all top-level content areas
        // IMPORTANT: Include CDB Container Block content areas for compatibility
        // The TreeWalker in linkTermsInElement() will recursively process all nested elements
        const contentAreas = document.querySelectorAll(
            '.entry-content, .page-content, article, .cbd-container-content'
        );

        contentAreas.forEach(contentArea => {
            this.linkTermsInElement(contentArea);
        });
    }

    linkTermsInElement(element) {
        // Skip if element is already processed or is a script/style tag
        if (element.classList.contains('glossar-processed') ||
            ['SCRIPT', 'STYLE', 'NOSCRIPT'].includes(element.tagName)) {
            return;
        }

        // Process text nodes
        const walker = document.createTreeWalker(
            element,
            NodeFilter.SHOW_TEXT,
            {
                acceptNode: (node) => {
                    // Skip if parent is already a glossar term or link
                    const parent = node.parentElement;
                    if (!parent ||
                        parent.classList.contains('glossar-term') ||
                        parent.tagName === 'A' ||
                        parent.tagName === 'SCRIPT' ||
                        parent.tagName === 'STYLE') {
                        return NodeFilter.FILTER_REJECT;
                    }
                    return NodeFilter.FILTER_ACCEPT;
                }
            }
        );

        const textNodes = [];
        let currentNode;
        while (currentNode = walker.nextNode()) {
            textNodes.push(currentNode);
        }

        // Process each text node
        textNodes.forEach(textNode => {
            this.replaceTermsInTextNode(textNode);
        });

        element.classList.add('glossar-processed');
    }

    replaceTermsInTextNode(textNode) {
        let text = textNode.textContent;
        let replacements = [];

        // Sort terms by word count (longest phrases first)
        // This ensures "chemisches Gleichgewicht" is matched before "Gleichgewicht"
        const sortedTerms = [...this.terms].sort((a, b) => {
            const aWords = a.term.trim().split(/\s+/).length;
            const bWords = b.term.trim().split(/\s+/).length;
            return bWords - aWords; // Descending order
        });

        // Find all terms in this text node
        sortedTerms.forEach(termData => {
            const term = termData.term;

            // Create regex for term matching
            const variants = this.getTermVariants(term);

            if (window.glossarDebug) {
                console.log(`Glossar: Suche nach Varianten von "${term}" in:`, text.substring(0, 100) + '...');
            }

            let foundInThisNode = false;

            variants.forEach(variant => {
                const flags = this.caseSensitive ? 'g' : 'gi';
                const regex = new RegExp(`\\b(${this.escapeRegex(variant)})\\b`, flags);

                let match;
                while ((match = regex.exec(text)) !== null) {
                    // Check firstOnly: Skip if this term was already linked in a previous node
                    if (this.firstOnly && this.linkedTerms.has(term.toLowerCase())) {
                        if (window.glossarDebug) {
                            console.log(`Glossar: Überspringe "${match[0]}" (Begriff "${term}" bereits global verlinkt)`);
                        }
                        continue;
                    }

                    if (window.glossarDebug) {
                        console.log(`Glossar: ✓ Match gefunden: "${match[0]}" (Variante: ${variant})`);
                    }

                    replacements.push({
                        start: match.index,
                        end: match.index + match[0].length,
                        text: match[0],
                        termData: termData
                    });

                    foundInThisNode = true;

                    // If firstOnly is enabled, mark as linked after first match
                    if (this.firstOnly) {
                        this.linkedTerms.add(term.toLowerCase());
                        if (window.glossarDebug) {
                            console.log(`Glossar: Begriff "${term}" als verlinkt markiert`);
                        }
                        // Break out of variant loop - we found one match, don't need more
                        return;
                    }
                }
            });
        });

        // Sort replacements by start position (reverse order for easier replacement)
        replacements.sort((a, b) => b.start - a.start);

        // Remove overlapping replacements
        replacements = this.removeOverlaps(replacements);

        // If no replacements, skip
        if (replacements.length === 0) {
            return;
        }

        // Create document fragment with replacements
        const fragment = document.createDocumentFragment();
        let lastIndex = text.length;

        replacements.forEach(replacement => {
            // Add text after this replacement
            if (lastIndex > replacement.end) {
                fragment.insertBefore(
                    document.createTextNode(text.substring(replacement.end, lastIndex)),
                    fragment.firstChild
                );
            }

            // Add glossar term element
            const span = document.createElement('span');
            span.className = 'glossar-term';
            span.textContent = replacement.text;
            this.makeTermClickable(span, replacement.termData);
            fragment.insertBefore(span, fragment.firstChild);

            // Note: linkedTerms.add() is now done earlier when match is found

            lastIndex = replacement.start;
        });

        // Add remaining text at the beginning
        if (lastIndex > 0) {
            fragment.insertBefore(
                document.createTextNode(text.substring(0, lastIndex)),
                fragment.firstChild
            );
        }

        // Replace the text node with the fragment
        textNode.parentNode.replaceChild(fragment, textNode);
    }

    getTermVariants(term) {
        const variants = [term];

        // German grammatical variants
        // This covers most common German noun inflections

        // 1. Plural forms
        if (term.endsWith('e')) {
            // Wörter auf -e: Atome -> Atomen (Dativ Plural)
            variants.push(term + 'n');
            variants.push(term + 's'); // Genitiv: Atomes
        } else if (term.endsWith('er')) {
            // Wörter auf -er: Moleküler -> Molekülers, Molekülen
            variants.push(term + 's');
            variants.push(term + 'n');
        } else if (term.endsWith('el')) {
            // Wörter auf -el: Artikel -> Artikels, Artikeln
            variants.push(term + 's');
            variants.push(term + 'n');
        } else if (term.endsWith('en')) {
            // Wörter auf -en: System -> Systemen (already covered below)
            variants.push(term + 's');
        } else {
            // Standard-Endungen für die meisten Substantive
            variants.push(term + 'e');   // Plural: System -> Systeme
            variants.push(term + 's');   // Genitiv: Systems
            variants.push(term + 'es');  // Genitiv: Systemes
            variants.push(term + 'en');  // Dativ/Akkusativ Plural: Systemen
        }

        // 2. Dativ singular (dem/einem + Substantiv)
        // Oft -e am Ende bei starken Maskulina/Neutra (veraltet, aber manchmal noch verwendet)
        if (!term.endsWith('e') && !term.endsWith('en')) {
            variants.push(term + 'e'); // dem Systeme (veraltet)
        }

        // 3. Umlaute für Pluralformen (häufige Muster)
        // Dies ist eine Heuristik und deckt nicht alle Fälle ab
        const withUmlaut = this.addUmlautVariants(term);
        withUmlaut.forEach(variant => {
            variants.push(variant);
            variants.push(variant + 'e');
            variants.push(variant + 'en');
            variants.push(variant + 's');
            variants.push(variant + 'es');
        });

        const uniqueVariants = [...new Set(variants)]; // Remove duplicates

        // Debug logging (can be removed in production)
        if (window.glossarDebug) {
            console.log(`Glossar: Varianten für "${term}":`, uniqueVariants);
        }

        return uniqueVariants;
    }

    /**
     * Generate umlaut variants for common German plural patterns
     * e.g., Kraft -> Kräfte, Stoff -> Stoffe, Buch -> Bücher
     */
    addUmlautVariants(term) {
        const variants = [];

        // Only add umlaut variants if term contains a, o, u, or au
        if (!/[aouäöü]|au/.test(term)) {
            return variants;
        }

        // Common patterns: a -> ä, o -> ö, u -> ü, au -> äu
        const umlautMap = {
            'a': 'ä',
            'o': 'ö',
            'u': 'ü',
            'au': 'äu'
        };

        // Try replacing vowels with umlauts (only once per word)
        Object.keys(umlautMap).forEach(vowel => {
            const umlaut = umlautMap[vowel];

            // Find last occurrence of vowel (German plurals typically umlaut the last vowel)
            const lastIndex = term.lastIndexOf(vowel);

            if (lastIndex !== -1) {
                const withUmlaut = term.substring(0, lastIndex) +
                                  umlaut +
                                  term.substring(lastIndex + vowel.length);

                // Only add if it's different from original
                if (withUmlaut !== term) {
                    variants.push(withUmlaut);
                }
            }
        });

        return variants;
    }

    removeOverlaps(replacements) {
        const result = [];
        const usedRanges = [];

        replacements.forEach(replacement => {
            // Check if this replacement overlaps with any already accepted range
            const overlaps = usedRanges.some(range => {
                // Check for any overlap: start is within range, end is within range, or encompasses range
                return (
                    (replacement.start >= range.start && replacement.start < range.end) ||
                    (replacement.end > range.start && replacement.end <= range.end) ||
                    (replacement.start <= range.start && replacement.end >= range.end)
                );
            });

            if (!overlaps) {
                result.push(replacement);
                usedRanges.push({ start: replacement.start, end: replacement.end });

                if (window.glossarDebug) {
                    console.log(`Glossar: ✓ Akzeptiert: "${replacement.text}" (${replacement.start}-${replacement.end})`);
                }
            } else {
                if (window.glossarDebug) {
                    console.log(`Glossar: ✗ Überlappt: "${replacement.text}" (${replacement.start}-${replacement.end})`);
                }
            }
        });

        return result;
    }

    escapeRegex(string) {
        return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    findTermData(termText) {
        const searchText = this.caseSensitive ? termText : termText.toLowerCase();

        return this.terms.find(term => {
            const termName = this.caseSensitive ? term.term : term.term.toLowerCase();

            // Check exact match
            if (termName === searchText) {
                return true;
            }

            // Check variants
            const variants = this.getTermVariants(term.term);
            return variants.some(variant => {
                const variantText = this.caseSensitive ? variant : variant.toLowerCase();
                return variantText === searchText;
            });
        });
    }

    makeTermClickable(element, termData) {
        element.classList.add('glossar-clickable');
        element.setAttribute('data-glossar-id', termData.id);
        element.setAttribute('role', 'button');
        element.setAttribute('tabindex', '0');
        element.setAttribute('aria-label', `Glossar-Begriff: ${termData.term}`);
    }

    createModalContainer() {
        if (this.modalType === 'tooltip') {
            this.createTooltipContainer();
        } else if (this.modalType === 'sidebar') {
            this.createSidebarContainer();
        }
    }

    createTooltipContainer() {
        const tooltip = document.createElement('div');
        tooltip.id = 'glossar-tooltip';
        tooltip.className = 'glossar-modal glossar-tooltip';
        tooltip.innerHTML = `
            <div class="glossar-tooltip-content">
                <button class="glossar-close" aria-label="Schließen">&times;</button>
                <h4 class="glossar-modal-title"></h4>
                <div class="glossar-modal-definition"></div>
                <a href="#" class="glossar-modal-link">Mehr erfahren &rarr;</a>
            </div>
        `;
        document.body.appendChild(tooltip);
        this.currentModal = tooltip;
    }

    createSidebarContainer() {
        const sidebar = document.createElement('div');
        sidebar.id = 'glossar-sidebar';
        sidebar.className = 'glossar-modal glossar-sidebar';
        sidebar.innerHTML = `
            <div class="glossar-sidebar-content">
                <div class="glossar-sidebar-header">
                    <h3 class="glossar-modal-title">Begriff</h3>
                    <button class="glossar-close" aria-label="Schließen">&times;</button>
                </div>
                <div class="glossar-sidebar-body">
                    <div class="glossar-modal-definition"></div>
                    <a href="#" class="glossar-modal-link">Vollständige Erklärung anzeigen &rarr;</a>
                </div>
            </div>
        `;
        document.body.appendChild(sidebar);
        this.currentModal = sidebar;
    }

    setupEventListeners() {
        // Click on glossar terms
        document.addEventListener('click', (e) => {
            const term = e.target.closest('.glossar-clickable');
            if (term) {
                e.preventDefault();
                const termId = term.getAttribute('data-glossar-id');
                const termData = this.terms.find(t => t.id == termId);
                if (termData) {
                    this.showModal(termData, term);
                }
                return; // Don't process other click handlers
            }

            // Close button
            if (e.target.closest('.glossar-close')) {
                e.preventDefault();
                this.hideModal();
                return;
            }

            // Close modal on click outside
            if (this.currentModal && this.currentModal.classList.contains('active')) {
                // For sidebar: check if click is outside sidebar content
                if (this.modalType === 'sidebar') {
                    const sidebarContent = this.currentModal.querySelector('.glossar-sidebar-content');
                    if (sidebarContent && !sidebarContent.contains(e.target)) {
                        this.hideModal();
                    }
                }
                // For tooltip: check if click is outside tooltip content
                else if (this.modalType === 'tooltip') {
                    const tooltipContent = this.currentModal.querySelector('.glossar-tooltip-content');
                    if (tooltipContent && !tooltipContent.contains(e.target)) {
                        this.hideModal();
                    }
                }
            }
        });

        // Keyboard navigation
        document.addEventListener('keydown', (e) => {
            // ESC to close
            if (e.key === 'Escape' && this.currentModal && this.currentModal.classList.contains('active')) {
                e.preventDefault();
                this.hideModal();
            }

            // Enter/Space on glossar term
            if ((e.key === 'Enter' || e.key === ' ') && e.target.classList.contains('glossar-clickable')) {
                e.preventDefault();
                const termId = e.target.getAttribute('data-glossar-id');
                const termData = this.terms.find(t => t.id == termId);
                if (termData) {
                    this.showModal(termData, e.target);
                }
            }
        });

        // Swipe gesture support for glossar sidebar (touch devices)
        if (this.modalType === 'sidebar' && this.currentModal) {
            let touchStartX = 0;
            let touchStartY = 0;
            let touchEndX = 0;
            let touchEndY = 0;

            this.currentModal.addEventListener('touchstart', (e) => {
                touchStartX = e.changedTouches[0].screenX;
                touchStartY = e.changedTouches[0].screenY;
            }, { passive: true });

            this.currentModal.addEventListener('touchend', (e) => {
                touchEndX = e.changedTouches[0].screenX;
                touchEndY = e.changedTouches[0].screenY;
                this.handleGlossarSwipe(touchStartX, touchStartY, touchEndX, touchEndY);
            }, { passive: true });
        }
    }

    handleGlossarSwipe(startX, startY, endX, endY) {
        const swipeThreshold = 50;
        const swipeX = endX - startX;
        const swipeY = endY - startY;

        // Check if horizontal swipe is dominant (not vertical scroll)
        if (Math.abs(swipeX) > Math.abs(swipeY) && Math.abs(swipeX) > swipeThreshold) {
            // Swipe right to close sidebar (it's on the right side)
            if (swipeX > 0) {
                this.hideModal();
            }
        }
    }

    showModal(termData, triggerElement) {
        if (!this.currentModal) return;

        // Update modal content
        const title = this.currentModal.querySelector('.glossar-modal-title');
        const definition = this.currentModal.querySelector('.glossar-modal-definition');
        const link = this.currentModal.querySelector('.glossar-modal-link');

        if (title) title.textContent = termData.term;
        if (definition) definition.textContent = termData.definition;
        if (link) link.href = termData.permalink;

        // Show modal
        this.currentModal.classList.add('active');

        // Position tooltip near trigger element
        if (this.modalType === 'tooltip') {
            this.positionTooltip(triggerElement);
        }

        // Focus trap
        setTimeout(() => {
            const closeButton = this.currentModal.querySelector('.glossar-close');
            if (closeButton) closeButton.focus();
        }, 100);
    }

    positionTooltip(triggerElement) {
        const tooltip = this.currentModal;
        const rect = triggerElement.getBoundingClientRect();
        const tooltipRect = tooltip.getBoundingClientRect();

        // Calculate position
        let top = rect.bottom + window.scrollY + 8;
        let left = rect.left + window.scrollX + (rect.width / 2) - (tooltipRect.width / 2);

        // Adjust if off-screen
        const viewportWidth = window.innerWidth;
        const viewportHeight = window.innerHeight;

        // Horizontal adjustment
        if (left < 10) {
            left = 10;
        } else if (left + tooltipRect.width > viewportWidth - 10) {
            left = viewportWidth - tooltipRect.width - 10;
        }

        // Vertical adjustment (show above if not enough space below)
        if (rect.bottom + tooltipRect.height > viewportHeight - 20) {
            top = rect.top + window.scrollY - tooltipRect.height - 8;
        }

        tooltip.style.top = top + 'px';
        tooltip.style.left = left + 'px';
    }

    hideModal() {
        if (this.currentModal) {
            this.currentModal.classList.remove('active');
        }
    }
}

// Initialize when script loads
new GlossarSystem();
