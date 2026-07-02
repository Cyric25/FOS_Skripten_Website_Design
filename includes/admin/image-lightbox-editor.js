/**
 * Adds a "Lightbox" button to the core/image block toolbar.
 * Sets className 'clb-lightbox' when enabled; PHP detects this class
 * and adds data-clb-src so our custom lightbox handles the click.
 */
( function () {
    var el            = wp.element.createElement;
    var Fragment      = wp.element.Fragment;
    var addFilter     = wp.hooks.addFilter;
    var BlockControls = wp.blockEditor.BlockControls;
    var ToolbarButton = wp.components.ToolbarButton;

    var CLB_CLASS = 'clb-lightbox';

    addFilter(
        'editor.BlockEdit',
        'fos-theme/image-lightbox-control',
        function ( BlockEdit ) {
            return function ( props ) {
                if ( props.name !== 'core/image' ) {
                    return el( BlockEdit, props );
                }

                var currentClass = props.attributes.className || '';
                var isEnabled    = currentClass.split( ' ' ).indexOf( CLB_CLASS ) !== -1;

                function toggleLightbox() {
                    var classes = currentClass
                        .split( ' ' )
                        .filter( function ( c ) { return c && c !== CLB_CLASS; } );
                    if ( ! isEnabled ) classes.push( CLB_CLASS );
                    props.setAttributes( { className: classes.join( ' ' ).trim() || undefined } );
                }

                return el(
                    Fragment,
                    null,
                    el( BlockEdit, props ),
                    el(
                        BlockControls,
                        { group: 'other' },
                        el( ToolbarButton, {
                            icon: 'fullscreen-alt',
                            label: 'Lightbox',
                            isPressed: isEnabled,
                            onClick: toggleLightbox,
                        } )
                    )
                );
            };
        }
    );
}() );
