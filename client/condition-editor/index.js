/**
 * WordPress dependencies
 */
import { createRoot } from '@wordpress/element';
import domReady from '@wordpress/dom-ready';

/**
 * Internal dependencies
 */
import ConditionEditor from './editor';
import './editor.scss';

domReady( () => {
	const root = document.getElementById( 'rspw-lite-condition-editor-root' );
	if ( root && window.rspwLiteConditionEditor ) {
		createRoot( root ).render(
			<ConditionEditor data={ window.rspwLiteConditionEditor } />
		);
	}
} );
