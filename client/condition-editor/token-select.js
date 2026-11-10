/**
 * WordPress dependencies
 */
import { FormTokenField } from '@wordpress/components';
import { useEffect, useMemo, useRef, useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';

/**
 * A multi-select of {value, label} options that shows labels and stores values.
 * Searchable options are loaded from the REST API when `search` is given.
 *
 * @param {Object}   props
 * @param {string}   props.label
 * @param {string[]} props.value       Selected values.
 * @param {Object[]} props.options     Static options.
 * @param {Object}   props.search      { ruleType, field } for options searched through the REST API.
 * @param {Object}   props.knownLabels value => label of already selected values.
 * @param {Function} props.onChange    Called with the new values.
 * @param {string}   props.help
 */
export default function TokenSelect( {
	label,
	value = [],
	options = [],
	search,
	knownLabels = {},
	onChange,
	help,
} ) {
	const [ labels, setLabels ] = useState( () => {
		const initial = { ...knownLabels };
		options.forEach(
			( option ) => ( initial[ option.value ] = option.label )
		);
		return initial;
	} );
	const [ suggestions, setSuggestions ] = useState(
		options.map( ( option ) => option.label )
	);
	const timer = useRef();

	const valueByLabel = useMemo( () => {
		const map = {};
		Object.keys( labels ).forEach(
			( key ) => ( map[ labels[ key ] ] = key )
		);
		return map;
	}, [ labels ] );

	// Typed text resolves to its exact label, else to the first suggestion containing it (what Enter picks).
	const resolve = ( token ) => {
		if ( token in valueByLabel ) {
			return valueByLabel[ token ];
		}
		const needle = String( token ).toLowerCase();
		const match = suggestions.find( ( suggestion ) =>
			suggestion.toLowerCase().includes( needle )
		);
		return match !== undefined ? valueByLabel[ match ] : undefined;
	};

	const fetchOptions = ( text ) => {
		apiFetch( {
			path: addQueryArgs( `/rspw-lite/v1/options/${ search.ruleType }`, {
				field: search.field,
				search: text,
			} ),
		} )
			.then( ( results ) => {
				setLabels( ( current ) => {
					const next = { ...current };
					results.forEach(
						( option ) => ( next[ option.value ] = option.label )
					);
					return next;
				} );
				setSuggestions( results.map( ( option ) => option.label ) );
			} )
			.catch( () => setSuggestions( [] ) );
	};

	useEffect( () => {
		if ( search ) {
			fetchOptions( '' );
		}
		return () => clearTimeout( timer.current );
		// Load the first page of options once.
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [] );

	const onInputChange = ( text ) => {
		if ( ! search ) {
			return;
		}
		clearTimeout( timer.current );
		timer.current = setTimeout( () => fetchOptions( text ), 250 );
	};

	return (
		<FormTokenField
			label={ label }
			value={ value.map( ( v ) => labels[ v ] || v ) }
			suggestions={ suggestions }
			onInputChange={ onInputChange }
			onChange={ ( tokens ) =>
				onChange( [
					...new Set(
						tokens
							.map( ( token ) =>
								typeof token === 'string' ? token : token.value
							)
							.map( ( token ) =>
								value.includes( token )
									? token
									: resolve( token )
							)
							.filter( ( v ) => v !== undefined )
					),
				] )
			}
			__experimentalExpandOnFocus
			__experimentalShowHowTo={ false }
			__experimentalValidateInput={ ( token ) =>
				resolve( token ) !== undefined
			}
			maxSuggestions={ 50 }
			help={ help }
			__next40pxDefaultSize
			__nextHasNoMarginBottom
		/>
	);
}
