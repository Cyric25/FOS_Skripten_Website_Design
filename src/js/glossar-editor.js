/**
 * Glossar Gutenberg Editor Integration
 * Adds a custom button to the toolbar for marking glossary terms
 */

const { registerFormatType, toggleFormat } = wp.richText;
const { RichTextToolbarButton } = wp.blockEditor;
const { Component } = wp.element;

// Register the glossar format type
registerFormatType('simple-clean-theme/glossar', {
    title: 'Glossar',
    tagName: 'span',
    className: 'glossar-term',
    edit: class GlossarButton extends Component {
        render() {
            const { isActive, value, onChange } = this.props;

            return wp.element.createElement(RichTextToolbarButton, {
                icon: 'book-alt',
                title: 'Glossar-Begriff markieren',
                onClick: () => {
                    onChange(
                        toggleFormat(value, {
                            type: 'simple-clean-theme/glossar',
                        })
                    );
                },
                isActive: isActive,
            });
        }
    },
});
