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
        // Get all content areas (avoid navigation, sidebar, etc.)
        const contentAreas = document.querySelectorAll('.entry-content, .page-content, article');

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

        // Find all terms in this text node
        this.terms.forEach(termData => {
            const term = termData.term;

            // Skip if we should only link first occurrence and it's already linked
            if (this.firstOnly && this.linkedTerms.has(term.toLowerCase())) {
                return;
            }

            // Create regex for term matching
            const variants = this.getTermVariants(term);
            variants.forEach(variant => {
                const flags = this.caseSensitive ? 'g' : 'gi';
                const regex = new RegExp(`\\b(${this.escapeRegex(variant)})\\b`, flags);

                let match;
                while ((match = regex.exec(text)) !== null) {
                    replacements.push({
                        start: match.index,
                        end: match.index + match[0].length,
                        text: match[0],
                        termData: termData
                    });
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

            // Mark as linked
            if (this.firstOnly) {
                this.linkedTerms.add(replacement.termData.term.toLowerCase());
            }

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

        // Add singular/plural variants (German)
        // Simple heuristics for German plurals
        if (term.endsWith('e')) {
            variants.push(term + 'n'); // Atome -> Atomen
        } else if (term.endsWith('er')) {
            variants.push(term); // Moleküler
        } else {
            variants.push(term + 'e'); // Atom -> Atome
            variants.push(term + 's'); // Atom -> Atoms
            variants.push(term + 'en'); // Atom -> Atomen
        }

        return [...new Set(variants)]; // Remove duplicates
    }

    removeOverlaps(replacements) {
        const result = [];
        let lastEnd = Infinity;

        replacements.forEach(replacement => {
            if (replacement.end <= lastEnd) {
                result.push(replacement);
                lastEnd = replacement.start;
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
            }

            // Close modal on click outside
            if (this.currentModal && this.currentModal.classList.contains('active')) {
                if (!e.target.closest('.glossar-modal-content, .glossar-sidebar-content, .glossar-clickable')) {
                    this.hideModal();
                }
            }

            // Close button
            if (e.target.closest('.glossar-close')) {
                e.preventDefault();
                this.hideModal();
            }
        });

        // Keyboard navigation
        document.addEventListener('keydown', (e) => {
            // ESC to close
            if (e.key === 'Escape' && this.currentModal && this.currentModal.classList.contains('active')) {
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
