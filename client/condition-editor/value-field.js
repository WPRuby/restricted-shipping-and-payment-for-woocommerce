/**
 * WordPress dependencies
 */
import { TextControl } from '@wordpress/components';

/**
 * Internal dependencies
 */
import TokenSelect from './token-select';

/**
 * Input for one value field of a rule, chosen by the field's type.
 *
 * @param {Object}   props
 * @param {Object}   props.field       Field schema from PHP (AbstractRule::get_value_schema()).
 * @param {*}        props.value
 * @param {string}   props.ruleType
 * @param {Object}   props.knownLabels Labels of selected values of searchable fields.
 * @param {string}   props.currency
 * @param {string}   props.error
 * @param {Function} props.onChange
 */
export default function ValueField( {
	field,
	value,
	ruleType,
	knownLabels,
	currency,
	error,
	onChange,
} ) {
	const common = {
		label: field.label,
		help: error ? (
			<span className="rspw-field-error">{ error }</span>
		) : (
			field.help || undefined
		),
		__nextHasNoMarginBottom: true,
	};

	switch ( field.type ) {
		case 'multiselect':
			return (
				<TokenSelect
					{ ...common }
					value={ Array.isArray( value ) ? value : [] }
					options={ field.options }
					search={
						field.async ? { ruleType, field: field.key } : null
					}
					knownLabels={ knownLabels }
					onChange={ onChange }
				/>
			);

		case 'number':
		case 'money':
			return (
				<TextControl
					{ ...common }
					label={
						field.type === 'money'
							? `${ field.label } (${ currency })`
							: `${ field.label }${
									field.unit ? ` (${ field.unit })` : ''
							  }`
					}
					type="number"
					min="0"
					step="any"
					value={ value ?? '' }
					onChange={ onChange }
					__next40pxDefaultSize
				/>
			);

		case 'email':
			return (
				<TextControl
					{ ...common }
					type="email"
					value={ value || '' }
					onChange={ onChange }
					__next40pxDefaultSize
				/>
			);

		default:
			return (
				<TextControl
					{ ...common }
					value={ value || '' }
					onChange={ onChange }
					__next40pxDefaultSize
				/>
			);
	}
}
