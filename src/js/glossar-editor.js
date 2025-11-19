/**
 * Glossar Gutenberg Editor Integration - Enhanced
 * Adds a custom button to the toolbar for marking and creating glossary terms
 */

const { registerFormatType, toggleFormat, applyFormat, getActiveFormat } = wp.richText;
const { RichTextToolbarButton } = wp.blockEditor;
const { Component, createElement: el } = wp.element;
const { Modal, Button, TextControl, TextareaControl, SelectControl, Notice } = wp.components;
const apiFetch = wp.apiFetch;

class GlossarFormatButton extends Component {
    constructor(props) {
        super(props);
        this.state = {
            isModalOpen: false,
            selectedText: '',
            formData: {
                title: '',
                definition: '',
                category: '',
                tags: '',
                links: '',
            },
            isLoading: false,
            error: null,
            success: null,
        };
    }

    openModal() {
        const { value } = this.props;
        const { start, end, text } = value;
        const selectedText = text.slice(start, end);

        if (!selectedText) {
            this.setState({
                error: 'Bitte markieren Sie zuerst einen Text.',
            });
            return;
        }

        this.setState({
            isModalOpen: true,
            selectedText: selectedText,
            formData: {
                ...this.state.formData,
                title: selectedText,
            },
            error: null,
            success: null,
        });
    }

    closeModal() {
        this.setState({
            isModalOpen: false,
            formData: {
                title: '',
                definition: '',
                category: '',
                tags: '',
                links: '',
            },
            error: null,
            success: null,
        });
    }

    handleFieldChange(field, value) {
        this.setState({
            formData: {
                ...this.state.formData,
                [field]: value,
            },
        });
    }

    async createGlossarTerm() {
        const { formData } = this.state;
        const { value, onChange } = this.props;

        // Validate
        if (!formData.title || !formData.definition) {
            this.setState({
                error: 'Titel und Definition sind erforderlich.',
            });
            return;
        }

        this.setState({ isLoading: true, error: null });

        try {
            // apiFetch automatically handles authentication and nonce
            const response = await apiFetch({
                path: '/simple-clean/v1/glossar',
                method: 'POST',
                data: formData,
            });

            if (response && response.success) {
                // Mark the selected text as glossar term
                const newValue = applyFormat(value, {
                    type: 'simple-clean-theme/glossar',
                });
                onChange(newValue);

                this.setState({
                    success: response.message || 'Glossar-Eintrag erfolgreich erstellt!',
                    isLoading: false,
                });

                // Close modal after 2 seconds
                setTimeout(() => {
                    this.closeModal();
                }, 2000);
            } else {
                throw new Error('Unerwartete Antwort vom Server.');
            }
        } catch (error) {
            console.error('Glossar API Error:', error);
            this.setState({
                error: error.message || 'Fehler beim Erstellen des Glossar-Eintrags.',
                isLoading: false,
            });
        }
    }

    render() {
        const { isActive, value, onChange } = this.props;
        const { isModalOpen, selectedText, formData, isLoading, error, success } = this.state;

        return el('div', {},
            // Toolbar Button
            el(RichTextToolbarButton, {
                icon: 'book-alt',
                title: 'Glossar-Begriff hinzufügen',
                onClick: () => {
                    const activeFormat = getActiveFormat(value, 'simple-clean-theme/glossar');
                    if (activeFormat) {
                        // If already marked, just toggle off
                        onChange(toggleFormat(value, {
                            type: 'simple-clean-theme/glossar',
                        }));
                    } else {
                        // Open modal to create new term
                        this.openModal();
                    }
                },
                isActive: isActive,
            }),

            // Modal Dialog
            isModalOpen && el(Modal, {
                title: 'Glossar-Eintrag erstellen',
                onRequestClose: () => this.closeModal(),
                className: 'glossar-editor-modal',
                style: { maxWidth: '600px' },
            },
                el('div', { className: 'glossar-editor-form' },
                    // Success Message
                    success && el(Notice, {
                        status: 'success',
                        isDismissible: false,
                    }, success),

                    // Error Message
                    error && el(Notice, {
                        status: 'error',
                        isDismissible: true,
                        onRemove: () => this.setState({ error: null }),
                    }, error),

                    // Selected Text Display
                    el('p', { style: { marginBottom: '1rem', color: '#666' } },
                        el('strong', {}, 'Markierter Text: '),
                        el('em', {}, `"${selectedText}"`)
                    ),

                    // Title Field
                    el(TextControl, {
                        label: 'Begriff (Titel) *',
                        value: formData.title,
                        onChange: (value) => this.handleFieldChange('title', value),
                        help: 'Der Name des Glossar-Begriffs',
                        disabled: isLoading,
                    }),

                    // Definition Field
                    el(TextareaControl, {
                        label: 'Definition/Erklärung *',
                        value: formData.definition,
                        onChange: (value) => this.handleFieldChange('definition', value),
                        help: 'Ausführliche Erklärung des Begriffs',
                        rows: 5,
                        disabled: isLoading,
                    }),

                    // Category Select
                    el(SelectControl, {
                        label: 'Kategorie',
                        value: formData.category,
                        options: [
                            { label: '-- Keine Kategorie --', value: '' },
                            ...glossarEditorData.categories.map(cat => ({
                                label: cat.name,
                                value: cat.id.toString(),
                            })),
                        ],
                        onChange: (value) => this.handleFieldChange('category', value),
                        help: 'Kategorisieren Sie den Glossar-Eintrag',
                        disabled: isLoading,
                    }),

                    // Tags Field
                    el(TextControl, {
                        label: 'Schlagwörter (Tags)',
                        value: formData.tags,
                        onChange: (value) => this.handleFieldChange('tags', value),
                        help: 'Komma-getrennte Liste von Schlagwörtern (z.B. "Chemie, Atom, Grundlagen")',
                        disabled: isLoading,
                    }),

                    // Links Field
                    el(TextareaControl, {
                        label: 'Weiterführende Links',
                        value: formData.links,
                        onChange: (value) => this.handleFieldChange('links', value),
                        help: 'Optional: Links zu weiterführenden Ressourcen (einer pro Zeile)',
                        rows: 3,
                        disabled: isLoading,
                    }),

                    // Action Buttons
                    el('div', {
                        style: {
                            marginTop: '1.5rem',
                            display: 'flex',
                            justifyContent: 'flex-end',
                            gap: '0.5rem',
                        }
                    },
                        el(Button, {
                            variant: 'tertiary',
                            onClick: () => this.closeModal(),
                            disabled: isLoading,
                        }, 'Abbrechen'),

                        el(Button, {
                            variant: 'primary',
                            onClick: () => this.createGlossarTerm(),
                            isBusy: isLoading,
                            disabled: isLoading || !formData.title || !formData.definition,
                        }, isLoading ? 'Erstelle...' : 'Glossar-Eintrag erstellen')
                    )
                )
            )
        );
    }
}

// Register the glossar format type
registerFormatType('simple-clean-theme/glossar', {
    title: 'Glossar',
    tagName: 'span',
    className: 'glossar-term',
    edit: GlossarFormatButton,
});
